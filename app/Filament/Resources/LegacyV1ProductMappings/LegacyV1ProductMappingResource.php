<?php

namespace App\Filament\Resources\LegacyV1ProductMappings;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\LegacyV1ProductMappings\Pages\CreateLegacyV1ProductMapping;
use App\Filament\Resources\LegacyV1ProductMappings\Pages\EditLegacyV1ProductMapping;
use App\Filament\Resources\LegacyV1ProductMappings\Pages\ListLegacyV1ProductMappings;
use App\Filament\Resources\LegacyV1ProductMappings\Schemas\LegacyV1ProductMappingForm;
use App\Filament\Resources\LegacyV1ProductMappings\Tables\LegacyV1ProductMappingsTable;
use App\Models\LegacyV1ProductMapping;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LegacyV1ProductMappingResource extends Resource
{
    protected static ?string $model = LegacyV1ProductMapping::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Legacy Product Mappings';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Administrasi;

    protected static ?int $navigationSort = 53;

    public static function form(Schema $schema): Schema
    {
        return LegacyV1ProductMappingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LegacyV1ProductMappingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLegacyV1ProductMappings::route('/'),
            'create' => CreateLegacyV1ProductMapping::route('/create'),
            'edit' => EditLegacyV1ProductMapping::route('/{record}/edit'),
        ];
    }
}
