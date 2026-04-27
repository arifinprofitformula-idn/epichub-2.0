<?php

use App\Concerns\PasswordValidationRules;
use App\Actions\Oms\SendPasswordChangeToOmsAction;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Security settings')] class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public bool $canManageTwoFactor;

    public bool $twoFactorEnabled;

    public bool $requiresConfirmation;

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $this->canManageTwoFactor = Features::canManageTwoFactorAuthentication();

        if ($this->canManageTwoFactor) {
            if (Fortify::confirmsTwoFactorAuthentication() && is_null(auth()->user()->two_factor_confirmed_at)) {
                $disableTwoFactorAuthentication(auth()->user());
            }

            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
            $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }
    }

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(SendPasswordChangeToOmsAction $sendPasswordChangeToOms): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        $user = Auth::user();

        $user->update([
            'password' => $validated['password'],
        ]);

        $synced = $sendPasswordChangeToOms->execute($user, (string) $validated['password']);

        $this->reset('current_password', 'password', 'password_confirmation');

        Flux::toast(variant: 'success', text: __('Password updated.'));

        if (! $synced && config('epichub.oms.enabled', false)) {
            Flux::toast(variant: 'warning', text: 'Password sudah diubah, namun sinkronisasi ke OMS gagal. Silakan hubungi admin.');
        }
    }

    /**
     * Handle the two-factor authentication enabled event.
     */
    #[On('two-factor-enabled')]
    public function onTwoFactorEnabled(): void
    {
        $this->twoFactorEnabled = true;
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(auth()->user());

        $this->twoFactorEnabled = false;
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Security settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Keamanan')" :subheading="__('Kelola password dan autentikasi dua faktor untuk menjaga keamanan akun kamu.')">
        <div class="my-6 w-full space-y-5">

            {{-- Password Card --}}
            <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="h-1.5 w-full bg-gradient-to-r from-rose-500 via-orange-500 to-amber-500"></div>

                <div class="p-5">
                    <div class="mb-5 flex items-center gap-2">
                        <div class="flex size-7 items-center justify-center rounded-lg bg-rose-100 dark:bg-rose-900/40">
                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-rose-600 dark:text-rose-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M7.75 10V7.5C7.75 5.42893 9.42893 3.75 11.5 3.75H12.5C14.5711 3.75 16.25 5.42893 16.25 7.5V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <rect x="4.75" y="10.75" width="14.5" height="10.5" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                <circle cx="12" cy="16" r="1.25" fill="currentColor"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">Ubah Password</span>
                    </div>

                    <form method="POST" wire:submit="updatePassword" class="space-y-4">
                        {{-- Current Password --}}
                        <div>
                            <label for="current_password_input" class="mb-2 flex items-center gap-2">
                                <div class="flex size-5 shrink-0 items-center justify-center rounded-md bg-zinc-100 dark:bg-zinc-800">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-3 text-zinc-500 dark:text-zinc-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M7.75 10V7.5C7.75 5.42893 9.42893 3.75 11.5 3.75H12.5C14.5711 3.75 16.25 5.42893 16.25 7.5V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        <rect x="4.75" y="10.75" width="14.5" height="10.5" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Password Saat Ini</span>
                            </label>
                            <div class="relative">
                                <input
                                    id="current_password_input"
                                    wire:model="current_password"
                                    type="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm font-medium text-zinc-900 shadow-sm outline-none transition-all duration-200 placeholder:text-zinc-300 focus:border-rose-400 focus:bg-white focus:ring-2 focus:ring-rose-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-600 dark:focus:border-rose-500 dark:focus:bg-zinc-900 dark:focus:ring-rose-900/30"
                                />
                            </div>
                            @error('current_password')
                                <p class="mt-2 flex items-center gap-1.5 text-xs font-medium text-rose-600">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5" fill="currentColor" fill-opacity=".1"/><path d="M12 9V12.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="12" cy="15" r="0.75" fill="currentColor"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- New Password --}}
                        <div>
                            <label for="new_password_input" class="mb-2 flex items-center gap-2">
                                <div class="flex size-5 shrink-0 items-center justify-center rounded-md bg-emerald-100 dark:bg-emerald-900/40">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-3 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M12 5.75V18.25M5.75 12H18.25" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Password Baru</span>
                            </label>
                            <input
                                id="new_password_input"
                                wire:model="password"
                                type="password"
                                required
                                autocomplete="new-password"
                                placeholder="Min. 8 karakter"
                                class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm font-medium text-zinc-900 shadow-sm outline-none transition-all duration-200 placeholder:text-zinc-300 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-600 dark:focus:border-emerald-500 dark:focus:bg-zinc-900 dark:focus:ring-emerald-900/30"
                            />
                            @error('password')
                                <p class="mt-2 flex items-center gap-1.5 text-xs font-medium text-rose-600">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5" fill="currentColor" fill-opacity=".1"/><path d="M12 9V12.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="12" cy="15" r="0.75" fill="currentColor"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Confirm Password --}}
                        <div>
                            <label for="confirm_password_input" class="mb-2 flex items-center gap-2">
                                <div class="flex size-5 shrink-0 items-center justify-center rounded-md bg-blue-100 dark:bg-blue-900/40">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-3 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M5 12L10 17L19 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Konfirmasi Password Baru</span>
                            </label>
                            <input
                                id="confirm_password_input"
                                wire:model="password_confirmation"
                                type="password"
                                required
                                autocomplete="new-password"
                                placeholder="Ulangi password baru"
                                class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-2.5 text-sm font-medium text-zinc-900 shadow-sm outline-none transition-all duration-200 placeholder:text-zinc-300 focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-600 dark:focus:border-blue-500 dark:focus:bg-zinc-900 dark:focus:ring-blue-900/30"
                            />
                            @error('password_confirmation')
                                <p class="mt-2 flex items-center gap-1.5 text-xs font-medium text-rose-600">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5" fill="currentColor" fill-opacity=".1"/><path d="M12 9V12.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="12" cy="15" r="0.75" fill="currentColor"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Tip --}}
                        <div class="flex items-start gap-2 rounded-xl bg-amber-50 px-3 py-2.5 dark:bg-amber-900/15">
                            <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 size-3.5 shrink-0 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.4"/><path d="M12 11V16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="12" cy="8.5" r="0.75" fill="currentColor"/></svg>
                            <p class="text-xs text-amber-700/80 dark:text-amber-400/80">Gunakan minimal 8 karakter dengan kombinasi huruf, angka, dan simbol untuk password yang kuat.</p>
                        </div>

                        {{-- Submit --}}
                        <button
                            type="submit"
                            class="group relative flex w-full items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-rose-600 to-orange-500 px-6 py-3 text-sm font-semibold text-white shadow-md shadow-rose-200 transition-all duration-200 hover:from-rose-700 hover:to-orange-600 hover:shadow-lg hover:shadow-rose-200 active:scale-[0.98] dark:shadow-rose-900/30"
                            data-test="update-password-button"
                        >
                            <div wire:loading wire:target="updatePassword" class="absolute inset-0 flex items-center justify-center bg-rose-700">
                                <svg viewBox="0 0 24 24" fill="none" class="size-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" stroke-dasharray="30 56" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div wire:loading.remove wire:target="updatePassword" class="flex items-center gap-2">
                                <svg viewBox="0 0 24 24" fill="none" class="size-4 transition-transform duration-200 group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M7.75 10V7.5C7.75 5.42893 9.42893 3.75 11.5 3.75H12.5C14.5711 3.75 16.25 5.42893 16.25 7.5V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                    <rect x="4.75" y="10.75" width="14.5" height="10.5" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                    <circle cx="12" cy="16" r="1.25" fill="currentColor"/>
                                </svg>
                                Simpan Password Baru
                            </div>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Two-Factor Auth Card --}}
            @if ($canManageTwoFactor)
                <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900" wire:cloak>
                    <div class="h-1.5 w-full bg-gradient-to-r {{ $twoFactorEnabled ? 'from-emerald-500 to-teal-500' : 'from-zinc-300 to-zinc-400 dark:from-zinc-600 dark:to-zinc-700' }}"></div>

                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $twoFactorEnabled ? 'bg-emerald-100 dark:bg-emerald-900/40' : 'bg-zinc-100 dark:bg-zinc-800' }}">
                                    @if ($twoFactorEnabled)
                                        <svg viewBox="0 0 24 24" fill="none" class="size-5 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M12 3.75L4.75 7.25V12C4.75 16.1023 7.59367 19.9093 12 20.75C16.4063 19.9093 19.25 16.1023 19.25 12V7.25L12 3.75Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                            <path d="M9.5 11.75L11.25 13.5L14.75 10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" class="size-5 text-zinc-500 dark:text-zinc-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M12 3.75L4.75 7.25V12C4.75 16.1023 7.59367 19.9093 12 20.75C16.4063 19.9093 19.25 16.1023 19.25 12V7.25L12 3.75Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                            <path d="M12 9V13M12 15.5V16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-semibold text-zinc-900 dark:text-white">Autentikasi Dua Faktor</div>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                        @if ($twoFactorEnabled)
                                            2FA aktif — akun kamu terlindungi dengan PIN saat login.
                                        @else
                                            Tambahkan lapisan keamanan ekstra saat login dengan TOTP.
                                        @endif
                                    </p>
                                </div>
                            </div>

                            {{-- Status badge --}}
                            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold {{ $twoFactorEnabled ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                <span class="size-1.5 rounded-full {{ $twoFactorEnabled ? 'bg-emerald-500' : 'bg-zinc-400' }}"></span>
                                {{ $twoFactorEnabled ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>

                        <div class="mt-5 space-y-4">
                            @if ($twoFactorEnabled)
                                <div class="flex items-start gap-2 rounded-xl bg-emerald-50 px-3 py-2.5 dark:bg-emerald-900/15">
                                    <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 size-3.5 shrink-0 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.4" fill="currentColor" fill-opacity=".08"/><path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <p class="text-xs text-emerald-700/80 dark:text-emerald-400/80">Kamu akan diminta memasukkan PIN dari aplikasi TOTP (Google Authenticator, Authy, dll.) saat login.</p>
                                </div>

                                <livewire:pages::settings.two-factor.recovery-codes :$requiresConfirmation />

                                <button
                                    type="button"
                                    wire:click="disable"
                                    class="group inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-700 shadow-sm transition-all duration-200 hover:border-rose-300 hover:bg-rose-100 active:scale-[0.97] dark:border-rose-800/50 dark:bg-rose-900/20 dark:text-rose-400 dark:hover:bg-rose-900/40">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-4 transition-transform duration-200 group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M9.5 9.5L14.5 14.5M14.5 9.5L9.5 14.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                        <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5"/>
                                    </svg>
                                    Nonaktifkan 2FA
                                </button>
                            @else
                                <div class="flex items-start gap-2 rounded-xl bg-zinc-50 px-3 py-2.5 dark:bg-zinc-800/60">
                                    <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 size-3.5 shrink-0 text-zinc-400" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.4"/><path d="M12 11V16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="12" cy="8.5" r="0.75" fill="currentColor"/></svg>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Aktifkan 2FA untuk mendapatkan PIN dari aplikasi TOTP di ponsel kamu setiap kali login.</p>
                                </div>

                                <flux:modal.trigger name="two-factor-setup-modal">
                                    <button
                                        type="button"
                                        wire:click="$dispatch('start-two-factor-setup')"
                                        class="group inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 shadow-sm transition-all duration-200 hover:border-emerald-300 hover:bg-emerald-100 active:scale-[0.97] dark:border-emerald-800/50 dark:bg-emerald-900/20 dark:text-emerald-400 dark:hover:bg-emerald-900/40">
                                        <svg viewBox="0 0 24 24" fill="none" class="size-4 transition-transform duration-200 group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M12 3.75L4.75 7.25V12C4.75 16.1023 7.59367 19.9093 12 20.75C16.4063 19.9093 19.25 16.1023 19.25 12V7.25L12 3.75Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                            <path d="M9.5 11.75L11.25 13.5L14.75 10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        Aktifkan 2FA
                                    </button>
                                </flux:modal.trigger>

                                <livewire:pages::settings.two-factor-setup-modal :requires-confirmation="$requiresConfirmation" />
                            @endif
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </x-pages::settings.layout>
</section>
