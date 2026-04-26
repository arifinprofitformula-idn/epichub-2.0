<?php

namespace App\Providers;

use App\Actions\Affiliates\ResolveCurrentReferralAction;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn (): Response => $this->noStoreView('pages::auth.login'));
        Fortify::verifyEmailView(fn (): Response => $this->noStoreView('pages::auth.verify-email'));
        Fortify::twoFactorChallengeView(fn (): Response => $this->noStoreView('pages::auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn (): Response => $this->noStoreView('pages::auth.confirm-password'));
        Fortify::registerView(fn (Request $request): Response => $this->noStoreView('pages::auth.register', [
            'referralChannel' => app(ResolveCurrentReferralAction::class)->execute($request),
        ]));
        Fortify::resetPasswordView(fn (): Response => $this->noStoreView('pages::auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn (): Response => $this->noStoreView('pages::auth.forgot-password'));
    }

    /**
     * Render auth pages with headers that prevent stale CSRF-bearing forms from being cached.
     */
    private function noStoreView(string $view, array $data = []): Response
    {
        return response()
            ->view($view, $data)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
