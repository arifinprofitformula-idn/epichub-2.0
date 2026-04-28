<?php

namespace App\Filament\Resources\LegacyV1ImportErrors;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\LegacyV1ImportErrors\Pages\ListLegacyV1ImportErrors;
use App\Filament\Resources\LegacyV1ImportErrors\Tables\LegacyV1ImportErrorsTable;
use App\Models\LegacyV1ImportError;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LegacyV1ImportErrorResource extends Resource
{
    protected static ?string $model = LegacyV1ImportError::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $navigationLabel = 'Legacy Import Errors';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Administrasi;

    protected static ?int $navigationSort = 55;

    public static function table(Table $table): Table
    {
        return LegacyV1ImportErrorsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLegacyV1ImportErrors::route('/'),
        ];
    }
}
