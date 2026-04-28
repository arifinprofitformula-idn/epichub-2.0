<?php

namespace App\Actions\LegacyV1;

use App\Enums\LegacyV1CommissionMigrationStatus;
use App\Enums\LegacyV1CommissionStatus;
use App\Models\CommissionPayout;
use App\Models\LegacyV1Commission;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class IncludeLegacyV1CommissionInPayoutAction
{
    /**
     * @param  array<int, int>  $commissionIds
     */
    public function execute(CommissionPayout $payout, array $commissionIds): int
    {
        return DB::transaction(function () use ($payout, $commissionIds): int {
            $commissions = LegacyV1Commission::query()
                ->whereIn('id', $commissionIds)
                ->where('is_payable', true)
                ->where('epi_channel_id', $payout->epi_channel_id)
                ->whereNull('payout_id')
                ->lockForUpdate()
                ->get();

            if ($commissions->isEmpty()) {
                throw new RuntimeException('Tidak ada legacy commission payable yang bisa dimasukkan ke payout.');
            }

            $legacyTotal = (float) $commissions->sum('commission_amount');

            LegacyV1Commission::query()
                ->whereIn('id', $commissions->pluck('id'))
                ->update([
                    'payout_id' => $payout->id,
                    'migration_status' => LegacyV1CommissionMigrationStatus::IncludedInPayout,
                    'commission_status' => LegacyV1CommissionStatus::Paid,
                    'paid_at' => $payout->paid_at ?? now(),
                    'updated_at' => now(),
                ]);

            $existingSources = collect(data_get($payout->metadata, 'sources', []))
                ->push('legacy_v1_commissions')
                ->unique()
                ->values()
                ->all();

            $payout->forceFill([
                'total_amount' => (float) $payout->total_amount + $legacyTotal,
                'metadata' => array_merge($payout->metadata ?? [], [
                    'legacy_v1_commission_total' => ((float) data_get($payout->metadata, 'legacy_v1_commission_total', 0)) + $legacyTotal,
                    'sources' => $existingSources,
                ]),
            ])->save();

            return $commissions->count();
        });
    }
}
