<?php

namespace App\Filament\Resources\LegacyV1UserMappings;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\LegacyV1UserMappings\Pages\ListLegacyV1UserMappings;
use App\Filament\Resources\LegacyV1UserMappings\Tables\LegacyV1UserMappingsTable;
use App\Models\LegacyV1UserMapping;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LegacyV1UserMappingResource extends Resource
{
    protected static ?string $model = LegacyV1UserMapping::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $navigationLabel = 'Legacy User Mappings';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Administrasi;

    protected static ?int $navigationSort = 51;

    public static function table(Table $table): Table
    {
        return LegacyV1UserMappingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLegacyV1UserMappings::route('/'),
        ];
    }
}
