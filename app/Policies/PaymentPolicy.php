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

        return $this->ownsPayment($user, $payment);
    }

    public function uploadProof(User $user, Payment $payment): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        return $this->ownsPayment($user, $payment)
            && $payment->status === PaymentStatus::Pending;
    }

    private function ownsPayment(User $user, Payment $payment): bool
    {
        $payment->loadMissing('order');

        $ownerId = $payment->order?->user_id;
        if ($ownerId !== null && $ownerId === $user->id) {
            return true;
        }

        $customerEmail = trim((string) ($payment->order?->customer_email ?? ''));

        return $customerEmail !== ''
            && strcasecmp($customerEmail, (string) $user->email) === 0;
    }
}

