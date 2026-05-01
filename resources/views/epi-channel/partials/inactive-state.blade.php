<div class="grid gap-6">
    <x-ui.card class="overflow-hidden p-0">
        <div class="bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.18),transparent_34%),radial-gradient(circle_at_top_right,rgba(74,222,128,0.14),transparent_34%),linear-gradient(180deg,#ffffff_0%,#f3fbf6_100%)] px-6 py-7 md:px-8 md:py-9">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <div class="flex items-center gap-4">
                        <div class="flex size-16 items-center justify-center rounded-[1.4rem] bg-emerald-100 text-emerald-700 shadow-[0_10px_22px_rgba(16,185,129,0.14)]">
                            <svg viewBox="0 0 24 24" fill="none" class="size-8" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M12 3.75L4.75 7.25V12C4.75 16.1023 7.59367 19.9093 12 20.75C16.4063 19.9093 19.25 16.1023 19.25 12V7.25L12 3.75Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                <path d="M9.5 11.75L11.25 13.5L14.75 10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>

                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Ekosistem Bisnis</div>
                            <div class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 md:text-3xl">Buka Akses Anda ke Ekosistem EPI Channel</div>
                        </div>
                    </div>

                    <p class="mt-5 max-w-2xl text-sm leading-7 text-slate-600 md:text-base">
                        EPI Channel adalah jaringan retail bisnis emas dan perak fisik EPI. Melalui EPIC Hub, Anda dapat mengakses edukasi, materi promosi, produk digital, dan dashboard affiliate untuk mendukung pertumbuhan bisnis Anda.
                    </p>

                    <div class="mt-5 flex flex-wrap items-center gap-2">
                        <x-ui.badge variant="{{ ($channel?->status?->value ?? null) === 'suspended' ? 'danger' : 'warning' }}">
                            {{ $channel?->status?->label() ?? 'Belum Aktif' }}
                        </x-ui.badge>

                        @if (filled($referrerContact['sponsor_epic_code'] ?? null))
                            <x-ui.badge variant="info">
                                Pereferral {{ $referrerContact['sponsor_epic_code'] }}
                            </x-ui.badge>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.button variant="ghost" size="sm" :href="route('dashboard')">
                        Kembali ke dashboard
                    </x-ui.button>
                    <x-ui.button variant="secondary" size="sm" :href="route('profile.edit')">
                        Lengkapi Profil
                    </x-ui.button>
                </div>
            </div>

            <div class="mt-7">
                <div class="text-sm font-semibold text-slate-900">Cara Mengaktifkan EPI Channel</div>

                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    <div class="rounded-[1.35rem] border border-emerald-100/90 bg-white/88 p-4 shadow-[0_12px_26px_rgba(16,185,129,0.08)]">
                        <div class="flex items-center gap-3">
                            <div class="flex size-8 items-center justify-center rounded-full bg-emerald-700 text-xs font-bold text-white">1</div>
                            <div class="text-sm font-semibold text-slate-900">Registrasi melalui jaringan EPI</div>
                        </div>
                        <div class="mt-2 text-sm text-slate-500">Aktivasi EPI Channel dilakukan melalui proses resmi di jaringan EPI/OMS.</div>
                    </div>

                    <div class="rounded-[1.35rem] border border-emerald-100/90 bg-white/88 p-4 shadow-[0_12px_26px_rgba(16,185,129,0.08)]">
                        <div class="flex items-center gap-3">
                            <div class="flex size-8 items-center justify-center rounded-full bg-emerald-700 text-xs font-bold text-white">2</div>
                            <div class="text-sm font-semibold text-slate-900">Sinkronisasi ke EPIC Hub</div>
                        </div>
                        <div class="mt-2 text-sm text-slate-500">Setelah data aktif, akun Anda akan tersinkron dengan EPIC Hub.</div>
                    </div>

                    <div class="rounded-[1.35rem] border border-emerald-100/90 bg-white/88 p-4 shadow-[0_12px_26px_rgba(16,185,129,0.08)]">
                        <div class="flex items-center gap-3">
                            <div class="flex size-8 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">3</div>
                            <div class="text-sm font-semibold text-slate-900">Akses Dashboard EPI Channel</div>
                        </div>
                        <div class="mt-2 text-sm text-slate-500">Gunakan dashboard untuk edukasi, materi promosi, produk digital, dan aktivitas affiliate.</div>
                    </div>
                </div>
            </div>
        </div>
    </x-ui.card>

    @if (filled($referrerContact['sponsor_epic_code'] ?? null) || filled($referrerContact['sponsor_name'] ?? null))
        <x-ui.card class="p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="min-w-0">
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Tertarik Daftar EPI Channel?</div>
                    <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">
                        Silakan hubungi pereferral Anda untuk mengetahui info lebih lanjut sekarang juga melalui tombol berikut
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        @if (filled($referrerContact['sponsor_name'] ?? null))
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                <svg viewBox="0 0 24 24" fill="none" class="size-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M12 13C14.2091 13 16 11.2091 16 9C16 6.79086 14.2091 5 12 5C9.79086 5 8 6.79086 8 9C8 11.2091 9.79086 13 12 13Z" stroke="currentColor" stroke-width="1.6"/>
                                    <path d="M4 20C4.95 17.33 8.13 16 12 16C15.87 16 19.05 17.33 20 20" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                </svg>
                                {{ $referrerContact['sponsor_name'] }}
                            </span>
                        @endif
                        @if (filled($referrerContact['sponsor_epic_code'] ?? null))
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                <svg viewBox="0 0 24 24" fill="none" class="size-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <rect x="3.75" y="3.75" width="16.5" height="16.5" rx="2.25" stroke="currentColor" stroke-width="1.6"/>
                                    <path d="M8 12H16M8 8.5H16M8 15.5H13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                                ID EPIC: {{ $referrerContact['sponsor_epic_code'] }}
                            </span>
                        @endif
                        @if (($referrerContact['has_contact'] ?? false) && filled($referrerContact['whatsapp_number'] ?? null))
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/40 dark:text-green-300">
                                <svg viewBox="0 0 24 24" fill="none" class="size-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 13.6 3.41 15.1 4.13 16.4L3 21L7.7 19.9C8.97 20.6 10.44 21 12 21Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                </svg>
                                {{ $referrerContact['whatsapp_number'] }}
                            </span>
                        @endif
                    </div>
                </div>

                @if (($referrerContact['has_contact'] ?? false) && filled($referrerContact['whatsapp_url'] ?? null))
                    <x-ui.button variant="primary" size="lg" :href="$referrerContact['whatsapp_url']" target="_blank" rel="noopener noreferrer">
                        Hubungi via WhatsApp
                    </x-ui.button>
                @else
                    <div class="shrink-0 rounded-[var(--radius-lg)] border border-dashed border-zinc-200 bg-zinc-50 px-4 py-3 text-center dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">WhatsApp belum tersedia</div>
                        <div class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">Pereferral belum mengisi nomor</div>
                    </div>
                @endif
            </div>
        </x-ui.card>
    @else
        <x-ui.card class="p-6">
            <div class="rounded-[1.35rem] border border-dashed border-slate-200 bg-slate-50/80 p-5">
                <div class="text-sm font-semibold text-slate-900">Kontak pereferral belum tersedia</div>
                <div class="mt-2 text-sm leading-6 text-slate-500">
                    Pastikan pereferral Anda sudah mengisi nomor WhatsApp di profil EPIC Hub atau hubungi admin untuk bantuan lebih lanjut.
                </div>
            </div>
        </x-ui.card>
    @endif

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-ui.card class="overflow-hidden p-6">
            <div class="flex items-start gap-4">
                <div class="relative shrink-0">
                    <div class="absolute inset-0 translate-x-1.5 translate-y-2 rounded-[1.35rem] bg-emerald-900/10 blur-sm"></div>
                    <div class="relative flex size-16 items-center justify-center rounded-[1.35rem] border border-emerald-100 bg-[linear-gradient(145deg,#ecfdf5_0%,#d1fae5_55%,#a7f3d0_100%)] text-emerald-700 shadow-[inset_0_1px_0_rgba(255,255,255,0.9),0_14px_24px_rgba(16,185,129,0.14)]">
                        <svg viewBox="0 0 24 24" fill="none" class="size-8" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M4 8.5L12 4L20 8.5L12 13L4 8.5Z" fill="currentColor" fill-opacity=".18"/>
                            <path d="M4 8.5L12 13L20 8.5" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                            <path d="M12 13V20" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            <path d="M7 10.8V15.6L12 18.5L17 15.6V10.8" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Jaringan Retail Emas &amp; Perak EPI</div>
                    <div class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                        Terhubung dengan ekosistem bisnis emas dan perak fisik EPI yang dibangun untuk pertumbuhan jangka panjang.
                    </div>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="overflow-hidden p-6">
            <div class="flex items-start gap-4">
                <div class="relative shrink-0">
                    <div class="absolute inset-0 translate-x-1.5 translate-y-2 rounded-[1.35rem] bg-emerald-900/10 blur-sm"></div>
                    <div class="relative flex size-16 items-center justify-center rounded-[1.35rem] border border-emerald-100 bg-[linear-gradient(145deg,#f0fdf4_0%,#dcfce7_55%,#bbf7d0_100%)] text-emerald-700 shadow-[inset_0_1px_0_rgba(255,255,255,0.9),0_14px_24px_rgba(34,197,94,0.12)]">
                        <svg viewBox="0 0 24 24" fill="none" class="size-8" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M6.75 6.75H17.25C17.9404 6.75 18.5 7.30964 18.5 8V16.75C18.5 17.4404 17.9404 18 17.25 18H6.75C6.05964 18 5.5 17.4404 5.5 16.75V8C5.5 7.30964 6.05964 6.75 6.75 6.75Z" fill="currentColor" fill-opacity=".14" stroke="currentColor" stroke-width="1.6"/>
                            <path d="M8.5 4.75V8.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            <path d="M15.5 4.75V8.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            <path d="M8.5 11H15.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            <path d="M8.5 14H13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Edukasi &amp; Materi Promosi</div>
                    <div class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                        Dapatkan akses ke materi pembelajaran, konten promosi, dan panduan untuk mendukung aktivitas bisnis Anda.
                    </div>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="overflow-hidden p-6">
            <div class="flex items-start gap-4">
                <div class="relative shrink-0">
                    <div class="absolute inset-0 translate-x-1.5 translate-y-2 rounded-[1.35rem] bg-emerald-900/10 blur-sm"></div>
                    <div class="relative flex size-16 items-center justify-center rounded-[1.35rem] border border-emerald-100 bg-[linear-gradient(145deg,#ecfeff_0%,#d1fae5_52%,#a7f3d0_100%)] text-emerald-700 shadow-[inset_0_1px_0_rgba(255,255,255,0.9),0_14px_24px_rgba(20,184,166,0.14)]">
                        <svg viewBox="0 0 24 24" fill="none" class="size-8" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect x="5.5" y="6.5" width="13" height="11" rx="2.2" fill="currentColor" fill-opacity=".14" stroke="currentColor" stroke-width="1.6"/>
                            <path d="M9 17.5H15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            <path d="M10 9.75H14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            <path d="M8.5 12.25H15.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Akses Produk Digital EPIC Hub</div>
                    <div class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                        Nikmati produk digital, kelas, event, dan materi premium yang membantu meningkatkan kapasitas bisnis Anda.
                    </div>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="overflow-hidden p-6">
            <div class="flex items-start gap-4">
                <div class="relative shrink-0">
                    <div class="absolute inset-0 translate-x-1.5 translate-y-2 rounded-[1.35rem] bg-emerald-900/10 blur-sm"></div>
                    <div class="relative flex size-16 items-center justify-center rounded-[1.35rem] border border-emerald-100 bg-[linear-gradient(145deg,#f0fdf4_0%,#d1fae5_52%,#86efac_100%)] text-emerald-700 shadow-[inset_0_1px_0_rgba(255,255,255,0.9),0_14px_24px_rgba(22,163,74,0.14)]">
                        <svg viewBox="0 0 24 24" fill="none" class="size-8" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M6.5 17.5V13.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M12 17.5V9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M17.5 17.5V6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M5 18.5H19" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            <circle cx="6.5" cy="13.5" r="1.2" fill="currentColor"/>
                            <circle cx="12" cy="9.5" r="1.2" fill="currentColor"/>
                            <circle cx="17.5" cy="6.5" r="1.2" fill="currentColor"/>
                        </svg>
                    </div>
                </div>

                <div>
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Dashboard Affiliate Digital</div>
                    <div class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                        Gunakan link promosi, pantau aktivitas referral, komisi, dan payout untuk produk digital EPIC Hub.
                    </div>
                </div>
            </div>
        </x-ui.card>
    </div>
</div>
