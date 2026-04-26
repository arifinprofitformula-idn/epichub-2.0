<?php

namespace App\Filament\Navigation;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum AdminNavigationGroup implements HasLabel
{
    case Operasional;
    case Katalog;
    case Program;
    case Afiliasi;
    case Administrasi;

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Operasional => 'Operasional',
            self::Katalog => 'Katalog',
            self::Program => 'Program & Event',
            self::Afiliasi => 'Afiliasi',
            self::Administrasi => 'Administrasi',
        };
    }
}
