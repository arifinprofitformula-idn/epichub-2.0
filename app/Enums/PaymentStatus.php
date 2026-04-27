<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Success = 'success';
    case Failed = 'failed';
    case Expired = 'expired';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu',
            self::Success => 'Berhasil',
            self::Failed => 'Gagal',
            self::Expired => 'Kedaluwarsa',
            self::Refunded => 'Dikembalikan',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Success => 'success',
            self::Pending => 'warning',
            self::Failed, self::Expired => 'danger',
            self::Refunded => 'info',
        };
    }
}
