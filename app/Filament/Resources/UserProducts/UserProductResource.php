<?php

namespace App\Filament\Resources\UserProducts;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\UserProducts\Pages\ListUserProducts;
use App\Filament\Resources\UserProducts\Tables\UserProductsTable;
use App\Models\UserProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UserProductResource extends Resource
{
    protected static ?string $model = UserProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = 'Hak Akses Produk';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Operasional;

    protected static ?int $navigationSort = 30;

    public static function table(Table $table): Table
    {
        return UserProductsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserProducts::route('/'),
        ];
    }
}
