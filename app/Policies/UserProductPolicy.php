<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserProduct;

class UserProductPolicy
{
    public function view(User $user, UserProduct $userProduct): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        return $userProduct->user_id === $user->id;
    }
}

