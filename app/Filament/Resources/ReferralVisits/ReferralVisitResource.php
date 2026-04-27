<?php

namespace App\Filament\Resources\ReferralVisits;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\ReferralVisits\Pages\ListReferralVisits;
use App\Filament\Resources\ReferralVisits\Tables\ReferralVisitsTable;
use App\Models\ReferralVisit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ReferralVisitResource extends Resource
{
    protected static ?string $model = ReferralVisit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCursorArrowRays;

    protected static ?string $navigationLabel = 'Kunjungan Referral';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Afiliasi;

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return ReferralVisitsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReferralVisits::route('/'),
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
        return parent::getEloquentQuery()->with(['epiChannel', 'product']);
    }
}
