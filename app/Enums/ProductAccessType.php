<?php

namespace App\Enums;

enum ProductAccessType: string
{
    case InstantAccess = 'instant_access';
    case ScheduledAccess = 'scheduled_access';
    case ManualRelease = 'manual_release';

    public function label(): string
    {
        return match ($this) {
            self::InstantAccess => 'Akses instan',
            self::ScheduledAccess => 'Akses terjadwal',
            self::ManualRelease => 'Rilis manual',
        };
    }
}
