<?php

namespace App\Actions\Access;

use App\Enums\AccessLogAction;
use App\Enums\UserProductStatus;
use App\Models\AccessLog;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Support\Facades\DB;

class RevokeProductAccessAction
{
    public function execute(UserProduct $userProduct, User $actor, string $reason): UserProduct
    {
        return DB::transaction(function () use ($userProduct, $actor, $reason): UserProduct {
            $userProduct = UserProduct::query()
                ->with(['user', 'product', 'order'])
                ->whereKey($userProduct->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($userProduct->status === UserProductStatus::Revoked) {
                return $userProduct;
            }

            $now = now();

            $userProduct->update([
                'status' => UserProductStatus::Revoked,
                'revoked_by' => $actor->id,
                'revoked_at' => $now,
                'revoke_reason' => $reason,
            ]);

            AccessLog::query()->create([
                'user_id' => $userProduct->user_id,
                'product_id' => $userProduct->product_id,
                'user_product_id' => $userProduct->id,
                'order_id' => $userProduct->order_id,
                'action' => AccessLogAction::ManualRevoke,
                'actor_id' => $actor->id,
                'metadata' => [
                    'reason' => $reason,
                ],
                'created_at' => $now,
            ]);

            return $userProduct->refresh();
        });
    }
}

