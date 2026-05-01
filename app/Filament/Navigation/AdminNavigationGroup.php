<?php

namespace App\Filament\Navigation;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum AdminNavigationGroup implements HasLabel
{
    case Products;
    case Afiliasi;
    case Administration;
    case Settings;

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Products => 'Products',
            self::Afiliasi => 'Affiliasi',
            self::Administration => 'Administration',
            self::Settings => 'Settings',
        };
    }
}
