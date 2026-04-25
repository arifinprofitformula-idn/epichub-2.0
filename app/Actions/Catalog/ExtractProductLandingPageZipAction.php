<?php

namespace App\Actions\Catalog;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class ExtractProductLandingPageZipAction
{
    protected const MAX_FILES = 300;

    protected const MAX_UNCOMPRESSED_BYTES = 52428800;

    /**
     * @var array<int, string>
     */
    protected array $blockedExtensions = [
        'php',
        'phtml',
        'phar',
        'cgi',
        'asp',
        'aspx',
        'jsp',
        'sh',
        'bat',
        'exe',
        'env',
    ];

    /**
     * @var array<int, string>
     */
    protected array $blockedFilenames = [
        '.env',
        '.htaccess',
        'web.config',
    ];

    /**
     * @var array<int, string>
     */
    protected array $allowedExtensions = [
        'html',
        'htm',
        'css',
        'js',
        'png',
        'jpg',
        'jpeg',
        'webp',
        'gif',
        'svg',
        'ico',
        'woff',
        'woff2',
        'ttf',
        'otf',
        'pdf',
    ];

    public function execute(Product $product, string $zipPath, bool $incrementVersion = false): Product
    {
        $disk = Storage::disk('local');

        if ($zipPath === '' || ! $disk->exists($zipPath)) {
            throw ValidationException::withMessages([
                'landing_page_zip_path' => 'File ZIP landing page tidak ditemukan.',
            ]);
        }

        $absoluteZipPath = $disk->path($zipPath);
        $zip = new ZipArchive();
        $opened = $zip->open($absoluteZipPath);

        if ($opened !== true) {
            throw ValidationException::withMessages([
                'landing_page_zip_path' => 'ZIP landing page tidak bisa dibuka.',
            ]);
        }

        $newVersion = $incrementVersion
            ? max(1, (int) $product->landing_page_version + 1)
            : max(1, (int) $product->landing_page_version);
        $extractPath = 'product-landings/extracted/'.$product->id.'/'.$newVersion;
        $entryFile = $this->normalizeEntryFile((string) ($product->landing_page_entry_file ?: 'index.html'));

        $disk->deleteDirectory($extractPath);
        $disk->makeDirectory($extractPath);

        $fileCount = 0;
        $totalBytes = 0;

        try {
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entryName = $zip->getNameIndex($index);

                if (! is_string($entryName) || $entryName === '') {
                    continue;
                }

                $normalized = $this->normalizeZipEntryPath($entryName);
                $isDirectory = str_ends_with(str_replace('\\', '/', $entryName), '/');

                if ($isDirectory) {
                    $disk->makeDirectory($extractPath.'/'.$normalized);
                    continue;
                }

                $stat = $zip->statIndex($index);
                $entrySize = (int) ($stat['size'] ?? 0);

                $fileCount++;
                $totalBytes += max(0, $entrySize);

                if ($fileCount > self::MAX_FILES) {
                    throw ValidationException::withMessages([
                        'landing_page_zip_path' => 'ZIP landing page melebihi batas jumlah file.',
                    ]);
                }

                if ($totalBytes > self::MAX_UNCOMPRESSED_BYTES) {
                    throw ValidationException::withMessages([
                        'landing_page_zip_path' => 'ZIP landing page melebihi batas ukuran extract.',
                    ]);
                }

                $this->assertAllowedFile($normalized);

                $contents = $zip->getFromIndex($index);

                if ($contents === false) {
                    throw ValidationException::withMessages([
                        'landing_page_zip_path' => 'Ada file ZIP yang tidak bisa diekstrak.',
                    ]);
                }

                $target = $extractPath.'/'.$normalized;
                $directory = trim(dirname($target), '.');

                if ($directory !== '') {
                    $disk->makeDirectory($directory);
                }

                $disk->put($target, $contents);
            }
        } catch (\Throwable $e) {
            $zip->close();
            $disk->deleteDirectory($extractPath);

            throw $e;
        }

        $zip->close();

        if (! $disk->exists($extractPath.'/'.$entryFile)) {
            $disk->deleteDirectory($extractPath);

            throw ValidationException::withMessages([
                'landing_page_entry_file' => 'Entry file landing page tidak ditemukan di dalam ZIP.',
            ]);
        }

        $assetToken = $product->landing_page_asset_token ?: $this->generateUniqueAssetToken();

        $product->forceFill([
            'landing_page_extract_path' => $extractPath,
            'landing_page_entry_file' => $entryFile,
            'landing_page_asset_token' => $assetToken,
            'landing_page_uploaded_at' => now(),
            'landing_page_version' => $newVersion,
        ])->saveQuietly();

        return $product->refresh();
    }

    protected function normalizeEntryFile(string $entryFile): string
    {
        return $this->normalizeZipEntryPath($entryFile);
    }

    protected function normalizeZipEntryPath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = preg_replace('#/+#', '/', $path) ?? '';

        while (str_starts_with($path, './')) {
            $path = substr($path, 2);
        }

        if ($path === '' || str_starts_with($path, '/') || preg_match('/^[A-Za-z]:\//', $path) === 1) {
            throw ValidationException::withMessages([
                'landing_page_zip_path' => 'ZIP mengandung path yang tidak aman.',
            ]);
        }

        $segments = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                throw ValidationException::withMessages([
                    'landing_page_zip_path' => 'ZIP mengandung path traversal yang tidak diizinkan.',
                ]);
            }

            $segments[] = $segment;
        }

        if ($segments === []) {
            throw ValidationException::withMessages([
                'landing_page_zip_path' => 'ZIP mengandung path yang tidak valid.',
            ]);
        }

        return implode('/', $segments);
    }

    protected function assertAllowedFile(string $path): void
    {
        $basename = Str::lower(basename($path));
        $extension = Str::lower(pathinfo($basename, PATHINFO_EXTENSION));

        if (in_array($basename, $this->blockedFilenames, true) || in_array($extension, $this->blockedExtensions, true)) {
            throw ValidationException::withMessages([
                'landing_page_zip_path' => 'ZIP mengandung file yang tidak diizinkan: '.$basename,
            ]);
        }

        if (! in_array($extension, $this->allowedExtensions, true)) {
            throw ValidationException::withMessages([
                'landing_page_zip_path' => 'ZIP mengandung extension file yang tidak didukung: '.$basename,
            ]);
        }
    }

    protected function generateUniqueAssetToken(): string
    {
        do {
            $token = Str::random(40);
        } while (Product::query()->where('landing_page_asset_token', $token)->exists());

        return $token;
    }
}
