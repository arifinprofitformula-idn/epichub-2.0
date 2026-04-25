<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Carbon;

class OrderNumberGenerator
{
    public static function nextOrderNumber(?Carbon $date = null): string
    {
        $date ??= Carbon::now();
        $prefix = 'ORD-'.$date->format('Ymd').'-';

        $latest = Order::query()
            ->where('order_number', 'like', $prefix.'%')
            ->orderByDesc('order_number')
            ->lockForUpdate()
            ->value('order_number');

        $next = $latest ? ((int) substr($latest, -6)) + 1 : 1;

        return $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public static function nextPaymentNumber(?Carbon $date = null): string
    {
        $date ??= Carbon::now();
        $prefix = 'PAY-'.$date->format('Ymd').'-';

        $latest = Payment::query()
            ->where('payment_number', 'like', $prefix.'%')
            ->orderByDesc('payment_number')
            ->lockForUpdate()
            ->value('payment_number');

        $next = $latest ? ((int) substr($latest, -6)) + 1 : 1;

        return $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
