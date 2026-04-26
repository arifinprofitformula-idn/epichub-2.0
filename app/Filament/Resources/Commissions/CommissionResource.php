<?php

namespace App\Filament\Resources\Commissions;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\Commissions\Pages\ListCommissions;
use App\Filament\Resources\Commissions\Tables\CommissionsTable;
use App\Models\Commission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CommissionResource extends Resource
{
    protected static ?string $model = Commission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = 'Komisi';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Afiliasi;

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return CommissionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommissions::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
