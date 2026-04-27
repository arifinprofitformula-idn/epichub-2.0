<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Unpaid => 'Belum dibayar',
            self::Paid => 'Lunas',
            self::Failed => 'Gagal',
            self::Cancelled => 'Dibatalkan',
            self::Refunded => 'Dikembalikan',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Paid => 'success',
            self::Pending, self::Unpaid => 'warning',
            self::Failed, self::Cancelled => 'danger',
            self::Refunded => 'info',
        };
    }
}
