<?php

namespace App\Filament\Widgets;

use App\Models\LegacyV1Commission;
use App\Models\LegacyV1CommissionImportError;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LegacyV1CommissionOverviewWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalAmount = (float) LegacyV1Commission::query()->sum('commission_amount');
        $paidAmount = (float) LegacyV1Commission::query()->where('commission_status', 'paid')->sum('commission_amount');
        $unpaidAmount = (float) LegacyV1Commission::query()
            ->whereNotIn('commission_status', ['paid', 'rejected', 'cancelled'])
            ->sum('commission_amount');

        return [
            Stat::make('Total Legacy Commission', 'Rp '.number_format($totalAmount, 0, ',', '.'))
                ->description('Seluruh ledger komisi EPIC HUB 1.0')
                ->color('gray'),
            Stat::make('Total Paid', 'Rp '.number_format($paidAmount, 0, ',', '.'))
                ->color('success'),
            Stat::make('Total Unpaid', 'Rp '.number_format($unpaidAmount, 0, ',', '.'))
                ->color('warning'),
            Stat::make('Total Unresolved User', (string) LegacyV1Commission::query()->where('migration_status', 'unresolved_user')->count())
                ->color('danger'),
            Stat::make('Total Unknown Status', (string) LegacyV1Commission::query()->where('commission_status', 'unknown')->count())
                ->color('gray'),
            Stat::make('Total Import Error', (string) LegacyV1CommissionImportError::query()->count())
                ->color('danger'),
        ];
    }
}
