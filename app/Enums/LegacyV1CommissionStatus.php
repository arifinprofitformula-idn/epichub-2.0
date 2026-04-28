<?php

namespace App\Enums;

enum LegacyV1CommissionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Paid = 'paid';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Paid => 'Paid',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
            self::Unknown => 'Unknown',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Paid => 'success',
            self::Approved => 'info',
            self::Pending => 'warning',
            self::Rejected, self::Cancelled => 'danger',
            self::Unknown => 'gray',
        };
    }
}
