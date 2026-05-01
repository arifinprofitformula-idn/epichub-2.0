<x-layouts::auth :title="__('Password Reset Sent')">
    <div class="flex flex-col gap-6">
        <x-auth-header 
            :title="'Permintaan Reset Password Berhasil'" 
            :description="'Permintaan reset password berhasil, silakan cek pesan masuk (inbox) email Anda untuk melanjutkan reset password.'" 
        />

        <!-- Success Message -->
        <div class="rounded-lg bg-blue-50/50 border border-blue-200/50 p-4 text-center">
            <p class="text-sm text-slate-700">
                Jika email tidak ditemukan, periksa folder <span class="font-semibold">spam</span> atau coba kirim ulang permintaan reset password.
            </p>
        </div>

        <!-- Primary Button: Back to Login -->
        <div class="flex items-center justify-center">
            <flux:button variant="primary" class="epi-auth-btn w-full" :href="route('login')">
                <span style="display:flex;align-items:center;justify-content:center;gap:0.5rem;width:100%;">
                    <span style="text-transform:uppercase;letter-spacing:.06em;">Kembali ke Login</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.9;flex-shrink:0;"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </span>
            </flux:button>
        </div>

        <!-- Secondary Link: Resend Email -->
        <div class="mx-auto w-full border-t border-slate-200 pt-7 text-center text-sm text-slate-500">
            <span class="uppercase tracking-wide">Ingin mengirim ulang?</span>
            <flux:link class="epi-auth-link ml-1 font-semibold uppercase tracking-wide" :href="route('password.request')">Kirim Ulang Email Reset Password</flux:link>
        </div>
    </div>
</x-layouts::auth>
