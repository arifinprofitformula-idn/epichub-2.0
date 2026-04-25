<?php

namespace App\Filament\Resources\UserProducts;

use App\Filament\Resources\UserProducts\Pages\ListUserProducts;
use App\Filament\Resources\UserProducts\Tables\UserProductsTable;
use App\Models\UserProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class UserProductResource extends Resource
{
    protected static ?string $model = UserProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = 'Entitlements';

    protected static string|UnitEnum|null $navigationGroup = 'Access';

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

