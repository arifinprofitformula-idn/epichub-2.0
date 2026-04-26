<x-layouts::app :title="__('Kunjungan EPI Channel')">
    @include('epi-channel.partials.page-shell-start')

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <div class="flex size-8 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 shadow-sm">
                        <svg viewBox="0 0 24 24" fill="none" class="size-4 text-white" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M3.75 12H12M12 12L8.5 8.5M12 12L8.5 15.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 4.75C16.0041 4.75 19.25 7.99594 19.25 12C19.25 16.0041 16.0041 19.25 12 19.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-widest text-sky-600 dark:text-sky-400">EPI Channel</span>
                </div>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Kunjungan</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Referral visit yang tercatat untuk link promosi milik channel kamu.</p>
            </div>
            <a href="{{ route('epi-channel.dashboard') }}"
               class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-600 shadow-sm transition-all duration-200 hover:border-zinc-300 hover:bg-zinc-50 hover:shadow active:scale-[0.98] dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
                <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M9.25 19.25L4.75 12L9.25 4.75" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M4.75 12H19.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                </svg>
                Dashboard
            </a>
        </div>

        {{-- Filter Card --}}
        <div class="mt-6">
            <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-center gap-3 border-b border-zinc-100 px-5 py-3.5 dark:border-zinc-800">
                    <div class="flex size-7 items-center justify-center rounded-lg bg-sky-100 dark:bg-sky-900/40">
                        <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-sky-600 dark:text-sky-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M4.75 6.75H19.25L14.25 12.25V18.25L9.75 16.25V12.25L4.75 6.75Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">Filter Kunjungan</span>
                </div>
                <form method="GET" class="grid gap-4 p-5 md:grid-cols-4">
                    <div>
                        <label for="product_id" class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M4.75 5.75A1 1 0 0 1 5.75 4.75h12.5a1 1 0 0 1 1 1v12.5a1 1 0 0 1-1 1H5.75a1 1 0 0 1-1-1V5.75Z" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8.5 10.5h7M8.5 13.5h4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            Produk
                        </label>
                        <select id="product_id" name="product_id" class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none transition-colors focus:border-sky-400 focus:ring-2 focus:ring-sky-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-sky-500 dark:focus:ring-sky-900/30">
                            <option value="">Semua produk</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected((string) ($filters['product_id'] ?? '') === (string) $product->id)>{{ $product->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date_from" class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <rect x="4.75" y="4.75" width="14.5" height="15.5" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8.75 4.75V3.25M15.25 4.75V3.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M4.75 9.25H19.25" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8.5 13.5h2M8.5 16.5h2M13.5 13.5h2M13.5 16.5h2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            Dari tanggal
                        </label>
                        <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none transition-colors focus:border-sky-400 focus:ring-2 focus:ring-sky-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-sky-500 dark:focus:ring-sky-900/30" />
                    </div>
                    <div>
                        <label for="date_to" class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <rect x="4.75" y="4.75" width="14.5" height="15.5" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8.75 4.75V3.25M15.25 4.75V3.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M4.75 9.25H19.25" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8.5 13.5h7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            Sampai tanggal
                        </label>
                        <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="mt-2 w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none transition-colors focus:border-sky-400 focus:ring-2 focus:ring-sky-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:focus:border-sky-500 dark:focus:ring-sky-900/30" />
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:from-sky-600 hover:to-blue-700 hover:shadow active:scale-[0.97]">
                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M4.75 6.75H19.25L14.25 12.25V18.25L9.75 16.25V12.25L4.75 6.75Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                            </svg>
                            Filter
                        </button>
                        <a href="{{ route('epi-channel.visits') }}"
                           class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm font-medium text-zinc-500 shadow-sm transition-all duration-200 hover:bg-zinc-50 hover:text-zinc-700 active:scale-[0.97] dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700">
                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M19.25 12C19.25 16.0041 16.0041 19.25 12 19.25C7.99594 19.25 4.75 16.0041 4.75 12C4.75 7.99594 7.99594 4.75 12 4.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                <path d="M16.75 4.75L19.25 7.25L16.75 9.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M13.75 7.25H19.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Insight banner --}}
        <div class="mt-4">
            <div class="flex items-start gap-3 rounded-2xl border border-sky-100 bg-sky-50/80 px-4 py-3.5 dark:border-sky-900/40 dark:bg-sky-950/20">
                <div class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-lg bg-sky-100 dark:bg-sky-900/40">
                    <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-sky-600 dark:text-sky-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M12 11V16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        <circle cx="12" cy="8.5" r="0.75" fill="currentColor"/>
                    </svg>
                </div>
                <p class="text-xs text-sky-800/80 dark:text-sky-200/80">
                    Device ditampilkan sebagai
                    <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-700 dark:bg-sky-900/50 dark:text-sky-300">
                        <svg viewBox="0 0 24 24" fill="none" class="size-3" xmlns="http://www.w3.org/2000/svg"><rect x="5" y="3.75" width="14" height="16.5" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M10.5 17.25h3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        Mobile
                    </span>
                    atau
                    <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-700 dark:bg-sky-900/50 dark:text-sky-300">
                        <svg viewBox="0 0 24 24" fill="none" class="size-3" xmlns="http://www.w3.org/2000/svg"><rect x="3.75" y="5.75" width="16.5" height="11.5" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M8.75 19.25H15.25M12 17.25V19.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        Desktop
                    </span>.
                    Kolom <strong>Domisili</strong> hanya terisi jika data lokasi tersedia pada metadata kunjungan.
                </p>
            </div>
        </div>

        {{-- Visits Table --}}
        <div class="mt-6">
            @if ($visits->isEmpty())
                <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white p-10 dark:border-zinc-800 dark:bg-zinc-900">
                    <x-ui.empty-state
                        title="Belum ada kunjungan"
                        description="Data kunjungan akan muncul setelah link referral kamu mulai dikunjungi."
                    />
                </div>
            @else
                {{-- Desktop table (lg+) --}}
                <div class="hidden lg:block">
                    <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b border-zinc-100 bg-zinc-50/80 dark:border-zinc-800 dark:bg-zinc-900/60">
                                    <th class="px-5 py-3.5 text-left">
                                        <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.4"/><path d="M12 8V12L14.5 14.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                                            Waktu
                                        </span>
                                    </th>
                                    <th class="px-5 py-3.5 text-left">
                                        <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg"><path d="M4.75 5.75A1 1 0 0 1 5.75 4.75h12.5a1 1 0 0 1 1 1v12.5a1 1 0 0 1-1 1H5.75a1 1 0 0 1-1-1V5.75Z" stroke="currentColor" stroke-width="1.5"/><path d="M8.5 10.5h7M8.5 13.5h4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                            Produk
                                        </span>
                                    </th>
                                    <th class="px-5 py-3.5 text-left">
                                        <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg"><path d="M3.75 6.75C3.75 5.64543 4.64543 4.75 5.75 4.75H18.25C19.3546 4.75 20.25 5.64543 20.25 6.75V14.25C20.25 15.3546 19.3546 16.25 18.25 16.25H5.75C4.64543 16.25 3.75 15.3546 3.75 14.25V6.75Z" stroke="currentColor" stroke-width="1.5"/><path d="M8.75 19.25H15.25M12 16.25V19.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                            Landing URL
                                        </span>
                                    </th>
                                    <th class="px-5 py-3.5 text-left">
                                        <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg"><path d="M10 13.5C10.918 14.7144 12.2986 15.4762 13.8001 15.5936C15.3017 15.7111 16.7808 15.1736 17.875 14.125L20.375 11.625C22.3747 9.55533 22.3163 6.27268 20.2466 4.27344C18.177 2.27419 14.8943 2.33253 12.8946 4.40214L11.4595 5.85072" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 10.5C13.082 9.28559 11.7014 8.52384 10.1999 8.40637C8.69836 8.28889 7.21922 8.82641 6.125 9.875L3.625 12.375C1.62534 14.4447 1.68368 17.7273 3.75329 19.7266C5.82289 21.7258 9.10554 21.6675 11.1054 19.5979L12.5317 18.1406" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            Source
                                        </span>
                                    </th>
                                    <th class="px-5 py-3.5 text-left">
                                        <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="8" r="3.25" stroke="currentColor" stroke-width="1.5"/><path d="M5 19C5 16.2386 8.13401 14 12 14C15.866 14 19 16.2386 19 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                            Visitor
                                        </span>
                                    </th>
                                    <th class="px-5 py-3.5 text-left">
                                        <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg"><path d="M12 13.75C12 13.75 7.75 10.5 7.75 7.75C7.75 5.67893 9.67893 4 12 4C14.3211 4 16.25 5.67893 16.25 7.75C16.25 10.5 12 13.75 12 13.75Z" stroke="currentColor" stroke-width="1.5"/><circle cx="12" cy="7.5" r="1.25" fill="currentColor"/><path d="M5 19.25C5.95728 17.1932 8.7477 16 12 16C15.2523 16 18.0427 17.1932 19 19.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                            Domisili
                                        </span>
                                    </th>
                                    <th class="px-5 py-3.5 text-left">
                                        <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg"><rect x="5" y="3.75" width="14" height="16.5" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M10.5 17.25h3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                            Device
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach ($visits as $visit)
                                    @php
                                        $isMobile = stripos($visit->device_label ?? '', 'mobile') !== false;
                                        $deviceIcon = $isMobile
                                            ? '<rect x="5" y="3.75" width="14" height="16.5" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M10.5 17.25h3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'
                                            : '<rect x="3.75" y="5.75" width="16.5" height="11.5" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M8.75 19.25H15.25M12 17.25V19.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>';
                                        $deviceColor = $isMobile
                                            ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300'
                                            : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300';
                                        $sourceDomain = parse_url($visit->source_url ?? '', PHP_URL_HOST) ?: null;
                                    @endphp
                                    <tr class="bg-white transition-colors duration-150 hover:bg-sky-50/40 dark:bg-zinc-950 dark:hover:bg-sky-900/10">
                                        <td class="px-5 py-4">
                                            <div class="font-semibold text-sm text-zinc-900 dark:text-white">{{ $visit->clicked_at?->format('d M Y') ?? '-' }}</div>
                                            <div class="mt-0.5 flex items-center gap-1 text-xs text-zinc-400">
                                                <svg viewBox="0 0 24 24" fill="none" class="size-3" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.4"/><path d="M12 8V12L14.5 14.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                                                {{ $visit->clicked_at?->format('H:i') ?? '-' }} WIB
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="font-semibold text-sm text-zinc-900 dark:text-white">{{ $visit->product?->title ?? '-' }}</div>
                                            <div class="mt-0.5 inline-flex items-center rounded-md bg-zinc-100 px-1.5 py-0.5 font-mono text-xs text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">{{ $visit->referral_code }}</div>
                                        </td>
                                        <td class="max-w-[200px] px-5 py-4">
                                            <div class="font-medium text-xs text-sky-700 dark:text-sky-400">{{ parse_url($visit->landing_url ?? '', PHP_URL_HOST) ?: '-' }}</div>
                                            <div class="mt-0.5 truncate text-xs text-zinc-400 dark:text-zinc-500" title="{{ $visit->landing_url }}">{{ \Illuminate\Support\Str::limit($visit->landing_url ?? '-', 50) }}</div>
                                        </td>
                                        <td class="max-w-[180px] px-5 py-4">
                                            @if ($sourceDomain)
                                                <span class="inline-flex items-center gap-1 rounded-lg bg-zinc-100 px-2 py-1 text-xs font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                                    <svg viewBox="0 0 24 24" fill="none" class="size-3 text-zinc-400" xmlns="http://www.w3.org/2000/svg"><path d="M10 13.5C10.918 14.7144 12.2986 15.4762 13.8001 15.5936C15.3017 15.7111 16.7808 15.1736 17.875 14.125L20.375 11.625C22.3747 9.55533 22.3163 6.27268 20.2466 4.27344C18.177 2.27419 14.8943 2.33253 12.8946 4.40214L11.4595 5.85072" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 10.5C13.082 9.28559 11.7014 8.52384 10.1999 8.40637C8.69836 8.28889 7.21922 8.82641 6.125 9.875L3.625 12.375C1.62534 14.4447 1.68368 17.7273 3.75329 19.7266C5.82289 21.7258 9.10554 21.6675 11.1054 19.5979L12.5317 18.1406" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    {{ $sourceDomain }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-lg bg-zinc-100 px-2 py-1 text-xs text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500">
                                                    <svg viewBox="0 0 24 24" fill="none" class="size-3" xmlns="http://www.w3.org/2000/svg"><path d="M3.75 12H12M12 12L8.5 8.5M12 12L8.5 15.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    Direct
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            @if ($visit->visitor_id)
                                                <div class="font-mono text-xs text-zinc-500 dark:text-zinc-400 truncate max-w-[100px]" title="{{ $visit->visitor_id }}">{{ \Illuminate\Support\Str::limit($visit->visitor_id, 12) }}</div>
                                            @else
                                                <span class="text-xs text-zinc-300 dark:text-zinc-600">—</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            @if ($visit->domicile_label && $visit->domicile_label !== '-')
                                                <div class="flex items-center gap-1.5">
                                                    <svg viewBox="0 0 24 24" fill="none" class="size-3.5 shrink-0 text-zinc-400" xmlns="http://www.w3.org/2000/svg"><path d="M12 13.75C12 13.75 7.75 10.5 7.75 7.75C7.75 5.67893 9.67893 4 12 4C14.3211 4 16.25 5.67893 16.25 7.75C16.25 10.5 12 13.75 12 13.75Z" stroke="currentColor" stroke-width="1.5"/><circle cx="12" cy="7.5" r="1.25" fill="currentColor"/></svg>
                                                    <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">{{ $visit->domicile_label }}</span>
                                                </div>
                                                @if ($visit->ip_address)
                                                    <div class="mt-0.5 font-mono text-xs text-zinc-400 dark:text-zinc-500">{{ $visit->ip_address }}</div>
                                                @endif
                                            @else
                                                <span class="text-xs text-zinc-300 dark:text-zinc-600">—</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4">
                                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $deviceColor }}">
                                                <svg viewBox="0 0 24 24" fill="none" class="size-3 shrink-0" xmlns="http://www.w3.org/2000/svg">{!! $deviceIcon !!}</svg>
                                                {{ $visit->device_label }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Mobile / Tablet card list (< lg) --}}
                <div class="flex flex-col gap-3 lg:hidden">
                    @foreach ($visits as $visit)
                        @php
                            $isMobile = stripos($visit->device_label ?? '', 'mobile') !== false;
                            $deviceColor = $isMobile
                                ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300'
                                : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300';
                            $deviceIcon = $isMobile
                                ? '<rect x="5" y="3.75" width="14" height="16.5" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M10.5 17.25h3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>'
                                : '<rect x="3.75" y="5.75" width="16.5" height="11.5" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M8.75 19.25H15.25M12 17.25V19.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>';
                            $sourceDomain = parse_url($visit->source_url ?? '', PHP_URL_HOST) ?: null;
                        @endphp

                        <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                            <div class="h-1 w-full bg-gradient-to-r from-sky-400 to-blue-500"></div>
                            <div class="p-4">
                                {{-- Top row: product + device badge --}}
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="font-bold text-zinc-900 dark:text-white leading-snug">{{ $visit->product?->title ?? 'Kunjungan Umum' }}</div>
                                        <div class="mt-1 flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center rounded-md bg-zinc-100 px-1.5 py-0.5 font-mono text-xs text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">{{ $visit->referral_code }}</span>
                                            @if ($sourceDomain)
                                                <span class="inline-flex items-center gap-1 text-xs text-zinc-400">
                                                    <svg viewBox="0 0 24 24" fill="none" class="size-3" xmlns="http://www.w3.org/2000/svg"><path d="M10 13.5C10.918 14.7144 12.2986 15.4762 13.8001 15.5936C15.3017 15.7111 16.7808 15.1736 17.875 14.125L20.375 11.625C22.3747 9.55533 22.3163 6.27268 20.2466 4.27344C18.177 2.27419 14.8943 2.33253 12.8946 4.40214L11.4595 5.85072" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 10.5C13.082 9.28559 11.7014 8.52384 10.1999 8.40637C8.69836 8.28889 7.21922 8.82641 6.125 9.875L3.625 12.375C1.62534 14.4447 1.68368 17.7273 3.75329 19.7266C5.82289 21.7258 9.10554 21.6675 11.1054 19.5979L12.5317 18.1406" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    {{ $sourceDomain }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-xs text-zinc-400">
                                                    <svg viewBox="0 0 24 24" fill="none" class="size-3" xmlns="http://www.w3.org/2000/svg"><path d="M3.75 12H12M12 12L8.5 8.5M12 12L8.5 15.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                    Direct
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $deviceColor }}">
                                        <svg viewBox="0 0 24 24" fill="none" class="size-3 shrink-0" xmlns="http://www.w3.org/2000/svg">{!! $deviceIcon !!}</svg>
                                        {{ $visit->device_label }}
                                    </span>
                                </div>

                                {{-- Landing URL --}}
                                @if ($visit->landing_url)
                                    <div class="mt-3 flex items-start gap-2 rounded-xl bg-sky-50/80 px-3 py-2 dark:bg-sky-900/10">
                                        <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 size-3.5 shrink-0 text-sky-500" xmlns="http://www.w3.org/2000/svg"><path d="M3.75 6.75C3.75 5.64543 4.64543 4.75 5.75 4.75H18.25C19.3546 4.75 20.25 5.64543 20.25 6.75V14.25C20.25 15.3546 19.3546 16.25 18.25 16.25H5.75C4.64543 16.25 3.75 15.3546 3.75 14.25V6.75Z" stroke="currentColor" stroke-width="1.5"/><path d="M8.75 19.25H15.25M12 16.25V19.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                        <span class="break-all text-xs text-sky-700 dark:text-sky-300">{{ \Illuminate\Support\Str::limit($visit->landing_url, 80) }}</span>
                                    </div>
                                @endif

                                {{-- Footer: time + domicile --}}
                                <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                    <div class="flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                                        <svg viewBox="0 0 24 24" fill="none" class="size-3.5 shrink-0 text-zinc-400" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.4"/><path d="M12 8V12L14.5 14.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
                                        <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $visit->clicked_at?->format('d M Y') ?? '-' }}</span>
                                        <span>{{ $visit->clicked_at?->format('H:i') ?? '' }} WIB</span>
                                    </div>
                                    @if ($visit->domicile_label && $visit->domicile_label !== '-')
                                        <div class="flex items-center gap-1 text-xs text-zinc-500 dark:text-zinc-400">
                                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg"><path d="M12 13.75C12 13.75 7.75 10.5 7.75 7.75C7.75 5.67893 9.67893 4 12 4C14.3211 4 16.25 5.67893 16.25 7.75C16.25 10.5 12 13.75 12 13.75Z" stroke="currentColor" stroke-width="1.5"/><circle cx="12" cy="7.5" r="1.25" fill="currentColor"/></svg>
                                            {{ $visit->domicile_label }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-6">
            {{ $visits->links() }}
        </div>

    @include('epi-channel.partials.page-shell-end')
</x-layouts::app>
