<?php

namespace App\Filament\Resources\CommissionPayouts;

use App\Filament\Navigation\AdminNavigationGroup;
use App\Filament\Resources\CommissionPayouts\Pages\ListCommissionPayouts;
use App\Filament\Resources\CommissionPayouts\Tables\CommissionPayoutsTable;
use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\EpiChannel;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CommissionPayoutResource extends Resource
{
    protected static ?string $model = EpiChannel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Pencairan Komisi';

    protected static ?string $modelLabel = 'Payout Komisi';

    protected static ?string $pluralModelLabel = 'Payout Komisi';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Afiliasi;

    protected static ?int $navigationSort = 50;

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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('user')
            ->select('epi_channels.*')
            ->selectSub(
                Commission::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('commissions.epi_channel_id', 'epi_channels.id')
                    ->eligibleForPayout(),
                'available_commissions_count',
            )
            ->selectSub(
                Commission::query()
                    ->selectRaw('COALESCE(SUM(commission_amount), 0)')
                    ->whereColumn('commissions.epi_channel_id', 'epi_channels.id')
                    ->eligibleForPayout(),
                'available_commission_total_amount',
            )
            ->selectSub(
                Commission::query()
                    ->selectRaw('MAX(COALESCE(approved_at, created_at))')
                    ->whereColumn('commissions.epi_channel_id', 'epi_channels.id')
                    ->eligibleForPayout(),
                'last_available_commission_at',
            )
            ->selectSub(
                CommissionPayout::query()
                    ->select('id')
                    ->whereColumn('commission_payouts.epi_channel_id', 'epi_channels.id')
                    ->latest('id')
                    ->limit(1),
                'latest_payout_id',
            )
            ->selectSub(
                CommissionPayout::query()
                    ->select('payout_number')
                    ->whereColumn('commission_payouts.epi_channel_id', 'epi_channels.id')
                    ->latest('id')
                    ->limit(1),
                'latest_payout_number',
            )
            ->whereHas('commissions', fn (Builder $query) => $query->eligibleForPayout());
    }
}
