<?php

namespace App\Filament\Resources\ReferralOrders;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\ReferralOrders\Pages\ListReferralOrders;
use App\Filament\Resources\ReferralOrders\Tables\ReferralOrdersTable;
use App\Models\ReferralOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ReferralOrderResource extends Resource
{
    protected static ?string $model = ReferralOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?string $navigationLabel = 'Order Referral';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Afiliasi;

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return ReferralOrdersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReferralOrders::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['epiChannel', 'buyer', 'order']);
    }
}
