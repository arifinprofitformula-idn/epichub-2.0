<?php

namespace App\Filament\Resources\LegacyV1ProductAccesses;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\LegacyV1ProductAccesses\Pages\ListLegacyV1ProductAccesses;
use App\Filament\Resources\LegacyV1ProductAccesses\Tables\LegacyV1ProductAccessesTable;
use App\Models\LegacyV1ProductAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LegacyV1ProductAccessResource extends Resource
{
    protected static ?string $model = LegacyV1ProductAccess::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = 'Legacy Product Accesses';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Administrasi;

    protected static ?int $navigationSort = 52;

    public static function table(Table $table): Table
    {
        return LegacyV1ProductAccessesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLegacyV1ProductAccesses::route('/'),
        ];
    }
}
