<?php

namespace App\Enums;

enum PayoutStatus: string
{
    case Draft = 'draft';
    case Processing = 'processing';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Processing => 'Processing',
            self::Paid => 'Paid',
            self::Cancelled => 'Cancelled',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Paid => 'success',
            self::Processing => 'info',
            self::Draft => 'warning',
            self::Cancelled => 'danger',
        };
    }
}

