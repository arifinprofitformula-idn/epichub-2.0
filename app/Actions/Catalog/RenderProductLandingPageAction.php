<?php

namespace App\Actions\Catalog;

use App\Models\EpiChannel;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RenderProductLandingPageAction
{
    public function __construct(
        protected ReplaceLandingPageShortcodesAction $replaceShortcodes,
    ) {
    }

    /**
     * @return array{html:string, metaTitle:string, metaDescription:string, shortcodes:array<string, string>}
     */
    public function execute(Product $product, ?EpiChannel $channel = null): array
    {
        $disk = Storage::disk('local');
        $entryFile = $this->normalizeEntryFile((string) $product->landing_page_entry_file);
        $entryPath = trim((string) $product->landing_page_extract_path, '/');

        if ($entryPath === '' || ! $disk->exists($entryPath.'/'.$entryFile)) {
            throw ValidationException::withMessages([
                'landing_page_entry_file' => 'Landing page belum siap dirender.',
            ]);
        }

        $shortcodes = $this->buildShortcodes($product, $channel);
        $rawHtml = (string) $disk->get($entryPath.'/'.$entryFile);
        $baseDirectory = dirname($entryFile);
        $baseDirectory = $baseDirectory === '.' ? '' : $baseDirectory;
        $renderedHtml = $this->replaceShortcodes->execute($rawHtml, $shortcodes);
        $renderedHtml = $this->rewriteAssetUrls($renderedHtml, $product, $baseDirectory);

        return [
            'html' => $this->sanitizeHtml($renderedHtml),
            'metaTitle' => $this->sanitizeMeta(
                $this->replaceShortcodes->execute(
                    (string) ($product->landing_page_meta_title ?: $product->title),
                    $shortcodes,
                ),
                (string) $product->title,
                255,
            ),
            'metaDescription' => $this->sanitizeMeta(
                $this->replaceShortcodes->execute(
                    (string) ($product->landing_page_meta_description ?: $product->short_description ?: ''),
                    $shortcodes,
                ),
                '',
                320,
            ),
            'metaImage' => $this->resolveMetaImage($product),
            'shortcodes' => $shortcodes,
        ];
    }

    protected function resolveMetaImage(Product $product): string
    {
        return $product->getThumbnailUrl() ?? '';
    }

    /**
     * @return array<string, string>
     */
    protected function buildShortcodes(Product $product, ?EpiChannel $channel): array
    {
        $affiliateName = $channel?->user?->name ?: 'EPIC HUB';
        $affiliateCode = $channel?->epic_code ?? '';
        $affiliateStoreName = $channel?->store_name ?: 'EPIC HUB';

        $catalogUrl = route('catalog.products.show', $product->slug);
        $checkoutUrl = route('checkout.show', $product->slug);
        $affiliateReferralLink = $channel
            ? route('referral.redirect', $channel->epic_code).'?product='.$product->slug
            : '';

        if ($affiliateCode !== '') {
            $checkoutUrl .= '?ref='.urlencode($affiliateCode);
        }

        $salePrice = $product->sale_price !== null
            ? $this->formatPrice((float) $product->sale_price)
            : '';

        return [
            '{{product_name}}' => $this->escape($product->title),
            '{{product_title}}' => $this->escape($product->title),
            '{{product_slug}}' => $this->escape($product->slug),
            '{{product_type}}' => $this->escape($product->product_type?->label() ?? (string) $product->product_type),
            '{{product_price}}' => $this->escape($this->formatPrice((float) $product->price)),
            '{{product_sale_price}}' => $this->escape($salePrice),
            '{{product_effective_price}}' => $this->escape($this->formatPrice((float) $product->effective_price)),
            '{{product_short_description}}' => $this->escape((string) $product->short_description),
            '{{product_description}}' => $this->escape(strip_tags((string) $product->full_description)),
            '{{checkout_url}}' => $this->escape($checkoutUrl),
            '{{catalog_url}}' => $this->escape($catalogUrl),
            '{{affiliate_name}}' => $this->escape($affiliateName),
            '{{affiliate_code}}' => $this->escape($affiliateCode),
            '{{affiliate_store_name}}' => $this->escape($affiliateStoreName),
            '{{affiliate_referral_link}}' => $this->escape($affiliateReferralLink),
        ];
    }

    protected function normalizeEntryFile(string $entryFile): string
    {
        $entryFile = str_replace('\\', '/', trim($entryFile));
        $entryFile = preg_replace('#/+#', '/', $entryFile) ?? '';

        while (str_starts_with($entryFile, './')) {
            $entryFile = substr($entryFile, 2);
        }

        if ($entryFile === '' || str_starts_with($entryFile, '/') || preg_match('/^[A-Za-z]:\//', $entryFile) === 1) {
            throw ValidationException::withMessages([
                'landing_page_entry_file' => 'Entry file landing page tidak valid.',
            ]);
        }

        $segments = [];

        foreach (explode('/', $entryFile) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                throw ValidationException::withMessages([
                    'landing_page_entry_file' => 'Entry file landing page tidak aman.',
                ]);
            }

            $segments[] = $segment;
        }

        if ($segments === []) {
            throw ValidationException::withMessages([
                'landing_page_entry_file' => 'Entry file landing page tidak valid.',
            ]);
        }

        return implode('/', $segments);
    }

    protected function escape(?string $value): string
    {
        return e($value ?? '');
    }

    protected function formatPrice(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    protected function sanitizeHtml(string $html): string
    {
        if ($html === '') {
            return '';
        }

        $sanitized = preg_replace('/<\?(?:php|=).*?\?>/is', '', $html) ?? '';
        $sanitized = preg_replace('/<%.*?%>/is', '', $sanitized) ?? '';
        $sanitized = preg_replace('/<script\b(?![^>]*\bsrc\s*=)[^>]*>.*?<\/script>/is', '', $sanitized) ?? '';
        $sanitized = preg_replace('/\s+on[a-z]+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/is', '', $sanitized) ?? '';

        return $sanitized;
    }

    protected function rewriteAssetUrls(string $html, Product $product, string $baseDirectory): string
    {
        return preg_replace_callback(
            '/\b(src|href)\s*=\s*(["\'])(.*?)\2/i',
            function (array $matches) use ($product, $baseDirectory): string {
                $original = $matches[3];

                if ($this->shouldKeepOriginalAssetReference($original)) {
                    return $matches[0];
                }

                $rewritten = $this->buildLandingAssetUrl($product, $original, $baseDirectory);

                return $matches[1].'='.$matches[2].$rewritten.$matches[2];
            },
            $html,
        ) ?? $html;
    }

    protected function shouldKeepOriginalAssetReference(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        return Str::startsWith(Str::lower($value), [
            'http://',
            'https://',
            'mailto:',
            'tel:',
            '#',
            'data:',
            '//',
            '/',
            '{{',
        ]);
    }

    protected function buildLandingAssetUrl(Product $product, string $value, string $baseDirectory): string
    {
        preg_match('/^([^?#]+)(.*)$/', $value, $parts);

        $path = $parts[1] ?? $value;
        $suffix = $parts[2] ?? '';
        $normalized = $this->normalizeRelativeAssetPath($baseDirectory, $path);
        $encodedPath = collect(explode('/', $normalized))
            ->map(fn (string $segment): string => rawurlencode($segment))
            ->implode('/');

        return url('/offer-assets/'.$product->landing_page_asset_token.'/'.$encodedPath).$suffix;
    }

    protected function normalizeRelativeAssetPath(string $baseDirectory, string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = preg_replace('#/+#', '/', $path) ?? '';

        if ($path === '' || str_starts_with($path, '/')) {
            return ltrim($path, '/');
        }

        $segments = $baseDirectory !== '' ? explode('/', trim($baseDirectory, '/')) : [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($segments);

                continue;
            }

            $segments[] = $segment;
        }

        return implode('/', $segments);
    }

    protected function sanitizeMeta(string $value, string $fallback = '', int $limit = 255): string
    {
        $cleaned = trim(strip_tags($value));

        if ($cleaned !== '') {
            return Str::limit($cleaned, $limit, '');
        }

        return Str::limit(trim(strip_tags($fallback)), $limit, '');
    }
}
