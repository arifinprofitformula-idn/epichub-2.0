<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="'Selamat Datang'" :description="'Masuk untuk mengakses Akun Anda.'" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="mx-auto flex w-full flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="'Alamat Email'"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@anda.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="'Password'"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="'Masukkan password'"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="epi-auth-link absolute top-0 text-xs font-bold tracking-widest uppercase end-0" :href="route('password.request')">
                        Lupa Password?
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="'Ingat saya'" :checked="old('remember')" />

            <div class="flex items-center justify-center">
                <flux:button variant="primary" type="submit" class="epi-auth-btn w-full" data-test="login-button">
                    <span style="display:flex;align-items:center;justify-content:center;gap:0.5rem;width:100%;">
                        <span style="text-transform:uppercase;letter-spacing:.06em;">Masuk Sekarang</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.9;flex-shrink:0;"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </span>
                </flux:button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="mx-auto w-full border-t border-slate-200 pt-7 text-center text-sm text-slate-500">
                <span class="uppercase tracking-wide">Belum punya akun?</span>
                <flux:link class="epi-auth-link ml-1 font-semibold uppercase tracking-wide" :href="route('register')">Daftar Akun Baru</flux:link>
            </div>
        @endif
    </div>
</x-layouts::auth>
