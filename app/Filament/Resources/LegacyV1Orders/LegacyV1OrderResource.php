<?php

namespace App\Filament\Resources\LegacyV1Orders;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\LegacyV1Orders\Pages\ListLegacyV1Orders;
use App\Filament\Resources\LegacyV1Orders\Tables\LegacyV1OrdersTable;
use App\Models\LegacyV1Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LegacyV1OrderResource extends Resource
{
    protected static ?string $model = LegacyV1Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?string $navigationLabel = 'Legacy Orders';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Administrasi;

    protected static ?int $navigationSort = 53;

    public static function table(Table $table): Table
    {
        return LegacyV1OrdersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLegacyV1Orders::route('/'),
        ];
    }
}
