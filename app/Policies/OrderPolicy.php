<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        if ($order->user_id !== null && $order->user_id === $user->id) {
            return true;
        }

        $customerEmail = trim((string) $order->customer_email);

        return $customerEmail !== ''
            && strcasecmp($customerEmail, (string) $user->email) === 0;
    }

    public function cancel(User $user, Order $order): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        return $this->view($user, $order) && ! $order->isPaid();
    }
}

