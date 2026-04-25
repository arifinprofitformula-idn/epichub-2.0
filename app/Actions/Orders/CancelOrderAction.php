<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CancelOrderAction
{
    public function execute(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $order = Order::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->isPaid()) {
                throw new RuntimeException('Order sudah lunas dan tidak bisa dibatalkan.');
            }

            if ($order->status === OrderStatus::Cancelled) {
                return $order;
            }

            $order->update([
                'status' => OrderStatus::Cancelled,
                'cancelled_at' => now(),
            ]);

            return $order->refresh();
        });
    }
}

