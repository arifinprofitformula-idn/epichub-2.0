<x-layouts::app :title="__('Bergabung Menjadi EPI Channel — EPIC Hub')">
<div class="overflow-x-hidden">

    {{-- ============================================================
         SECTION 1 — HERO
    ============================================================ --}}
    <section class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 px-4 py-16 text-white sm:py-24">
        <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
            <div class="absolute -top-32 -right-32 size-96 rounded-full bg-amber-400/5 blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 size-80 rounded-full bg-emerald-500/6 blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-3xl text-center">
            <div class="inline-flex items-center gap-2 rounded-full border border-amber-400/30 bg-amber-400/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-amber-400">
                <svg viewBox="0 0 24 24" fill="none" class="size-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M12 3.75L4.75 7.25V12C4.75 16.1023 7.59367 19.9093 12 20.75C16.4063 19.9093 19.25 16.1023 19.25 12V7.25L12 3.75Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                </svg>
                Ekosistem Retail Emas &amp; Perak Fisik
            </div>

            <h1 class="mt-6 text-3xl font-bold leading-tight tracking-tight sm:text-4xl lg:text-5xl">
                Bergabung Menjadi EPI Channel,<br>
                <span class="text-amber-400">Buka Akses ke Ekosistem Emas Perak Indonesia</span>
            </h1>

            <p class="mt-5 text-base leading-8 text-slate-300 sm:text-lg">
                Dapatkan akses edukasi, materi promosi, dashboard digital, dan pendampingan untuk mulai membangun peran Anda dalam jaringan retail emas dan perak fisik EPI.
            </p>

            <div class="mt-8">
                @if (filled($ctaWhatsappUrl))
                    <a href="{{ $ctaWhatsappUrl }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2.5 rounded-2xl bg-green-600 px-7 py-4 text-base font-semibold text-white shadow-[0_8px_30px_rgba(22,163,74,0.45)] transition hover:bg-green-700 active:scale-[0.98]">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.948-1.424A9.956 9.956 0 0 0 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2Zm5.152 13.656c-.216.608-1.267 1.16-1.73 1.192-.463.03-.476.357-2.997-.735-2.52-1.093-4.02-3.735-4.14-3.908-.12-.174-.976-1.376-.942-2.596.035-1.22.686-1.8.928-2.043.242-.243.527-.304.703-.308h.516c.165.004.39-.063.61.522.22.585.748 2.013.813 2.158.065.146.107.316.014.505-.092.19-.14.308-.28.474-.14.167-.294.374-.42.502-.138.14-.282.29-.12.568.161.278.72 1.18 1.547 1.912.892.791 1.648 1.034 1.88 1.147.233.113.368.095.504-.057.136-.153.583-.68.738-.913.155-.234.31-.195.524-.117.213.078 1.355.678 1.587.801.232.124.386.185.443.29.058.104.058.601-.158 1.208Z"/>
                        </svg>
                        Konsultasi Bergabung via WhatsApp
                    </a>
                    <p class="mt-3 text-xs text-slate-400">Tanyakan alur, manfaat, dan langkah bergabung langsung kepada pereferral Anda.</p>
                @else
                    <div class="inline-flex cursor-not-allowed items-center gap-2.5 rounded-2xl border border-slate-600 bg-slate-700/50 px-7 py-4 text-base font-semibold text-slate-400">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.948-1.424A9.956 9.956 0 0 0 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2Z"/>
                        </svg>
                        Konsultasi via WhatsApp — Segera Tersedia
                    </div>
                    <p class="mt-3 text-xs text-slate-500">Hubungi admin untuk informasi lebih lanjut.</p>
                @endif
            </div>

            {{-- Status badge untuk channel yang ditangguhkan --}}
            @if ($channel && $channel->status?->value === 'suspended')
                <div class="mt-5 inline-flex items-center gap-2 rounded-full bg-rose-600/20 px-4 py-2 text-xs font-semibold text-rose-300">
                    <svg viewBox="0 0 24 24" fill="none" class="size-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 8V12M12 16H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                    </svg>
                    Channel Anda saat ini ditangguhkan — hubungi pereferral untuk informasi lebih lanjut.
                </div>
            @endif

            {{-- Badge pereferral --}}
            @if (filled($referrerContact['sponsor_name'] ?? null))
                <div class="mt-5 inline-flex items-center gap-2 rounded-full bg-slate-700/60 px-4 py-2 text-xs text-slate-300">
                    <svg viewBox="0 0 24 24" fill="none" class="size-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 13C14.2091 13 16 11.2091 16 9C16 6.79086 14.2091 5 12 5C9.79086 5 8 6.79086 8 9C8 11.2091 9.79086 13 12 13Z" stroke="currentColor" stroke-width="1.6"/>
                        <path d="M4 20C4.95 17.33 8.13 16 12 16C15.87 16 19.05 17.33 20 20" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                    Diundang oleh: <span class="font-semibold text-white">{{ $referrerContact['sponsor_name'] }}</span>
                    @if (filled($referrerContact['sponsor_epic_code'] ?? null))
                        <span class="text-amber-400/70">({{ $referrerContact['sponsor_epic_code'] }})</span>
                    @endif
                </div>
            @endif
        </div>
    </section>

    {{-- ============================================================
         SECTION 2 — PROBLEM
    ============================================================ --}}
    <section class="bg-slate-50 px-4 py-14 sm:py-20">
        <div class="mx-auto max-w-4xl">
            <div class="mb-2 text-xs font-semibold uppercase tracking-widest text-amber-600">Apakah Anda Merasakannya?</div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                Ingin Memulai Bisnis Emas dan Perak,<br class="hidden sm:block">
                Tapi Masih Bingung Harus Mulai dari Mana?
            </h2>
            <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">
                Banyak orang tertarik dengan emas dan perak, tetapi belum memiliki panduan, materi edukasi, sistem digital, dan pendampingan yang jelas. Akhirnya niat baik tertunda karena langkah pertama terasa rumit.
            </p>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                @foreach ([
                    'Belum memahami produk emas dan perak fisik yang tepat untuk ditawarkan.',
                    'Bingung membuat edukasi dan promosi yang dipercaya oleh calon pelanggan.',
                    'Tidak punya sistem untuk melihat prospek, order, dan komisi secara terpusat.',
                    'Butuh arahan nyata agar langkah awal lebih terstruktur dan tidak asal jalan.',
                ] as $problem)
                    <div class="flex items-start gap-3 rounded-2xl border border-red-100 bg-white px-5 py-4 shadow-sm">
                        <div class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-500">
                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M12 8V12M12 16H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <p class="text-sm leading-6 text-slate-700">{{ $problem }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
         SECTION 3 — SOLUTION
    ============================================================ --}}
    <section class="bg-white px-4 py-14 sm:py-20">
        <div class="mx-auto max-w-4xl">
            <div class="mb-2 text-xs font-semibold uppercase tracking-widest text-amber-600">Solusi</div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                EPI Channel Membantu Anda Memulai<br class="hidden sm:block">
                dengan Ekosistem yang Lebih Tertata
            </h2>
            <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">
                Melalui EPIC Hub, Anda mendapatkan akses ke edukasi, materi promosi, dashboard digital, informasi produk, dan pendampingan dari pereferral agar proses memulai lebih terarah.
            </p>

            <div class="mt-8 grid gap-5 sm:grid-cols-2">
                @foreach ([
                    [
                        'title' => 'Edukasi Terarah',
                        'desc'  => 'Pelajari dasar produk, cara komunikasi, dan langkah awal sebagai EPI Channel.',
                        'icon'  => '<path d="M12 3.75L4.75 7.25V12C4.75 16.1023 7.59367 19.9093 12 20.75C16.4063 19.9093 19.25 16.1023 19.25 12V7.25L12 3.75Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9.5 11.75L11.25 13.5L14.75 10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
                    ],
                    [
                        'title' => 'Materi Promosi',
                        'desc'  => 'Gunakan materi yang disiapkan untuk membantu edukasi calon pelanggan dengan lebih mudah.',
                        'icon'  => '<rect x="5.5" y="6.5" width="13" height="11" rx="2.2" fill="currentColor" fill-opacity=".14" stroke="currentColor" stroke-width="1.6"/><path d="M9 17.5H15" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M10 9.75H14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M8.5 12.25H15.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
                    ],
                    [
                        'title' => 'Dashboard Digital',
                        'desc'  => 'Pantau link, kunjungan, order, komisi, dan aktivitas affiliate melalui EPIC Hub.',
                        'icon'  => '<path d="M6.5 17.5V13.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M12 17.5V9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M17.5 17.5V6.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M5 18.5H19" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="6.5" cy="13.5" r="1.2" fill="currentColor"/><circle cx="12" cy="9.5" r="1.2" fill="currentColor"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor"/>',
                    ],
                    [
                        'title' => 'Pendampingan Pereferral',
                        'desc'  => 'Dapatkan arahan awal dari pereferral sebelum dan setelah bergabung untuk memulai lebih terarah.',
                        'icon'  => '<path d="M12 13C14.2091 13 16 11.2091 16 9C16 6.79086 14.2091 5 12 5C9.79086 5 8 6.79086 8 9C8 11.2091 9.79086 13 12 13Z" stroke="currentColor" stroke-width="1.6"/><path d="M4 20C4.95 17.33 8.13 16 12 16C15.87 16 19.05 17.33 20 20" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
                    ],
                ] as $item)
                    <div class="flex items-start gap-4 rounded-2xl border border-amber-100 bg-amber-50 p-5">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                            <svg viewBox="0 0 24 24" fill="none" class="size-6" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                {!! $item['icon'] !!}
                            </svg>
                        </div>
                        <div>
                            <div class="font-semibold text-slate-900">{{ $item['title'] }}</div>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $item['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
         SECTION 4 — WHAT YOU GET
    ============================================================ --}}
    <section class="bg-slate-50 px-4 py-14 sm:py-20">
        <div class="mx-auto max-w-4xl">
            <div class="mb-2 text-xs font-semibold uppercase tracking-widest text-amber-600">Yang Anda Dapatkan</div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                Apa yang Anda Dapatkan Saat Bergabung?
            </h2>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                @foreach ([
                    ['num' => '01', 'title' => 'Akses Materi Onboarding', 'desc' => 'Panduan awal untuk memahami ekosistem EPI Channel dan langkah memulai.'],
                    ['num' => '02', 'title' => 'Dashboard EPIC Hub',       'desc' => 'Area digital untuk melihat produk, link referral, order, komisi, dan materi promosi.'],
                    ['num' => '03', 'title' => 'Materi Edukasi & Promosi',  'desc' => 'Konten pendukung untuk membantu Anda menjelaskan produk kepada calon pelanggan.'],
                    ['num' => '04', 'title' => 'Akses Produk dan Program',  'desc' => 'Informasi produk digital, edukasi, event, dan program yang tersedia di EPIC Hub.'],
                    ['num' => '05', 'title' => 'Pendampingan Awal',         'desc' => 'Pereferral membantu menjelaskan alur, pilihan paket, dan langkah mulai yang tepat.'],
                ] as $item)
                    <div class="flex items-start gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-sm">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-xs font-bold text-white">
                            {{ $item['num'] }}
                        </div>
                        <div>
                            <div class="font-semibold text-slate-900">{{ $item['title'] }}</div>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $item['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
         SECTION 5 — HOW IT WORKS
    ============================================================ --}}
    <section class="bg-white px-4 py-14 sm:py-20">
        <div class="mx-auto max-w-4xl">
            <div class="mb-2 text-xs font-semibold uppercase tracking-widest text-amber-600">Langkah-Langkah</div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                Cara Bergabung Menjadi EPI Channel
            </h2>

            <div class="mt-8 space-y-4">
                @foreach ([
                    ['step' => '1', 'title' => 'Klik Tombol WhatsApp',             'desc' => 'Hubungi pereferral Anda untuk mendapatkan penjelasan awal mengenai EPI Channel.'],
                    ['step' => '2', 'title' => 'Konsultasi Paket & Kebutuhan',     'desc' => 'Pahami pilihan paket, manfaat, dan alur bergabung bersama pereferral Anda.'],
                    ['step' => '3', 'title' => 'Registrasi Melalui Link Pereferral','desc' => 'Data Anda akan terhubung dengan pereferral yang membimbing proses bergabung.'],
                    ['step' => '4', 'title' => 'Akses EPIC Hub',                   'desc' => 'Masuk ke dashboard untuk mengakses edukasi, materi promosi, dan fitur EPI Channel.'],
                    ['step' => '5', 'title' => 'Mulai Edukasi dan Promosi',        'desc' => 'Gunakan materi dan sistem yang tersedia untuk mulai bergerak secara terarah.'],
                ] as $s)
                    <div class="flex items-start gap-4 rounded-2xl border border-slate-100 bg-slate-50 px-5 py-4">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-bold text-white">
                            {{ $s['step'] }}
                        </div>
                        <div class="pt-0.5">
                            <div class="font-semibold text-slate-900">{{ $s['title'] }}</div>
                            <p class="mt-0.5 text-sm leading-6 text-slate-600">{{ $s['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            @if (filled($ctaWhatsappUrl))
                <div class="mt-8 text-center">
                    <p class="mb-3 text-sm text-slate-500">Siap memulai? Konsultasi gratis langkah pertama Anda.</p>
                    <a href="{{ $ctaWhatsappUrl }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.948-1.424A9.956 9.956 0 0 0 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2Zm5.152 13.656c-.216.608-1.267 1.16-1.73 1.192-.463.03-.476.357-2.997-.735-2.52-1.093-4.02-3.735-4.14-3.908-.12-.174-.976-1.376-.942-2.596.035-1.22.686-1.8.928-2.043.242-.243.527-.304.703-.308h.516c.165.004.39-.063.61.522.22.585.748 2.013.813 2.158.065.146.107.316.014.505-.092.19-.14.308-.28.474-.14.167-.294.374-.42.502-.138.14-.282.29-.12.568.161.278.72 1.18 1.547 1.912.892.791 1.648 1.034 1.88 1.147.233.113.368.095.504-.057.136-.153.583-.68.738-.913.155-.234.31-.195.524-.117.213.078 1.355.678 1.587.801.232.124.386.185.443.29.058.104.058.601-.158 1.208Z"/>
                        </svg>
                        Saya Ingin Dibimbing Lewat WhatsApp
                    </a>
                </div>
            @endif
        </div>
    </section>

    {{-- ============================================================
         SECTION 6 — WHY NOW
    ============================================================ --}}
    <section class="border-y border-amber-100 bg-amber-50 px-4 py-14 sm:py-20">
        <div class="mx-auto max-w-4xl">
            <div class="mb-2 text-xs font-semibold uppercase tracking-widest text-amber-600">Kenapa Sekarang?</div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                Kenapa Sebaiknya Mulai dari Sekarang?
            </h2>
            <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">
                Membangun kepercayaan dalam bisnis emas dan perak tidak bisa instan. Semakin cepat Anda belajar, memahami produk, dan membangun relasi edukatif, semakin siap Anda menjalankan peran sebagai EPI Channel.
            </p>

            <ul class="mt-8 space-y-3">
                @foreach ([
                    'Anda bisa mulai memahami ekosistem EPI Channel sejak awal sebelum terlambat.',
                    'Anda punya waktu membangun edukasi dan relasi yang kuat dengan calon pelanggan.',
                    'Anda tidak perlu menunggu semuanya sempurna untuk mulai belajar dan bergerak.',
                    'Pereferral bisa membantu menjelaskan langkah pertama agar tidak berjalan sendiri.',
                ] as $point)
                    <li class="flex items-start gap-3">
                        <div class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-amber-200 text-amber-700">
                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M5 12L10 17L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span class="text-base leading-7 text-slate-700">{{ $point }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ============================================================
         SECTION 7 — TRUST
    ============================================================ --}}
    <section class="bg-slate-900 px-4 py-14 text-white sm:py-20">
        <div class="mx-auto max-w-4xl">
            <div class="mb-2 text-xs font-semibold uppercase tracking-widest text-amber-400">Dukungan Platform</div>
            <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">
                Didukung Ekosistem Digital EPIC Hub
            </h2>
            <p class="mt-4 max-w-2xl text-base leading-7 text-slate-300">
                EPIC Hub dirancang untuk mendukung aktivitas EPI Channel melalui akses materi, dashboard affiliate, manajemen link, laporan order, komisi, payout, event, course, dan produk digital pendukung.
            </p>

            <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    'Dashboard affiliate terintegrasi untuk memantau aktivitas referral.',
                    'Materi promosi siap pakai, lebih mudah diakses kapan saja.',
                    'Riwayat order dan komisi yang lebih transparan dan terstruktur.',
                    'Akses edukasi tersentral di satu platform EPIC Hub.',
                    'Data referral mengikuti sistem referral lock agar lebih tertata.',
                ] as $trust)
                    <div class="flex items-start gap-3 rounded-xl border border-white/10 bg-white/5 px-4 py-3.5">
                        <div class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white">
                            <svg viewBox="0 0 24 24" fill="none" class="size-3" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M5 12L10 17L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <p class="text-sm leading-6 text-slate-300">{{ $trust }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
         SECTION 8 — FAQ
    ============================================================ --}}
    <section class="bg-white px-4 py-14 sm:py-20">
        <div class="mx-auto max-w-3xl">
            <div class="mb-2 text-center text-xs font-semibold uppercase tracking-widest text-amber-600">FAQ</div>
            <h2 class="text-center text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                Pertanyaan yang Sering Ditanyakan
            </h2>

            <div class="mt-10 space-y-3">
                @foreach ([
                    [
                        'q' => 'Apakah saya harus punya pengalaman jualan emas?',
                        'a' => 'Tidak harus. Anda bisa mulai dengan memahami edukasi dasar, alur bisnis, dan materi yang tersedia. Pereferral akan membantu menjelaskan langkah awal yang perlu dilakukan.',
                    ],
                    [
                        'q' => 'Apakah EPI Channel ini bisnis dropship?',
                        'a' => 'Tidak. EPI Channel diposisikan sebagai jaringan retail bisnis emas dan perak fisik EPI, bukan sekadar dropship atau link-only selling. Ada ekosistem, edukasi, dan pendampingan yang menyertai.',
                    ],
                    [
                        'q' => 'Apakah saya langsung mendapatkan dashboard EPIC Hub?',
                        'a' => 'Setelah proses bergabung dan registrasi selesai, Anda dapat mengakses EPIC Hub sesuai paket dan akses yang diberikan oleh pereferral dan sistem.',
                    ],
                    [
                        'q' => 'Apakah ada komisi affiliate?',
                        'a' => 'Komisi mengikuti aturan program yang berlaku di EPIC Hub dan akan terlihat melalui dashboard jika Anda memenuhi ketentuan yang berlaku.',
                    ],
                    [
                        'q' => 'Apakah hasil bisnis dijamin?',
                        'a' => 'Tidak ada jaminan hasil. Performa bergantung pada pemahaman produk, aktivitas edukasi, konsistensi, dan proses follow-up yang dilakukan oleh masing-masing EPI Channel.',
                    ],
                ] as $faq)
                    <details class="group rounded-2xl border border-slate-200 bg-slate-50/60">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 font-semibold text-slate-900 [&::-webkit-details-marker]:hidden">
                            {{ $faq['q'] }}
                            <svg viewBox="0 0 24 24" fill="none" class="size-4 shrink-0 text-slate-500 transition duration-200 group-open:rotate-180" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </summary>
                        <div class="px-5 pb-5 pt-0 text-sm leading-7 text-slate-600">
                            {{ $faq['a'] }}
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
         SECTION 9 — FINAL CTA
         pb-20 md:pb-24 untuk clearance sticky bar mobile
    ============================================================ --}}
    <section class="bg-gradient-to-br from-slate-900 to-slate-800 px-4 pb-20 pt-14 text-white sm:pt-20 md:pb-24">
        <div class="mx-auto max-w-3xl text-center">
            <div class="mb-2 text-xs font-semibold uppercase tracking-widest text-amber-400">Mulai Sekarang</div>
            <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">
                Siap Memulai Perjalanan Anda<br class="hidden sm:block">
                sebagai EPI Channel?
            </h2>
            <p class="mt-4 text-base leading-7 text-slate-300">
                Diskusikan dulu dengan pereferral Anda agar memahami paket, alur, dan langkah terbaik sebelum bergabung.
            </p>

            @if (filled($referrerContact['sponsor_name'] ?? null) || filled($referrerContact['sponsor_epic_code'] ?? null))
                <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
                    @if (filled($referrerContact['sponsor_name'] ?? null))
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-slate-200">
                            {{ $referrerContact['sponsor_name'] }}
                        </span>
                    @endif
                    @if (filled($referrerContact['sponsor_epic_code'] ?? null))
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-400/15 px-3 py-1.5 text-xs font-semibold text-amber-300">
                            {{ $referrerContact['sponsor_epic_code'] }}
                        </span>
                    @endif
                </div>
            @endif

            <div class="mt-8">
                @if (filled($ctaWhatsappUrl))
                    <a href="{{ $ctaWhatsappUrl }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2.5 rounded-2xl bg-green-600 px-8 py-4 text-base font-semibold text-white shadow-[0_8px_30px_rgba(22,163,74,0.4)] transition hover:bg-green-700 active:scale-[0.98]">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.948-1.424A9.956 9.956 0 0 0 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2Zm5.152 13.656c-.216.608-1.267 1.16-1.73 1.192-.463.03-.476.357-2.997-.735-2.52-1.093-4.02-3.735-4.14-3.908-.12-.174-.976-1.376-.942-2.596.035-1.22.686-1.8.928-2.043.242-.243.527-.304.703-.308h.516c.165.004.39-.063.61.522.22.585.748 2.013.813 2.158.065.146.107.316.014.505-.092.19-.14.308-.28.474-.14.167-.294.374-.42.502-.138.14-.282.29-.12.568.161.278.72 1.18 1.547 1.912.892.791 1.648 1.034 1.88 1.147.233.113.368.095.504-.057.136-.153.583-.68.738-.913.155-.234.31-.195.524-.117.213.078 1.355.678 1.587.801.232.124.386.185.443.29.058.104.058.601-.158 1.208Z"/>
                        </svg>
                        Konsultasi Bergabung via WhatsApp
                    </a>
                    <p class="mt-3 text-xs text-slate-400">Klik tombol ini untuk terhubung langsung dengan pereferral Anda.</p>
                @else
                    <div class="inline-flex cursor-not-allowed items-center gap-2.5 rounded-2xl border border-slate-600 bg-slate-700/50 px-8 py-4 text-base font-semibold text-slate-400">
                        Konsultasi via WhatsApp — Segera Tersedia
                    </div>
                @endif
            </div>
        </div>
    </section>

