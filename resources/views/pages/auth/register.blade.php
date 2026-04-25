<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="'Buat Akun Baru'" :description="'Daftar untuk mulai mengakses program belajar Anda.'" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="mx-auto flex w-full flex-col gap-6">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="'Nama Lengkap'"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="'Nama lengkap'"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="'Alamat Email'"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@anda.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="'Password'"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="'Buat password'"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="'Konfirmasi Password'"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="'Ulangi password'"
                viewable
            />

            <div class="flex items-center justify-center">
                <flux:button type="submit" variant="primary" class="epi-auth-btn w-full" data-test="register-user-button">
                    Daftar Sekarang
                </flux:button>
            </div>
        </form>

        <div class="mx-auto w-full border-t border-slate-200 pt-7 text-center text-sm text-slate-500">
            <span class="uppercase tracking-wide">Sudah punya akun?</span>
            <flux:link class="epi-auth-link ml-1 font-semibold uppercase tracking-wide" :href="route('login')" wire:navigate>Masuk</flux:link>
        </div>
    </div>
</x-layouts::auth>
