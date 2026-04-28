<?php

namespace App\Filament\Resources\LegacyV1SponsorLinks;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\LegacyV1SponsorLinks\Pages\ListLegacyV1SponsorLinks;
use App\Filament\Resources\LegacyV1SponsorLinks\Tables\LegacyV1SponsorLinksTable;
use App\Models\LegacyV1SponsorLink;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LegacyV1SponsorLinkResource extends Resource
{
    protected static ?string $model = LegacyV1SponsorLink::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static ?string $navigationLabel = 'Legacy Sponsor Links';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Administrasi;

    protected static ?int $navigationSort = 54;

    public static function table(Table $table): Table
    {
        return LegacyV1SponsorLinksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLegacyV1SponsorLinks::route('/'),
        ];
    }
}
