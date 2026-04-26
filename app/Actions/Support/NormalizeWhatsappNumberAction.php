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
        } elseif (str_starts_with($normalized, '0')) {
            $normalized = '62'.substr($normalized, 1);
        }

        $normalized = preg_replace('/\D/', '', $normalized) ?? '';

        return $normalized !== '' ? $normalized : null;
    }
}
