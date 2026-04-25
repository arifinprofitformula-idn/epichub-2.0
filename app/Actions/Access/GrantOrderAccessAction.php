<?php

namespace App\Actions\Access;

use App\Enums\AccessLogAction;
use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;
use RuntimeException;

class GrantOrderAccessAction
{
    public function __construct(
        protected GrantProductAccessAction $grantProductAccess,
    ) {
    }

    /**
     * @return Collection<int, \App\Models\UserProduct>
     */
    public function execute(Order $order, ?User $actor = null): Collection
    {
        $order->loadMissing(['items.product', 'items.product.bundledProducts']);

        if ($order->status !== OrderStatus::Paid) {
            throw new RuntimeException('Order belum paid.');
        }

        if (! $order->user) {
            throw new RuntimeException('Order tidak memiliki user.');
        }

        $user = $order->user;

        $results = collect();

        foreach ($order->items as $item) {
            $product = $item->product;

            if (! $product) {
                continue;
            }

            $results->push($this->grantProductAccess->execute(
                user: $user,
                product: $product,
                order: $order,
                orderItem: $item,
                sourceProduct: null,
                actor: $actor,
                logAction: AccessLogAction::OrderPaidGrant,
                metadata: [],
            ));

            $isBundle = $product->product_type instanceof ProductType
                ? $product->product_type === ProductType::Bundle
                : (string) $product->product_type === ProductType::Bundle->value;

            if (! $isBundle) {
                continue;
            }

            foreach ($product->bundledProducts as $bundledProduct) {
                $results->push($this->grantProductAccess->execute(
                    user: $user,
                    product: $bundledProduct,
                    order: $order,
                    orderItem: $item,
                    sourceProduct: $product,
                    actor: $actor,
                    logAction: AccessLogAction::BundleChildGrant,
                    metadata: [
                        'source' => 'bundle',
                    ],
                ));
            }
        }

        return $results;
    }
}

