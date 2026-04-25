<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductLandingAssetController extends Controller
{
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

    public function show(Request $request, string $token, string $path): BinaryFileResponse
    {
        $product = Product::query()
            ->where('landing_page_asset_token', $token)
            ->where('landing_page_enabled', true)
            ->firstOrFail();

        $safePath = $this->normalizePath($path);
        $extension = Str::lower(pathinfo($safePath, PATHINFO_EXTENSION));

        abort_unless(in_array($extension, $this->allowedExtensions, true), 404);
        abort_if(blank($product->landing_page_extract_path), 404);

        $relativePath = trim($product->landing_page_extract_path, '/').'/'.$safePath;
        $disk = Storage::disk('local');

        abort_unless($disk->exists($relativePath), 404);

        $absolutePath = $disk->path($relativePath);

        return response()->file($absolutePath, [
            'Content-Type' => $disk->mimeType($relativePath) ?: 'application/octet-stream',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    protected function normalizePath(string $path): string
    {
        $path = rawurldecode($path);
        $path = str_replace('\\', '/', trim($path));
        $path = preg_replace('#/+#', '/', $path) ?? '';

        abort_if($path === '', 404);
        abort_if(str_starts_with($path, '/'), 404);
        abort_if(preg_match('/^[A-Za-z]:\//', $path) === 1, 404);

        $segments = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            abort_if($segment === '..', 404);
            $segments[] = $segment;
        }

        abort_if($segments === [], 404);

        return implode('/', $segments);
    }
}
