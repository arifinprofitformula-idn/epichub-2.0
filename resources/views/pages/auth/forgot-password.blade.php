<x-layouts::auth :title="__('Forgot password')">
    <div class="flex flex-col gap-4 sm:gap-6">
        <x-auth-header :title="'Lupa Password'" :description="'Masukkan email Anda untuk mendapatkan link reset password.'" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="mx-auto flex w-full flex-col gap-3.5 sm:gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                type="email"
                required
                autofocus
                placeholder="email@anda.com"
            />

            <div class="flex items-center justify-center">
                <flux:button variant="primary" type="submit" class="epi-auth-btn w-full" data-test="email-password-reset-link-button">
                    <span style="text-transform:uppercase;letter-spacing:.06em;">Kirim Link Reset
                </flux:button>
            </div>
        </form>

        <div class="mx-auto w-full border-t border-slate-200 pt-4 text-center text-sm text-slate-500 sm:pt-7">
            <span class="uppercase tracking-wide">Kembali ke</span>
            <flux:link class="epi-auth-link ml-1 font-semibold uppercase tracking-wide" :href="route('login')">Halaman Login</flux:link>
        </div>
    </div>
</x-layouts::auth>
