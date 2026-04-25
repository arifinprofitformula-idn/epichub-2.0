<?php

namespace App\Http\Controllers;

use App\Actions\Orders\CancelOrderAction;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->with(['items', 'payments'])
            ->latest()
            ->paginate(10);

        return view('orders.index', [
            'orders' => $orders,
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

