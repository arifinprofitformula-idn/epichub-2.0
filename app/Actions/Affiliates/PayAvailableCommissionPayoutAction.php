<?php

namespace App\Actions\Affiliates;

use App\Enums\PayoutStatus;
use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\EpiChannel;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PayAvailableCommissionPayoutAction
{
    public function execute(EpiChannel $channel, User $actor, float $requestedAmount, ?string $notes = null): CommissionPayout
    {
        return DB::transaction(function () use ($channel, $actor, $requestedAmount, $notes): CommissionPayout {
            $channel = EpiChannel::query()
                ->whereKey($channel->getKey())
                ->with('user')
                ->lockForUpdate()
                ->firstOrFail();

            if (! $channel->hasCompletePayoutBankInfo()) {
                throw new RuntimeException('Data rekening payout belum lengkap.');
            }

            $commissions = Commission::query()
                ->where('epi_channel_id', $channel->id)
                ->eligibleForPayout()
                ->orderBy('approved_at')
                ->orderBy('created_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($commissions->isEmpty()) {
                throw new RuntimeException('Tidak ada saldo komisi eligible yang bisa dipayout.');
            }

            $availableAmount = (float) $commissions->sum('commission_amount');
            $requestedAmount = round($requestedAmount, 2);

            if ($requestedAmount <= 0) {
                throw new RuntimeException('Nominal payout harus lebih besar dari 0.');
            }

            if (round($availableAmount, 2) !== $requestedAmount) {
                throw new RuntimeException('Partial payout belum didukung. Nominal payout harus sama dengan total saldo tersedia.');
            }

            $now = now();

            $payout = CommissionPayout::query()->create([
                'epi_channel_id' => $channel->id,
                'payout_number' => $this->generatePayoutNumber(),
                'total_amount' => (string) $requestedAmount,
                'status' => PayoutStatus::Paid,
                'notes' => $notes,
                'processed_by' => $actor->id,
                'processed_at' => $now,
                'approved_by' => $actor->id,
                'approved_at' => $now,
                'paid_by' => $actor->id,
                'paid_at' => $now,
                'metadata' => [
                    'source' => 'admin_pay_now',
                    'admin_id' => $actor->id,
                    'total_available_balance' => $availableAmount,
                    'payout_input_amount' => $requestedAmount,
                    'bank_account_snapshot' => [
                        'bank_name' => $channel->payout_bank_name,
                        'bank_account_number' => $channel->payout_bank_account_number,
                        'bank_account_holder_name' => $channel->payout_bank_account_holder_name,
                    ],
                    'commission_ids' => $commissions->pluck('id')->all(),
                ],
            ]);

            $updatedCount = Commission::query()
                ->whereIn('id', $commissions->pluck('id'))
                ->eligibleForPayout()
                ->update([
                    'commission_payout_id' => $payout->id,
                    'paid_at' => $now,
                    'status' => \App\Enums\CommissionStatus::Paid,
                ]);

            if ($updatedCount !== $commissions->count()) {
                throw new RuntimeException('Sebagian komisi berubah saat proses payout. Silakan coba lagi.');
            }

            return $payout->refresh();
        });
    }

    /**
     * @param  Collection<int, Commission>  $commissions
     */
    public function availableAmount(Collection $commissions): float
    {
        return (float) $commissions->sum('commission_amount');
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
