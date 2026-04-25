<?php

namespace App\Http\Controllers;

use App\Actions\Orders\CancelOrderAction;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $baseQuery = Order::query()
            ->where('user_id', $request->user()->id);

        $orders = (clone $baseQuery)
            ->with(['items.product', 'payments'])
            ->latest()
            ->paginate(10);

        return view('orders.index', [
            'orders' => $orders,
            'invoiceSummary' => [
                'total_orders' => (clone $baseQuery)->count(),
                'unpaid_orders' => (clone $baseQuery)
                    ->whereIn('status', [OrderStatus::Pending, OrderStatus::Unpaid])
                    ->count(),
            ],
        ]);
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['items.product', 'payments']);

        return view('orders.show', [
            'order' => $order,
        ]);
    }

    public function cancel(Order $order, CancelOrderAction $action): RedirectResponse
    {
        $this->authorize('cancel', $order);

        $action->execute($order);

        return redirect()->route('orders.show', $order);
    }
}

