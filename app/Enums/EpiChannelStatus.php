<?php

namespace App\Enums;

enum EpiChannelStatus: string
{
    case Prospect = 'prospect';
    case Qualified = 'qualified';
    case Active = 'active';
    case Suspended = 'suspended';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Prospect => 'Prospect',
            self::Qualified => 'Qualified',
            self::Active => 'Active',
            self::Suspended => 'Suspended',
            self::Inactive => 'Inactive',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Qualified => 'info',
            self::Prospect => 'warning',
            self::Suspended, self::Inactive => 'danger',
        };
    }
}

