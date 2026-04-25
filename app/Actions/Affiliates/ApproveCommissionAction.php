<?php

namespace App\Actions\Affiliates;

use App\Enums\CommissionStatus;
use App\Models\Commission;
use App\Models\User;
use RuntimeException;

class ApproveCommissionAction
{
    public function execute(Commission $commission, User $actor): Commission
    {
        if ($commission->status !== CommissionStatus::Pending) {
            throw new RuntimeException('Commission tidak dalam status pending.');
        }

        $commission->update([
            'status' => CommissionStatus::Approved,
            'approved_by' => $actor->id,
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        return $commission->refresh();
    }
}

