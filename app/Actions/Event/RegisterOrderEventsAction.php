<?php

namespace App\Actions\Event;

use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Models\Event;
use App\Models\Order;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Support\Collection;
use RuntimeException;

class RegisterOrderEventsAction
{
    public function __construct(
        protected RegisterUserForEventAction $registerUserForEvent,
    ) {
    }

    /**
     * @return Collection<int, \App\Models\EventRegistration>
     */
    public function execute(Order $order, ?User $actor = null): Collection
    {
        $order->loadMissing(['user', 'items']);

        if ($order->status !== OrderStatus::Paid) {
            throw new RuntimeException('Order belum paid.');
        }

        if (! $order->user) {
            throw new RuntimeException('Order tidak memiliki user.');
        }

        $user = $order->user;

        $userProducts = UserProduct::query()
            ->where('order_id', $order->id)
            ->with(['product', 'orderItem', 'sourceProduct'])
            ->get();

        $results = collect();

        foreach ($userProducts as $userProduct) {
            $product = $userProduct->product;

            if (! $product) {
                continue;
            }

            $type = $product->product_type instanceof ProductType ? $product->product_type->value : (string) $product->product_type;

            if ($type !== ProductType::Event->value) {
                continue;
            }

            $event = Event::query()
                ->where('product_id', $product->id)
                ->first();

            if (! $event) {
                throw new RuntimeException('Event belum disiapkan untuk produk ini.');
            }

            $results->push($this->registerUserForEvent->execute(
                user: $user,
                event: $event,
                userProduct: $userProduct,
                order: $order,
                orderItem: $userProduct->orderItem,
                sourceProduct: $userProduct->sourceProduct,
                actor: $actor,
            ));
        }

        return $results;
    }
}

