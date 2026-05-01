<?php

namespace App\Actions\Support;

class NormalizeWhatsappNumberAction
{
    public function execute(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = preg_replace('/[^0-9+]/', '', trim($value)) ?? '';

        if ($normalized === '') {
            return null;
        }

        if (str_starts_with($normalized, '+62')) {
            $normalized = '62'.substr($normalized, 3);
        } elseif (str_starts_with($normalized, '62')) {
            // Already has country code prefix — keep as-is.
        } elseif (str_starts_with($normalized, '0')) {
            $normalized = '62'.substr($normalized, 1);
        } else {
            // Number entered without country code or leading 0 (e.g. "85860437327"
            // typed into a field that already shows "+62" as a visual prefix).
            $normalized = '62'.$normalized;
        }

        $normalized = preg_replace('/\D/', '', $normalized) ?? '';

        return $normalized !== '' ? $normalized : null;
    }
}
