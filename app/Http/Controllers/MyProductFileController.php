<?php

namespace App\Http\Controllers;

use App\Actions\Access\CheckProductAccessAction;
use App\Actions\Access\LogAccessAction;
use App\Enums\AccessLogAction;
use App\Models\ProductFile;
use App\Models\UserProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MyProductFileController extends Controller
{
    public function __construct(
        protected CheckProductAccessAction $checkProductAccess,
        protected LogAccessAction $logAccess,
    ) {
    }

    public function view(Request $request, UserProduct $userProduct, ProductFile $productFile): StreamedResponse
    {
        $validatedUserProduct = $this->checkProductAccess->execute($request->user(), $userProduct);

        if (! $validatedUserProduct) {
            $this->logDenied($request, $userProduct, $productFile, 'user_product_not_accessible');

            abort(404);
        }

        if ($productFile->product_id !== $userProduct->product_id) {
            $this->logDenied($request, $userProduct, $productFile, 'product_file_mismatch');

            abort(404);
        }

        if (! $productFile->is_active) {
            $this->logDenied($request, $userProduct, $productFile, 'product_file_inactive');

            abort(404);
        }

        $path = $productFile->file_path;

        if ($path === null || trim($path) === '') {
            $this->logDenied($request, $userProduct, $productFile, 'file_path_missing');

            abort(404);
        }

        [$disk, $resolvedPath] = $this->resolveFileOrAbort($request, $userProduct, $productFile, $path);

        $mime = Storage::disk($disk)->mimeType($resolvedPath);

        if (! $this->isViewableMime($mime)) {
            return $this->download($request, $userProduct, $productFile);
        }

        $filename = $this->downloadFilename($productFile, $resolvedPath);

        $this->logAccess->execute(
            action: AccessLogAction::FileViewed,
            user: $request->user(),
            userProduct: $userProduct,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            metadata: [
                'product_file_id' => $productFile->id,
                'file_title' => $productFile->title,
                'delivery_type' => 'view',
                'disk' => $disk,
            ],
        );

        return Storage::disk($disk)->response($resolvedPath, $filename, [
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    public function download(Request $request, UserProduct $userProduct, ProductFile $productFile): StreamedResponse
    {
        $validatedUserProduct = $this->checkProductAccess->execute($request->user(), $userProduct);

        if (! $validatedUserProduct) {
            $this->logDenied($request, $userProduct, $productFile, 'user_product_not_accessible');

            abort(404);
        }

        if ($productFile->product_id !== $userProduct->product_id) {
            $this->logDenied($request, $userProduct, $productFile, 'product_file_mismatch');

            abort(404);
        }

        if (! $productFile->is_active) {
            $this->logDenied($request, $userProduct, $productFile, 'product_file_inactive');

            abort(404);
        }

        $path = $productFile->file_path;

        if ($path === null || trim($path) === '') {
            $this->logDenied($request, $userProduct, $productFile, 'file_path_missing');

            abort(404);
        }

        [$disk, $resolvedPath] = $this->resolveFileOrAbort($request, $userProduct, $productFile, $path);

        $filename = $this->downloadFilename($productFile, $resolvedPath);

        $this->logAccess->execute(
            action: AccessLogAction::FileDownloaded,
            user: $request->user(),
            userProduct: $userProduct,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            metadata: [
                'product_file_id' => $productFile->id,
                'file_title' => $productFile->title,
                'delivery_type' => 'download',
                'disk' => $disk,
            ],
        );

        return Storage::disk($disk)->download($resolvedPath, $filename);
    }

    public function openExternal(Request $request, UserProduct $userProduct, ProductFile $productFile): RedirectResponse
    {
        $validatedUserProduct = $this->checkProductAccess->execute($request->user(), $userProduct);

        if (! $validatedUserProduct) {
            $this->logDenied($request, $userProduct, $productFile, 'user_product_not_accessible');

            abort(404);
        }

        if ($productFile->product_id !== $userProduct->product_id) {
            $this->logDenied($request, $userProduct, $productFile, 'product_file_mismatch');

            abort(404);
        }

        if (! $productFile->is_active) {
            $this->logDenied($request, $userProduct, $productFile, 'product_file_inactive');

            abort(404);
        }

        $url = $productFile->external_url;

        if ($url === null || trim($url) === '') {
            $this->logDenied($request, $userProduct, $productFile, 'external_url_missing');

            abort(404);
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            $this->logDenied($request, $userProduct, $productFile, 'external_url_invalid');

            abort(404);
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (! in_array($scheme, ['http', 'https'], true)) {
            $this->logDenied($request, $userProduct, $productFile, 'external_url_scheme_not_allowed');

            abort(404);
        }

        $this->logAccess->execute(
            action: AccessLogAction::ExternalLinkOpened,
            user: $request->user(),
            userProduct: $userProduct,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            metadata: [
                'product_file_id' => $productFile->id,
                'file_title' => $productFile->title,
                'delivery_type' => 'external',
            ],
        );

        return redirect()->away($url);
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function resolveFileOrAbort(Request $request, UserProduct $userProduct, ProductFile $productFile, string $path): array
    {
        if (Storage::disk('local')->exists($path)) {
            return ['local', $path];
        }

        if (Storage::disk('public')->exists($path)) {
            return ['public', $path];
        }

        $this->logDenied($request, $userProduct, $productFile, 'file_not_found');

        abort(404);
    }

    protected function isViewableMime(?string $mime): bool
    {
        if ($mime === null) {
            return false;
        }

        if ($mime === 'application/pdf') {
            return true;
        }

        return Str::startsWith($mime, 'image/');
    }

    protected function downloadFilename(ProductFile $productFile, string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $base = Str::slug((string) ($productFile->title ?: 'file'));

        if ($extension !== '') {
            return $base.'.'.$extension;
        }

        return $base;
    }

    protected function logDenied(Request $request, UserProduct $userProduct, ProductFile $productFile, string $reason): void
    {
        $this->logAccess->execute(
            action: AccessLogAction::AccessDenied,
            user: $request->user(),
            userProduct: $userProduct,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            metadata: [
                'reason' => $reason,
                'product_file_id' => $productFile->id,
                'file_title' => $productFile->title,
            ],
        );
    }
}

