<?php

namespace App\Enums;

enum AffiliateCommissionType: string
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => 'Persentase (%)',
            self::Fixed => 'Nominal (Rp)',
        };
    }
}
