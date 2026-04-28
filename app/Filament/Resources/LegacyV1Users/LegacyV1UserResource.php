<?php

namespace App\Filament\Resources\LegacyV1Users;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\LegacyV1Users\Pages\ListLegacyV1Users;
use App\Filament\Resources\LegacyV1Users\Tables\LegacyV1UsersTable;
use App\Models\LegacyV1User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LegacyV1UserResource extends Resource
{
    protected static ?string $model = LegacyV1User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Legacy Users';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Administrasi;

    protected static ?int $navigationSort = 51;

    public static function table(Table $table): Table
    {
        return LegacyV1UsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLegacyV1Users::route('/'),
        ];
    }
}
