<?php

namespace App\Providers;

use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\EventRegistration;
use App\Models\UserProduct;
use App\Observers\CommissionObserver;
use App\Observers\CommissionPayoutObserver;
use App\Observers\EventRegistrationObserver;
use App\Observers\UserProductObserver;
use App\Support\WindowsSafeFilesystem;
use Carbon\CarbonImmutable;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->app->singleton('files', fn () => new WindowsSafeFilesystem);
            $this->app->alias('files', Filesystem::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerObservers();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Schema::defaultStringLength(191);

        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(8)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function registerObservers(): void
    {
        EventRegistration::observe(EventRegistrationObserver::class);
        UserProduct::observe(UserProductObserver::class);
        Commission::observe(CommissionObserver::class);
        CommissionPayout::observe(CommissionPayoutObserver::class);
    }
}
