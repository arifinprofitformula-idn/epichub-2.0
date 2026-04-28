<?php

namespace App\Filament\Resources\LegacyV1Commissions;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\LegacyV1Commissions\Pages\ListLegacyV1Commissions;
use App\Filament\Resources\LegacyV1Commissions\Pages\ViewLegacyV1Commission;
use App\Filament\Resources\LegacyV1Commissions\Tables\LegacyV1CommissionsTable;
use App\Models\LegacyV1Commission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LegacyV1CommissionResource extends Resource
{
    protected static ?string $model = LegacyV1Commission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $navigationLabel = 'Legacy Commission Ledger';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Afiliasi;

    protected static ?int $navigationSort = 45;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return LegacyV1CommissionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLegacyV1Commissions::route('/'),
            'view' => ViewLegacyV1Commission::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