</div>

{{-- ============================================================
     SECTION 10 — STICKY MOBILE CTA
     Hanya tampil di mobile (md:hidden), di luar div scrollable
============================================================ --}}
@if (filled($ctaWhatsappUrl))
    <div class="fixed inset-x-0 bottom-0 z-50 md:hidden">
        <div class="border-t border-green-700/30 bg-green-600 shadow-[0_-8px_30px_rgba(22,163,74,0.35)]">
            <a href="{{ $ctaWhatsappUrl }}" target="_blank" rel="noopener noreferrer"
               class="flex items-center justify-center gap-2.5 px-4 py-3.5 text-sm font-semibold text-white">
                <svg viewBox="0 0 24 24" fill="currentColor" class="size-5 shrink-0" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M12 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.948-1.424A9.956 9.956 0 0 0 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2Zm5.152 13.656c-.216.608-1.267 1.16-1.73 1.192-.463.03-.476.357-2.997-.735-2.52-1.093-4.02-3.735-4.14-3.908-.12-.174-.976-1.376-.942-2.596.035-1.22.686-1.8.928-2.043.242-.243.527-.304.703-.308h.516c.165.004.39-.063.61.522.22.585.748 2.013.813 2.158.065.146.107.316.014.505-.092.19-.14.308-.28.474-.14.167-.294.374-.42.502-.138.14-.282.29-.12.568.161.278.72 1.18 1.547 1.912.892.791 1.648 1.034 1.88 1.147.233.113.368.095.504-.057.136-.153.583-.68.738-.913.155-.234.31-.195.524-.117.213.078 1.355.678 1.587.801.232.124.386.185.443.29.058.104.058.601-.158 1.208Z"/>
                </svg>
                <div>
                    <div>Chat Pereferral Sekarang</div>
                    <div class="text-xs font-normal text-green-200">Tanya alur bergabung EPI Channel</div>
                </div>
            </a>
        </div>
    </div>
@endif

</x-layouts::app>
