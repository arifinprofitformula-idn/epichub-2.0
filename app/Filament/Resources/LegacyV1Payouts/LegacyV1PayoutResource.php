<?php

namespace App\Filament\Resources\LegacyV1Payouts;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\LegacyV1Payouts\Pages\ListLegacyV1Payouts;
use App\Filament\Resources\LegacyV1Payouts\Tables\LegacyV1PayoutsTable;
use App\Models\LegacyV1Payout;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LegacyV1PayoutResource extends Resource
{
    protected static ?string $model = LegacyV1Payout::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Legacy Payouts';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Administrasi;

    protected static ?int $navigationSort = 57;

    public static function table(Table $table): Table
    {
        return LegacyV1PayoutsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLegacyV1Payouts::route('/'),
        ];
    }
}
