<x-layouts::public title="EPIC Hub Premium">
    <section class="mx-auto max-w-[var(--container-5xl)] px-4 py-8 md:py-10">
        <div class="rounded-[2rem] border border-slate-200 bg-white/90 p-8 shadow-[0_20px_50px_rgba(15,23,42,0.09)] md:p-12">
            <div class="grid gap-10 md:grid-cols-2 md:items-center">
                <div>
                    <x-ui.badge variant="info">Platform Belajar Digital</x-ui.badge>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tight text-slate-900 md:text-5xl">
                        Pusat Produk Digital Premium, Kelas, Event, dan Peluang Penghasilan
                    </h1>
                    <p class="mt-4 text-base leading-relaxed text-slate-600">
                        Satu platform untuk belajar, membeli produk digital, mengikuti event, dan bertumbuh bersama ekosistem EPIC Hub Premium.
                    </p>

                    <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <x-ui.button variant="primary" size="lg" :href="route('register')">
                            Mulai Sekarang
                        </x-ui.button>
                        <x-ui.button variant="ghost" size="lg" :href="route('login')">
                            Masuk Akun
                        </x-ui.button>
                    </div>

                    <div class="mt-8 grid gap-3 text-sm text-slate-600 sm:grid-cols-2">
                        <div class="flex items-start gap-2">
                            <span class="mt-0.5 inline-flex size-5 items-center justify-center rounded-full bg-amber-100 text-amber-900">
                                <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 7L10.5 16.5L4 10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            <div>Akses terpusat untuk produk, kelas, dan event</div>
                        </div>
                        <div class="flex items-start gap-2">
                            <span class="mt-0.5 inline-flex size-5 items-center justify-center rounded-full bg-amber-100 text-amber-900">
                                <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20 7L10.5 16.5L4 10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            <div>UI clean, mobile-friendly, dan mudah dipahami</div>
                        </div>
                    </div>
                </div>

                <div class="md:justify-self-end">
                    <x-ui.card class="p-6 shadow-[var(--shadow-card)]">
                        <div class="text-sm font-semibold text-slate-900">Preview pengalaman</div>
                        <div class="mt-3 grid gap-3">
                            <div class="rounded-[var(--radius-xl)] border border-zinc-200/70 p-4">
                                <div class="text-xs text-slate-500">Dashboard</div>
                                <div class="mt-1 text-sm font-semibold text-slate-900">Ringkasan akses & aktivitas</div>
                            </div>
                            <div class="rounded-[var(--radius-xl)] border border-zinc-200/70 p-4">
                                <div class="text-xs text-slate-500">Library</div>
                                <div class="mt-1 text-sm font-semibold text-slate-900">Produk dimiliki & kelas aktif</div>
                            </div>
                            <div class="rounded-[var(--radius-xl)] border border-zinc-200/70 p-4">
                                <div class="text-xs text-slate-500">Growth</div>
                                <div class="mt-1 text-sm font-semibold text-slate-900">Teaser EPI Channel</div>
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </div>
    </section>

    <section id="produk" class="mx-auto max-w-[var(--container-5xl)] px-4 py-14">
        <x-ui.section-header
            eyebrow="Yang tersedia"
            title="Ekosistem produk digital premium"
            description="Ebook, ecourse, event, membership, dan bundle akan hadir bertahap. Saat ini ditampilkan sebagai placeholder."
        />

        <div class="mt-8 grid gap-4 md:grid-cols-3">
            <x-ui.card class="p-6">
                <x-ui.badge variant="neutral">Produk Digital</x-ui.badge>
                <div class="mt-3 text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">Ebook & Digital File</div>
                <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Konten praktis, ringkas, dan siap dipakai.</div>
            </x-ui.card>
            <x-ui.card class="p-6">
                <x-ui.badge variant="neutral">Ecourse</x-ui.badge>
                <div class="mt-3 text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">Kelas Premium</div>
                <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Belajar bertahap dengan materi yang terstruktur.</div>
            </x-ui.card>
            <x-ui.card class="p-6">
                <x-ui.badge variant="neutral">Event</x-ui.badge>
                <div class="mt-3 text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">Event Premium</div>
                <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Ikut event dan dapatkan insight langsung.</div>
            </x-ui.card>
        </div>
    </section>

    <section class="mx-auto max-w-[var(--container-5xl)] px-4 pb-14">
        <x-ui.section-header
            eyebrow="Preview"
            title="Produk unggulan"
            description="Produk yang sedang dipilih sebagai unggulan. Jika belum ada, tampilkan placeholder."
        />

        <div class="mt-8 grid gap-4 md:grid-cols-3">
            @php($featured = $featuredProducts ?? collect())

            @if ($featured->count() > 0)
                @foreach ($featured as $product)
                    <a href="{{ route('catalog.products.show', $product->slug) }}" class="group">
                        <x-ui.card class="h-full overflow-hidden">
                            <div class="aspect-[16/10] bg-zinc-100 dark:bg-zinc-800">
                                @if (filled($product->thumbnail))
                                    <img
                                        src="{{ asset('storage/'.$product->thumbnail) }}"
                                        alt="{{ $product->title }}"
                                        class="h-full w-full object-cover"
                                        loading="lazy"
                                    />
                                @else
                                    <div class="flex h-full items-center justify-center text-xs text-zinc-500 dark:text-zinc-400">
                                        Tanpa thumbnail
                                    </div>
                                @endif
                            </div>

                            <div class="p-6">
                                <div class="flex items-center justify-between gap-3">
                                    <x-ui.badge variant="info">{{ $product->product_type?->label() ?? $product->product_type }}</x-ui.badge>
                                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                        Rp {{ number_format((float) $product->effective_price, 0, ',', '.') }}
                                    </div>
                                </div>

                                <div class="mt-4 text-lg font-semibold tracking-tight text-zinc-900 group-hover:underline dark:text-white">
                                    {{ $product->title }}
                                </div>
                                <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ $product->short_description ?: 'Lihat detail produk untuk informasi lengkap.' }}
                                </div>
                                <div class="mt-5">
                                    <x-ui.button variant="secondary" size="sm" :href="route('catalog.products.show', $product->slug)">
                                        Lihat Detail
                                    </x-ui.button>
                                </div>
                            </div>
                        </x-ui.card>
                    </a>
                @endforeach
            @else
                @foreach ([['Ebook','Strategi Growth 30 Hari'], ['Ecourse','Fundamental Digital Commerce'], ['Event','Kelas Live: Bisnis Digital'] ] as [$type, $name])
                    <x-ui.card class="p-6">
                        <div class="flex items-center justify-between gap-3">
                            <x-ui.badge variant="info">{{ $type }}</x-ui.badge>
                            <div class="text-sm font-semibold text-zinc-900 dark:text-white">Rp 0</div>
                        </div>
                        <div class="mt-4 text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $name }}</div>
                        <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Deskripsi singkat akan muncul di sini.</div>
                        <div class="mt-5">
                            <x-ui.button variant="secondary" size="sm" :href="route('catalog.products.index')">
                                Lihat Katalog
                            </x-ui.button>
                        </div>
                    </x-ui.card>
                @endforeach
            @endif
        </div>
    </section>

    <section id="event" class="mx-auto max-w-[var(--container-5xl)] px-4 pb-14">
        <x-ui.section-header
            eyebrow="Event"
            title="Event premium (placeholder)"
            description="Nanti kamu bisa daftar event dan melihat jadwal dari dashboard."
        />
        <x-ui.empty-state
            class="mt-8"
            title="Belum ada event yang ditampilkan"
            description="Event premium akan muncul di sini setelah modul event tersedia."
            action-label="Daftar / Masuk"
            :action-href="route('login')"
        />
    </section>

    <section id="membership" class="mx-auto max-w-[var(--container-5xl)] px-4 pb-14">
        <x-ui.section-header
            eyebrow="Membership"
            title="Membership (placeholder)"
            description="Nantinya membership memberikan akses premium selama periode tertentu."
        />
        <x-ui.empty-state
            class="mt-8"
            title="Belum ada membership"
            description="Membership akan tersedia setelah modulnya siap."
            action-label="Mulai Sekarang"
            :action-href="route('register')"
        />
    </section>

    <section id="epi-channel" class="mx-auto max-w-[var(--container-5xl)] px-4 pb-14">
        <x-ui.card class="p-8 md:p-10">
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                <div class="min-w-0">
                    <x-ui.badge variant="warning">Segera tersedia</x-ui.badge>
                    <div class="mt-4 text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                        EPI Channel (Affiliate Teaser)
                    </div>
                    <div class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-300">
                        Nantinya kamu bisa mempromosikan program pilihan dan mendapatkan komisi secara transparan. Dibangun bertahap, tetap sederhana, dan mudah dipahami.
                    </div>
                </div>
                <div class="shrink-0">
                    <x-ui.button variant="ghost" size="md" href="#">
                        Pelajari Konsepnya
                    </x-ui.button>
                </div>
            </div>
        </x-ui.card>
    </section>

    <section id="faq" class="mx-auto max-w-[var(--container-5xl)] px-4 pb-14">
        <x-ui.section-header
            eyebrow="FAQ"
            title="Pertanyaan singkat"
            description="Jawaban singkat untuk membantu user pemula."
        />

        <div class="mt-8 grid gap-4 md:grid-cols-3">
            <x-ui.card class="p-6">
                <div class="text-sm font-semibold text-zinc-900 dark:text-white">Apakah ini platform belajar?</div>
                <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Ya. Kamu bisa akses kelas dan materi yang disediakan di EPIC Hub Premium.</div>
            </x-ui.card>
            <x-ui.card class="p-6">
                <div class="text-sm font-semibold text-zinc-900 dark:text-white">Apakah produk langsung bisa diakses setelah pembayaran?</div>
                <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Targetnya iya. Mekanisme akses akan dibuat aman dan otomatis di sprint berikutnya.</div>
            </x-ui.card>
            <x-ui.card class="p-6">
                <div class="text-sm font-semibold text-zinc-900 dark:text-white">Apakah bisa ikut EPI Channel?</div>
                <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">Bisa. Saat ini masih tahap persiapan dan akan dibuka bertahap.</div>
            </x-ui.card>
        </div>
    </section>
</x-layouts::public>
