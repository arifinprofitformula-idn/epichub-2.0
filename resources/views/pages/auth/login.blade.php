<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="'Selamat Datang'" :description="'Masuk untuk melanjutkan proses belajar Anda.'" />

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
                    <flux:link class="epi-auth-link absolute top-0 text-sm font-semibold tracking-wide uppercase end-0" :href="route('password.request')">
                        Lupa Password?
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="'Ingat saya'" :checked="old('remember')" />

            <div class="flex items-center justify-center">
                <flux:button variant="primary" type="submit" class="epi-auth-btn w-full" data-test="login-button">
                    Masuk Sekarang
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
