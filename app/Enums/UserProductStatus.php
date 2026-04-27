<?php

namespace App\Enums;

enum UserProductStatus: string
{
    case Active = 'active';
    case Revoked = 'revoked';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Revoked => 'Dicabut',
            self::Expired => 'Kedaluwarsa',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Revoked => 'danger',
            self::Expired => 'gray',
        };
    }
}

