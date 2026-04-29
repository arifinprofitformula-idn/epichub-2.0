<?php

namespace App\Filament\Resources\LegacyV1Payments;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\LegacyV1Payments\Pages\ListLegacyV1Payments;
use App\Filament\Resources\LegacyV1Payments\Tables\LegacyV1PaymentsTable;
use App\Models\LegacyV1Payment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LegacyV1PaymentResource extends Resource
{
    protected static ?string $model = LegacyV1Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Legacy Payments';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Administrasi;

    protected static ?int $navigationSort = 54;

    public static function table(Table $table): Table
    {
        return LegacyV1PaymentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLegacyV1Payments::route('/'),
        ];
    }
}
