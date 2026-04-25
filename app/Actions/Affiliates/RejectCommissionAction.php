<?php

namespace App\Actions\Affiliates;

use App\Enums\CommissionStatus;
use App\Models\Commission;
use App\Models\User;
use RuntimeException;

class RejectCommissionAction
{
    public function execute(Commission $commission, User $actor, ?string $reason): Commission
    {
        if (! in_array($commission->status, [CommissionStatus::Pending, CommissionStatus::Approved], true)) {
            throw new RuntimeException('Commission tidak dalam status yang bisa ditolak.');
        }

        $commission->update([
            'status' => CommissionStatus::Rejected,
            'rejected_by' => $actor->id,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return $commission->refresh();
    }
}

