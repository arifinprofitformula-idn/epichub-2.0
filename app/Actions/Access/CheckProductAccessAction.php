<?php

namespace App\Actions\Access;

use App\Models\Product;
use App\Models\User;
use App\Models\UserProduct;

class CheckProductAccessAction
{
    public function execute(User $user, Product|UserProduct $subject): ?UserProduct
    {
        if ($subject instanceof UserProduct) {
            if ($subject->user_id !== $user->id) {
                return null;
            }

            if (! $subject->isActive()) {
                return null;
            }

            if ($subject->revoked_at !== null) {
                return null;
            }

            return $subject;
        }

        return UserProduct::query()
            ->where('user_id', $user->id)
            ->where('product_id', $subject->id)
            ->whereNull('revoked_at')
            ->active()
            ->latest('granted_at')
            ->first();
    }
}
