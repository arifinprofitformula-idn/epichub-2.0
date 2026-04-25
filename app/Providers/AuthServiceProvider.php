<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\UserProduct;
use App\Policies\OrderPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\UserProductPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Order::class => OrderPolicy::class,
        Payment::class => PaymentPolicy::class,
        UserProduct::class => UserProductPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}

