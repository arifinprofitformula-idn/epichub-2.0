<?php

namespace App\Enums;

enum ReferralOrderStatus: string
{
    case Pending = 'pending';
    case Converted = 'converted';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Converted => 'Converted',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Converted => 'success',
            self::Pending => 'warning',
            self::Cancelled => 'danger',
            self::Refunded => 'info',
        };
    }
}

