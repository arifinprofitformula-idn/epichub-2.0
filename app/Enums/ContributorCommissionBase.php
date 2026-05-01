<?php

namespace App\Enums;

enum ContributorCommissionBase: string
{
    case Gross = 'gross';
    case NetAfterDiscount = 'net_after_discount';
    case NetAfterAffiliate = 'net_after_affiliate';

    public function label(): string
    {
        return match ($this) {
            self::Gross => 'Gross (Harga Penuh)',
            self::NetAfterDiscount => 'Net Setelah Diskon',
            self::NetAfterAffiliate => 'Net Setelah Komisi Affiliate',
        };
    }
}
