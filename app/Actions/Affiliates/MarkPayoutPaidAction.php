<?php

namespace App\Actions\Affiliates;

use App\Enums\CommissionStatus;
use App\Enums\PayoutStatus;
use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MarkPayoutPaidAction
{
    public function execute(CommissionPayout $payout, User $actor): CommissionPayout
    {
        return DB::transaction(function () use ($payout, $actor): CommissionPayout {
            $payout = CommissionPayout::query()
                ->whereKey($payout->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($payout->status === PayoutStatus::Paid) {
                return $payout;
            }

            if (! in_array($payout->status, [PayoutStatus::Draft, PayoutStatus::Processing], true)) {
                throw new RuntimeException('Status payout tidak valid.');
            }

            $now = now();

            $payout->update([
                'status' => PayoutStatus::Paid,
                'paid_by' => $actor->id,
                'paid_at' => $now,
            ]);

            Commission::query()
                ->where('commission_payout_id', $payout->id)
                ->where('status', CommissionStatus::Approved)
                ->update([
                    'status' => CommissionStatus::Paid,
                    'paid_at' => $now,
                ]);

            return $payout->refresh();
        });
    }
}

