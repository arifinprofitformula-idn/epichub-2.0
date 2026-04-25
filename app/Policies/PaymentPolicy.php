<?php

namespace App\Policies;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        $ownerId = $payment->order?->user_id;

        return $ownerId !== null && $ownerId === $user->id;
    }

    public function uploadProof(User $user, Payment $payment): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        $ownerId = $payment->order?->user_id;

        return $ownerId !== null
            && $ownerId === $user->id
            && $payment->status === PaymentStatus::Pending;
    }
}

