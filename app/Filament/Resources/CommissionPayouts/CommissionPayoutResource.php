<?php

namespace App\Filament\Resources\CommissionPayouts;

use App\Filament\Resources\CommissionPayouts\Pages\ListCommissionPayouts;
use App\Filament\Resources\CommissionPayouts\Tables\CommissionPayoutsTable;
use App\Models\CommissionPayout;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CommissionPayoutResource extends Resource
{
    protected static ?string $model = CommissionPayout::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Commission Payouts';

    protected static string|UnitEnum|null $navigationGroup = 'Affiliate';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return CommissionPayoutsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommissionPayouts::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
