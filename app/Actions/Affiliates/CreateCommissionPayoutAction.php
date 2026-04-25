<?php

namespace App\Actions\Affiliates;

use App\Enums\CommissionStatus;
use App\Enums\PayoutStatus;
use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\EpiChannel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateCommissionPayoutAction
{
    /**
     * @param  array<int, int>  $commissionIds
     */
    public function execute(EpiChannel $channel, User $actor, array $commissionIds = [], ?string $notes = null): CommissionPayout
    {
        return DB::transaction(function () use ($channel, $actor, $commissionIds, $notes): CommissionPayout {
            $query = Commission::query()
                ->where('epi_channel_id', $channel->id)
                ->where('status', CommissionStatus::Approved)
                ->whereNull('commission_payout_id')
                ->lockForUpdate();

            if (count($commissionIds) > 0) {
                $query->whereIn('id', $commissionIds);
            }

            $commissions = $query->get();

            if ($commissions->count() === 0) {
                throw new RuntimeException('Tidak ada komisi approved yang bisa dipayout.');
            }

            $total = (string) $commissions->sum('commission_amount');

            $payout = CommissionPayout::query()->create([
                'epi_channel_id' => $channel->id,
                'payout_number' => $this->generatePayoutNumber(),
                'total_amount' => $total,
                'status' => PayoutStatus::Processing,
                'notes' => $notes,
                'paid_by' => null,
                'paid_at' => null,
                'metadata' => [
                    'created_by' => $actor->id,
                    'commission_ids' => $commissions->pluck('id')->all(),
                ],
            ]);

            Commission::query()
                ->whereIn('id', $commissions->pluck('id'))
                ->update([
                    'commission_payout_id' => $payout->id,
                ]);

            return $payout->refresh();
        });
    }

    protected function generatePayoutNumber(): string
    {
        $prefix = 'PO-'.now()->format('Ymd').'-';

        for ($i = 0; $i < 5; $i++) {
            $candidate = $prefix.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);

            if (! CommissionPayout::query()->where('payout_number', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new RuntimeException('Gagal membuat payout number yang unik.');
    }
}

