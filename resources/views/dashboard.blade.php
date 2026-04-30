<x-layouts::app :title="__('Dashboard')">
    <div class="mx-auto flex min-h-[calc(100vh-1rem)] w-full max-w-[min(1520px,calc(100vw-40px))] flex-col px-0 pb-0 pt-0 md:min-h-screen md:pb-0">

        {{-- Desktop top bar --}}
        <header class="sticky top-0 z-20 mb-[10px] hidden flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 bg-white/95 px-1 py-4 backdrop-blur md:-mx-6 md:-mt-8 md:flex md:px-0 lg:-mx-8">
            <div class="flex items-center gap-3 md:pl-6 lg:pl-8">
                <flux:sidebar.toggle
                    class="hidden size-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition-all duration-150 hover:border-cyan-300 hover:text-cyan-700 active:scale-[0.95] lg:inline-flex"
                    icon="bars-2"
                    inset="left"
                />
            </div>
            <div class="flex items-center gap-3 md:pr-6 lg:pr-8">
                <div class="text-right">
                    <div class="text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</div>
                    <div class="mt-0.5 text-xs text-slate-500">
                        {{ auth()->user()->hasVerifiedEmail() ? 'Terverifikasi ✓' : 'Menunggu verifikasi' }}
                    </div>
                </div>
                <a
                    href="{{ route('profile.edit') }}"
                    class="inline-flex size-11 items-center justify-center rounded-full bg-[linear-gradient(135deg,#0f172a,#1d4ed8)] text-sm font-semibold text-white shadow-[0_10px_24px_rgba(37,99,235,0.20)] transition-all duration-150 hover:scale-105 hover:brightness-110 active:scale-95"
                    aria-label="Profil"
                >
                    {{ auth()->user()->initials() }}
                </a>
            </div>
        </header>

        {{-- Welcome hero --}}
        <div class="pt-5">
            <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-[linear-gradient(135deg,rgba(255,255,255,0.98),rgba(236,254,255,0.88)_48%,rgba(255,248,225,0.84))] shadow-[0_20px_55px_rgba(15,23,42,0.08)]">
                <div class="grid gap-5 p-6 md:grid-cols-[minmax(0,1fr)_auto] md:items-center md:gap-8 md:p-8">
                    <div>
                        <x-ui.badge variant="info">Dashboard</x-ui.badge>
                        <h1 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900 md:text-4xl">
                            Halo, {{ auth()->user()->name }}!
                        </h1>

                        {{-- Quick stat pills --}}
                        <div class="mt-4 flex flex-wrap gap-2">
                            <div class="flex items-center gap-1.5 rounded-full border border-slate-200 bg-white/80 px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm">
                                <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-cyan-500" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M4.75 6.75c0-.552.448-1 1-1h12.5c.552 0 1 .448 1 1v10.5c0 .552-.448 1-1 1H5.75c-.552 0-1-.448-1-1V6.75Z" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M8 9.5h8M8 12h8M8 14.5h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                                {{ $activeCoursesCount }} Kelas
                            </div>
                            <div class="flex items-center gap-1.5 rounded-full border border-slate-200 bg-white/80 px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm">
                                <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-violet-500" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M4.75 7.75h14.5M6.75 7.75V6.5c0-1.519 1.231-2.75 2.75-2.75h5c1.519 0 2.75 1.231 2.75 2.75v1.25M7.25 7.75l.9 12h7.7l.9-12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                                {{ $activeUserProductsCount }} Produk
                            </div>
                            <div class="flex items-center gap-1.5 rounded-full border border-slate-200 bg-white/80 px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm">
                                <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-amber-500" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M7 3.75v2.5M17 3.75v2.5M5.75 7.75h12.5M6.75 5.75h10.5c1.105 0 2 .895 2 2v10.5c0 1.105-.895 2-2 2H6.75c-1.105 0-2-.895-2-2V7.75c0-1.105.895-2 2-2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                                {{ $activeEventsCount }} Event
                            </div>
                        </div>

                        {{-- CTA --}}
                        <div class="mt-5 flex flex-wrap gap-3">
                            <a
                                href="{{ route('my-products.index') }}"
                                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm transition-all duration-150 hover:-translate-y-0.5 hover:border-cyan-300 hover:text-cyan-700 hover:shadow-[0_8px_20px_rgba(6,182,212,0.14)] active:scale-[0.97] active:translate-y-0"
                            >
                                <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M4.75 7.75h14.5M6.75 7.75V6.5c0-1.519 1.231-2.75 2.75-2.75h5c1.519 0 2.75 1.231 2.75 2.75v1.25M7.25 7.75l.9 12h7.7l.9-12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                                Produk Saya
                            </a>
                            <a
                                href="{{ route('marketplace.index') }}"
                                class="inline-flex items-center gap-2 rounded-full bg-amber-500 px-4 py-2 text-sm font-semibold text-slate-950 shadow-[0_10px_25px_rgba(245,158,11,0.25)] transition-all duration-150 hover:-translate-y-0.5 hover:brightness-95 hover:shadow-[0_14px_32px_rgba(245,158,11,0.32)] active:scale-[0.97] active:translate-y-0"
                            >
                                <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <rect x="3.75" y="4.75" width="16.5" height="14.5" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M3.75 9.25h16.5" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M8.75 4.75v4.5M15.25 4.75v4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                                Marketplace
                            </a>
                        </div>
                    </div>

                    {{-- Kursus aktif mini card --}}
                    <div class="hidden md:block">
                        <div class="rounded-[1.5rem] border border-cyan-100 bg-cyan-50/80 px-6 py-5 text-center shadow-[0_10px_30px_rgba(34,211,238,0.12)]">
                            <svg viewBox="0 0 24 24" fill="none" class="mx-auto size-7 text-cyan-600" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M4.75 6.75c0-.552.448-1 1-1h12.5c.552 0 1 .448 1 1v10.5c0 .552-.448 1-1 1H5.75c-.552 0-1-.448-1-1V6.75Z" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8 9.5h8M8 12h8M8 14.5h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            <div class="mt-2 text-4xl font-semibold tracking-tight text-slate-900">{{ $activeCoursesCount }}</div>
                            <div class="mt-1 text-[11px] font-semibold uppercase tracking-[0.20em] text-cyan-700">Kursus Aktif</div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <x-pwa-install-button class="mt-4" />

        {{-- Quick links --}}
        <section class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
            <a
                href="#event"
                class="group flex items-center gap-3 rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-[var(--shadow-soft)] transition-all duration-150 hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-[0_12px_32px_rgba(245,158,11,0.10)] active:scale-[0.98] active:translate-y-0"
            >
                <div class="flex size-10 shrink-0 items-center justify-center rounded-[var(--radius-lg)] bg-amber-50 text-amber-600 transition-colors duration-150 group-hover:bg-amber-100">
                    <svg viewBox="0 0 24 24" fill="none" class="size-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M7 3.75v2.5M17 3.75v2.5M5.75 7.75h12.5M6.75 5.75h10.5c1.105 0 2 .895 2 2v10.5c0 1.105-.895 2-2 2H6.75c-1.105 0-2-.895-2-2V7.75c0-1.105.895-2 2-2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="text-lg font-semibold tracking-tight text-slate-900">{{ $activeEventsCount }}</div>
                    <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Event</div>
                </div>
            </a>

            <a
                href="{{ route('my-products.index') }}"
                class="group flex items-center gap-3 rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-[var(--shadow-soft)] transition-all duration-150 hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-[0_12px_32px_rgba(139,92,246,0.10)] active:scale-[0.98] active:translate-y-0"
            >
                <div class="flex size-10 shrink-0 items-center justify-center rounded-[var(--radius-lg)] bg-violet-50 text-violet-600 transition-colors duration-150 group-hover:bg-violet-100">
                    <svg viewBox="0 0 24 24" fill="none" class="size-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M4.75 7.75h14.5M6.75 7.75V6.5c0-1.519 1.231-2.75 2.75-2.75h5c1.519 0 2.75 1.231 2.75 2.75v1.25M7.25 7.75l.9 12h7.7l.9-12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="text-lg font-semibold tracking-tight text-slate-900">{{ $activeUserProductsCount }}</div>
                    <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Produk Aktif</div>
                </div>
            </a>

            <a
                href="{{ route('epi-channel.dashboard') }}"
                class="group flex items-center gap-3 rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-[var(--shadow-soft)] transition-all duration-150 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-[0_12px_32px_rgba(16,185,129,0.10)] active:scale-[0.98] active:translate-y-0"
            >
                <div class="flex size-10 shrink-0 items-center justify-center rounded-[var(--radius-lg)] bg-emerald-50 text-emerald-600 transition-colors duration-150 group-hover:bg-emerald-100">
                    <svg viewBox="0 0 24 24" fill="none" class="size-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 3.75L4.75 7.25V12C4.75 16.1023 7.59367 19.9093 12 20.75C16.4063 19.9093 19.25 16.1023 19.25 12V7.25L12 3.75Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <path d="M9.5 11.75L11.25 13.5L14.75 10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold text-slate-900">{{ $epiChannelStatus }}</div>
                    <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">EPI Channel</div>
                </div>
            </a>

            <a
                href="{{ route('marketplace.index') }}"
                class="group flex items-center gap-3 rounded-[1.35rem] border border-slate-200 bg-white p-4 shadow-[var(--shadow-soft)] transition-all duration-150 hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-[0_12px_32px_rgba(6,182,212,0.10)] active:scale-[0.98] active:translate-y-0"
            >
                <div class="flex size-10 shrink-0 items-center justify-center rounded-[var(--radius-lg)] bg-cyan-50 text-cyan-600 transition-colors duration-150 group-hover:bg-cyan-100">
                    <svg viewBox="0 0 24 24" fill="none" class="size-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <rect x="3.75" y="4.75" width="16.5" height="14.5" rx="2" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M3.75 9.25h16.5" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M8.75 4.75v4.5M15.25 4.75v4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="text-sm font-semibold text-slate-900">Jelajahi</div>
                    <div class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Marketplace</div>
                </div>
            </a>
        </section>

        {{-- Kursus + Katalog --}}
        <section class="mt-8 grid gap-8 xl:grid-cols-[minmax(0,1.45fr)_minmax(300px,1fr)]">

            {{-- Kursus Saya --}}
            <div id="kursus-saya">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2.5">
                        <div class="flex size-8 items-center justify-center rounded-[var(--radius-lg)] bg-cyan-100 text-cyan-700">
                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M4.75 6.75c0-.552.448-1 1-1h12.5c.552 0 1 .448 1 1v10.5c0 .552-.448 1-1 1H5.75c-.552 0-1-.448-1-1V6.75Z" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8 9.5h8M8 12h8M8 14.5h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <span class="text-lg font-semibold tracking-tight text-slate-900">Kursus Saya</span>
                    </div>
                    <a
                        href="{{ route('my-courses.index') }}"
                        class="inline-flex items-center gap-1 rounded-[var(--radius-md)] px-3 py-1.5 text-sm font-semibold text-slate-500 transition-all duration-150 hover:bg-slate-100 hover:text-slate-900 active:scale-[0.97]"
                    >
                        Semua
                        <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M9.25 5.75L15.25 12L9.25 18.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>

                @if ($activeCourseUserProducts->isEmpty())
                    <div class="mt-5 rounded-[1.75rem] border border-dashed border-slate-200 bg-slate-50/80 p-8 text-center">
                        <div class="mx-auto flex size-14 items-center justify-center rounded-[var(--radius-2xl)] bg-slate-100 text-slate-400">
                            <svg viewBox="0 0 24 24" fill="none" class="size-7" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4.75 6.75c0-.552.448-1 1-1h12.5c.552 0 1 .448 1 1v10.5c0 .552-.448 1-1 1H5.75c-.552 0-1-.448-1-1V6.75Z" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8 9.5h8M8 12h8M8 14.5h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="mt-4 text-sm font-semibold text-slate-900">Belum ada kelas aktif</div>
                        <div class="mt-1 text-sm text-slate-500">Beli produk LMS untuk mulai belajar.</div>
                        <div class="mt-5">
                            <a
                                href="{{ route('catalog.products.index', ['type' => 'course']) }}"
                                class="inline-flex items-center gap-2 rounded-full bg-[linear-gradient(135deg,#2563eb,#1d4ed8)] px-5 py-2.5 text-sm font-semibold text-white shadow-[0_10px_25px_rgba(37,99,235,0.22)] transition-all duration-150 hover:-translate-y-0.5 hover:brightness-105 hover:shadow-[0_16px_35px_rgba(37,99,235,0.30)] active:scale-[0.97] active:translate-y-0"
                            >
                                Lihat Katalog LMS
                            </a>
                        </div>
                    </div>
                @else
                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        @foreach ($activeCourseUserProducts as $userProduct)
                            @php($product = $userProduct->product)
                            @php($course = $product?->course)
                            @php($progress = $progressByUserProductId[$userProduct->id] ?? ['percent' => 0, 'completed' => 0, 'total' => 0])
                            @php($isStarted = $progress['percent'] > 0)
                            @php($isDone = $progress['percent'] >= 100)

                            <article class="group overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-[0_16px_40px_rgba(15,23,42,0.06)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_24px_55px_rgba(15,23,42,0.10)]">
                                <div class="relative aspect-[16/9] bg-slate-100">
                                    @if (filled($course?->thumbnail ?? $product?->thumbnail))
                                        <img
                                            src="{{ asset('storage/'.($course?->thumbnail ?? $product?->thumbnail)) }}"
                                            alt="{{ $course?->title ?? $product?->title }}"
                                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]"
                                            loading="lazy"
                                        />
                                    @else
                                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(34,211,238,0.28),_transparent_45%),linear-gradient(135deg,#0f172a,#1e293b_45%,#0f766e)]"></div>
                                    @endif

                                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-transparent px-5 pb-5 pt-14 text-white">
                                        <div class="flex items-center justify-between gap-2">
                                            <x-ui.badge variant="success">
                                                {{ $userProduct->expires_at ? $userProduct->expires_at->translatedFormat('d M Y') : 'Lifetime' }}
                                            </x-ui.badge>
                                            <div class="rounded-full bg-white/15 px-2.5 py-0.5 text-xs font-semibold backdrop-blur-sm">
                                                {{ $progress['completed'] }}/{{ $progress['total'] }}
                                            </div>
                                        </div>
                                        <div class="mt-2.5 text-base font-semibold leading-snug">
                                            {{ $course?->title ?? $product?->title ?? 'Kelas Premium' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="p-5">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Progress</span>
                                        <span class="text-[11px] font-semibold {{ $isDone ? 'text-emerald-600' : 'text-cyan-600' }}">{{ $progress['percent'] }}%</span>
                                    </div>
                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                        <div
                                            class="h-full rounded-full bg-[linear-gradient(90deg,#06b6d4,#f59e0b)] transition-[width] duration-700"
                                            style="width: {{ max(4, $progress['percent']) }}%"
                                        ></div>
                                    </div>

                                    <a
                                        href="{{ route('my-courses.show', $userProduct) }}"
                                        class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-[1rem] bg-[linear-gradient(135deg,#2563eb,#1d4ed8)] px-5 py-2.5 text-sm font-semibold text-white shadow-[0_12px_28px_rgba(37,99,235,0.20)] transition-all duration-150 hover:-translate-y-0.5 hover:brightness-105 hover:shadow-[0_18px_38px_rgba(37,99,235,0.30)] active:scale-[0.97] active:translate-y-0"
                                    >
                                        @if ($isDone)
                                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M5 12L10 17L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Pelajari Kembali
                                        @else
                                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M7 4.75L19.25 12L7 19.25V4.75Z" fill="currentColor" fill-opacity=".2" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                            </svg>
                                            {{ $isStarted ? 'Lanjutkan' : 'Mulai Belajar' }}
                                        @endif
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Katalog --}}
            <div id="katalog-lms">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2.5">
                        <div class="flex size-8 items-center justify-center rounded-[var(--radius-lg)] bg-amber-100 text-amber-700">
                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <rect x="3.75" y="4.75" width="16.5" height="14.5" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M3.75 9.25h16.5" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8.75 4.75v4.5M15.25 4.75v4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <span class="text-lg font-semibold tracking-tight text-slate-900">Katalog</span>
                    </div>
                    <a
                        href="{{ route('catalog.products.index', ['type' => 'course']) }}"
                        class="inline-flex items-center gap-1 rounded-[var(--radius-md)] px-3 py-1.5 text-sm font-semibold text-slate-500 transition-all duration-150 hover:bg-slate-100 hover:text-slate-900 active:scale-[0.97]"
                    >
                        Semua
                        <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M9.25 5.75L15.25 12L9.25 18.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($catalogCourses as $catalogItem)
                        @php($product = $catalogItem['product'])
                        @php($course = $product->course)

                        <article class="group overflow-hidden rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-[0_8px_24px_rgba(15,23,42,0.05)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_16px_40px_rgba(15,23,42,0.09)]">
                            <div class="flex gap-3.5">
                                <div class="relative h-[72px] w-[72px] shrink-0 overflow-hidden rounded-[1rem] bg-slate-100">
                                    @if (filled($course?->thumbnail ?? $product->thumbnail))
                                        <img
                                            src="{{ asset('storage/'.($course?->thumbnail ?? $product->thumbnail)) }}"
                                            alt="{{ $course?->title ?? $product->title }}"
                                            class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.06]"
                                            loading="lazy"
                                        />
                                    @else
                                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(245,158,11,0.3),_transparent_45%),linear-gradient(135deg,#0f172a,#164e63)]"></div>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <x-ui.badge variant="warning">Belum Beli</x-ui.badge>
                                        @if ($product->has_discount)
                                            <x-ui.badge variant="danger">Promo</x-ui.badge>
                                        @endif
                                    </div>
                                    <div class="mt-1.5 truncate text-sm font-semibold text-slate-900">
                                        {{ $course?->title ?? $product->title }}
                                    </div>

                                    <div class="mt-2 flex items-end justify-between gap-2">
                                        <div>
                                            @if ($product->has_discount)
                                                <div class="text-[11px] text-slate-400 line-through">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</div>
                                            @endif
                                            <div class="text-sm font-semibold text-slate-900">Rp {{ number_format((float) $product->effective_price, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="shrink-0 text-[11px] text-slate-400">{{ $product->category?->name ?? 'LMS' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3.5">
                                <a
                                    href="{{ route('checkout.show', $product->slug) }}"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-[1rem] bg-[linear-gradient(135deg,#f59e0b,#f97316)] px-4 py-2.5 text-sm font-semibold text-slate-950 shadow-[0_10px_24px_rgba(245,158,11,0.20)] transition-all duration-150 hover:-translate-y-0.5 hover:brightness-95 hover:shadow-[0_16px_32px_rgba(245,158,11,0.28)] active:scale-[0.97] active:translate-y-0"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M5.5 7.5H18.5L17 17.5H7L5.5 7.5Z" fill="currentColor" fill-opacity=".18" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                        <path d="M5.5 7.5L4.5 4.5H2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="9.5" cy="19.75" r="1.25" fill="currentColor"/>
                                        <circle cx="15.5" cy="19.75" r="1.25" fill="currentColor"/>
                                    </svg>
                                    Beli Sekarang
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[1.5rem] border border-dashed border-slate-200 bg-slate-50/80 p-8 text-center">
                            <div class="text-sm font-semibold text-slate-900">Semua katalog course sudah kamu miliki</div>
                            <div class="mt-1 text-sm text-slate-500">Produk baru yang belum dimiliki akan tampil di sini.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="mt-auto flex flex-col gap-3 border-t border-slate-200/80 px-1 pt-6 pb-0 text-sm text-slate-500 md:flex-row md:items-center md:justify-between md:pb-0">
            <div>© 2026 EPIC HUB</div>
            <div class="flex items-center gap-3">
                <flux:modal.trigger name="dashboard-terms-of-service">
                    <button type="button" class="transition-all duration-150 hover:text-slate-900 active:scale-[0.97]">
                        Terms of Service
                    </button>
                </flux:modal.trigger>
                <span class="text-slate-300">|</span>
                <flux:modal.trigger name="dashboard-privacy-policy">
                    <button type="button" class="transition-all duration-150 hover:text-slate-900 active:scale-[0.97]">
                        Privacy
                    </button>
                </flux:modal.trigger>
            </div>
        </footer>
    </div>

    <flux:modal name="dashboard-terms-of-service" class="max-w-3xl">
        <div class="space-y-5">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">Terms of Service</h2>
                <p class="mt-2 text-sm text-slate-500">Template syarat dan ketentuan penggunaan platform EPIC HUB.</p>
            </div>
            <div class="space-y-4 text-sm leading-relaxed text-slate-600">
                <p>Dengan menggunakan EPIC HUB, pengguna setuju untuk memakai platform ini secara sah, wajar, dan tidak melanggar hukum maupun hak pihak lain.</p>
                <p>Akses terhadap produk digital, kelas, event, dan fitur lain diberikan sesuai jenis pembelian, entitlement, atau persetujuan admin yang berlaku pada akun pengguna.</p>
                <p>Pengguna dilarang mendistribusikan ulang materi, membagikan akses akun, mencoba mengganggu sistem, atau menggunakan platform untuk aktivitas yang merugikan EPIC HUB maupun pengguna lain.</p>
                <p>EPIC HUB berhak memperbarui fitur, kebijakan, harga, maupun ketentuan layanan dari waktu ke waktu untuk menjaga kualitas layanan dan keamanan sistem.</p>
                <p>Apabila ditemukan penyalahgunaan, EPIC HUB dapat membatasi akses, menangguhkan akun, atau mengambil tindakan administratif lain sesuai kebijakan internal.</p>
            </div>
            <div class="flex justify-end">
                <flux:modal.close>
                    <button type="button" class="inline-flex items-center justify-center rounded-[1rem] bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition-all duration-150 hover:bg-slate-800 hover:-translate-y-0.5 active:scale-[0.97]">
                        Tutup
                    </button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="dashboard-privacy-policy" class="max-w-3xl">
        <div class="space-y-5">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">Privacy Policy</h2>
                <p class="mt-2 text-sm text-slate-500">Template kebijakan privasi untuk penggunaan EPIC HUB.</p>
            </div>
            <div class="space-y-4 text-sm leading-relaxed text-slate-600">
                <p>EPIC HUB dapat mengumpulkan data dasar pengguna seperti nama, email, aktivitas pembelian, progress belajar, dan data teknis yang diperlukan untuk menjalankan layanan.</p>
                <p>Data digunakan untuk autentikasi, pemberian akses produk, pengalaman belajar yang lebih baik, dukungan pengguna, analitik operasional, dan kebutuhan administratif platform.</p>
                <p>Data pengguna tidak dibagikan secara sembarangan kepada pihak lain di luar kebutuhan layanan, kepatuhan hukum, atau integrasi sistem yang memang diperlukan untuk operasional.</p>
                <p>EPIC HUB berupaya menjaga keamanan data dengan kontrol akses, validasi sistem, dan praktik pengelolaan data yang wajar sesuai kebutuhan aplikasi.</p>
                <p>Pengguna dapat menghubungi admin atau pengelola platform untuk permintaan pembaruan data, pertanyaan privasi, atau klarifikasi terkait kebijakan ini.</p>
            </div>
            <div class="flex justify-end">
                <flux:modal.close>
                    <button type="button" class="inline-flex items-center justify-center rounded-[1rem] bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition-all duration-150 hover:bg-slate-800 hover:-translate-y-0.5 active:scale-[0.97]">
                        Tutup
                    </button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    <x-ui.mobile-bottom-nav />
</x-layouts::app>
