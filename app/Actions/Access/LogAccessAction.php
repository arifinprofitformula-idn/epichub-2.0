<?php

namespace App\Actions\Access;

use App\Enums\AccessLogAction;
use App\Models\AccessLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\UserProduct;

class LogAccessAction
{
    public function execute(
        AccessLogAction $action,
        User $user,
        ?UserProduct $userProduct = null,
        ?Product $product = null,
        ?Order $order = null,
        ?User $actor = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        array $metadata = [],
    ): void {
        AccessLog::query()->create([
            'user_id' => $user->id,
            'product_id' => $product?->id ?? $userProduct?->product_id,
            'user_product_id' => $userProduct?->id,
            'order_id' => $order?->id ?? $userProduct?->order_id,
            'action' => $action,
            'actor_id' => $actor?->id,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'metadata' => $metadata !== [] ? $metadata : null,
            'created_at' => now(),
        ]);
    }
}

