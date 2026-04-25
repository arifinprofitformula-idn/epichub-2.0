<?php

namespace App\Enums;

enum ProductVisibility: string
{
    case Public = 'public';
    case Private = 'private';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Public',
            self::Private => 'Private',
            self::Hidden => 'Hidden',
        };
    }
}
