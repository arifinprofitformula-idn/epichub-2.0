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

        return $order->user_id !== null && $order->user_id === $user->id;
    }

    public function cancel(User $user, Order $order): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        return $order->user_id !== null && $order->user_id === $user->id && ! $order->isPaid();
    }
}

