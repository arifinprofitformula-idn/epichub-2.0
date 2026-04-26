@component('layouts::app', ['title' => 'Produk Saya'])
    <div class="mx-auto flex min-h-[calc(100vh-1rem)] w-full max-w-[min(1520px,calc(100vw-40px))] flex-col px-0 pb-0 pt-0 md:min-h-screen md:pb-0">
        @include('partials.user-dashboard-header')

        <section class="px-1 py-8 md:px-6 lg:px-8">

            {{-- Page header --}}
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-slate-400">HUB AKSES</div>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 md:text-3xl">Produk Saya</h1>
                </div>
                <a
                    href="{{ route('dashboard') }}"
                    class="inline-flex items-center gap-1.5 rounded-[var(--radius-md)] px-3 py-1.5 text-sm font-semibold text-slate-500 transition-all duration-150 hover:bg-slate-100 hover:text-slate-900 active:scale-[0.97]"
                >
                    <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M5.75 12H18.25M10.25 6.75L4.75 12L10.25 17.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Dashboard
                </a>
            </div>

            {{-- Summary stat pills --}}
            <div class="-mx-1 mt-5 overflow-x-auto px-1 pb-1">
                <div class="flex min-w-max items-center gap-3">
                    <div class="flex items-center gap-2.5 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 shadow-sm">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-slate-100">
                            <svg viewBox="0 0 24 24" fill="none" class="size-4 text-slate-600" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <rect x="3.5" y="3.5" width="6.5" height="6.5" rx="1.25" stroke="currentColor" stroke-width="1.4"/>
                                <rect x="14" y="3.5" width="6.5" height="6.5" rx="1.25" stroke="currentColor" stroke-width="1.4"/>
                                <rect x="3.5" y="14" width="6.5" height="6.5" rx="1.25" stroke="currentColor" stroke-width="1.4"/>
                                <rect x="14" y="14" width="6.5" height="6.5" rx="1.25" stroke="currentColor" stroke-width="1.4"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[10px] font-semibold uppercase tracking-[0.15em] text-slate-400">Total</div>
                            <div class="text-lg font-bold leading-none tracking-tight text-slate-900">{{ $summary['total_products'] }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 shadow-sm">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-blue-50">
                            <svg viewBox="0 0 24 24" fill="none" class="size-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M13.75 4.75H7.75C6.645 4.75 5.75 5.645 5.75 6.75v10.5c0 1.105.895 2 2 2h8.5c1.105 0 2-.895 2-2V8.75L13.75 4.75Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                                <path d="M13.75 4.75v4h4" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                                <path d="M9 12.75h6M9 15.25h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[10px] font-semibold uppercase tracking-[0.15em] text-slate-400">Digital</div>
                            <div class="text-lg font-bold leading-none tracking-tight text-slate-900">{{ $summary['digital_products'] }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 shadow-sm">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-cyan-50">
                            <svg viewBox="0 0 24 24" fill="none" class="size-4 text-cyan-600" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <rect x="3.75" y="5.75" width="16.5" height="10.5" rx="1.5" stroke="currentColor" stroke-width="1.4"/>
                                <path d="M8.75 19.25h6.5M12 16.25v3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                <path d="M9.75 9.75L14.25 12L9.75 14.25V9.75Z" fill="currentColor" fill-opacity=".2" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[10px] font-semibold uppercase tracking-[0.15em] text-slate-400">Kelas</div>
                            <div class="text-lg font-bold leading-none tracking-tight text-slate-900">{{ $summary['active_courses'] }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 shadow-sm">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-amber-50">
                            <svg viewBox="0 0 24 24" fill="none" class="size-4 text-amber-600" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M7 3.75v2.5M17 3.75v2.5M5.75 7.75h12.5M6.75 5.75h10.5c1.105 0 2 .895 2 2v10.5c0 1.105-.895 2-2 2H6.75c-1.105 0-2-.895-2-2V7.75c0-1.105.895-2 2-2Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[10px] font-semibold uppercase tracking-[0.15em] text-slate-400">Event</div>
                            <div class="text-lg font-bold leading-none tracking-tight text-slate-900">{{ $summary['registered_events'] }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter bar --}}
            <div class="mt-5">
                <form method="GET" action="{{ route('my-products.index') }}">
                    <input type="hidden" name="product_type" value="{{ $activeFilters['product_type'] }}" />

                    <div class="flex flex-col gap-2.5 sm:flex-row sm:flex-wrap sm:items-center">
                        {{-- Search --}}
                        <div class="relative min-w-[160px] flex-1">
                            <svg viewBox="0 0 24 24" fill="none" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M10.75 17.25a6.5 6.5 0 1 0 0-13 6.5 6.5 0 0 0 0 13ZM19.25 19.25l-3.5-3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <input
                                id="q" name="q" type="text"
                                value="{{ $activeFilters['q'] }}"
                                placeholder="Cari produk saya..."
                                class="w-full rounded-[1rem] border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 shadow-sm outline-none transition-colors duration-150 focus:border-cyan-300"
                            />
                        </div>

                        {{-- Status --}}
                        <select
                            name="status"
                            onchange="this.form.submit()"
                            class="rounded-[1rem] border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition-colors duration-150 hover:border-slate-300 focus:border-cyan-300"
                        >
                            @foreach ($statusOptions as $option)
                                <option value="{{ $option['value'] }}" @selected($activeFilters['status'] === $option['value'])>{{ $option['label'] }}</option>
                            @endforeach
                        </select>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2">
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 rounded-[1rem] bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-150 hover:-translate-y-0.5 hover:bg-slate-800 hover:shadow-[0_8px_20px_rgba(15,23,42,0.18)] active:scale-[0.97] active:translate-y-0"
                            >
                                <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M10.75 17.25a6.5 6.5 0 1 0 0-13 6.5 6.5 0 0 0 0 13ZM19.25 19.25l-3.5-3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Cari
                            </button>

                            @if (filled($activeFilters['q']) || filled($activeFilters['product_type']) || $activeFilters['status'] !== 'active')
                                <a
                                    href="{{ route('my-products.index') }}"
                                    class="inline-flex items-center gap-1.5 rounded-[1rem] border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition-all duration-150 hover:border-slate-300 hover:bg-slate-50 active:scale-[0.97]"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M17.25 6.75L6.75 17.25M6.75 6.75L17.25 17.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                    </svg>
                                    Reset
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            {{-- Type filter chips (scrollable, with icons + counts) --}}
            @php
                $chipTypes = [
                    '' => [
                        'label' => 'Semua',
                        'icon'  => '<rect x="3.5" y="3.5" width="6.5" height="6.5" rx="1.25" stroke="currentColor" stroke-width="1.4"/><rect x="14" y="3.5" width="6.5" height="6.5" rx="1.25" stroke="currentColor" stroke-width="1.4"/><rect x="3.5" y="14" width="6.5" height="6.5" rx="1.25" stroke="currentColor" stroke-width="1.4"/><rect x="14" y="14" width="6.5" height="6.5" rx="1.25" stroke="currentColor" stroke-width="1.4"/>',
                    ],
                    'ebook' => [
                        'label' => 'Ebook',
                        'icon'  => '<path d="M4.75 6.25c0-.828.672-1.5 1.5-1.5h11.5c.828 0 1.5.672 1.5 1.5v12c0 .828-.672 1.5-1.5 1.5H6.25c-.828 0-1.5-.672-1.5-1.5V6.25Z" stroke="currentColor" stroke-width="1.4"/><path d="M8 8.75h8M8 11.75h8M8 14.75h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
                    ],
                    'digital_file' => [
                        'label' => 'File Digital',
                        'icon'  => '<path d="M13.75 4.75H7.75C6.645 4.75 5.75 5.645 5.75 6.75v10.5c0 1.105.895 2 2 2h8.5c1.105 0 2-.895 2-2V8.75L13.75 4.75Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><path d="M13.75 4.75v4h4" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><path d="M9 12.75h6M9 15.25h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
                    ],
                    'course' => [
                        'label' => 'Kursus',
                        'icon'  => '<rect x="3.75" y="5.75" width="16.5" height="10.5" rx="1.5" stroke="currentColor" stroke-width="1.4"/><path d="M8.75 19.25h6.5M12 16.25v3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/><path d="M9.75 9.75L14.25 12L9.75 14.25V9.75Z" fill="currentColor" fill-opacity=".2" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>',
                    ],
                    'event' => [
                        'label' => 'Event',
                        'icon'  => '<path d="M7 3.75v2.5M17 3.75v2.5M5.75 7.75h12.5M6.75 5.75h10.5c1.105 0 2 .895 2 2v10.5c0 1.105-.895 2-2 2H6.75c-1.105 0-2-.895-2-2V7.75c0-1.105.895-2 2-2Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
                    ],
                    'bundle' => [
                        'label' => 'Bundle',
                        'icon'  => '<path d="M12 4.75L19.25 8.75V15.25L12 19.25L4.75 15.25V8.75L12 4.75Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><path d="M12 12L19.25 8.75M12 12v7.25M12 12L4.75 8.75" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
                    ],
                    'membership' => [
                        'label' => 'Membership',
                        'icon'  => '<path d="M12 3.75L4.75 7.25V12C4.75 16.1023 7.59367 19.9093 12 20.75C16.4063 19.9093 19.25 16.1023 19.25 12V7.25L12 3.75Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><path d="M9.5 11.75L11.25 13.5L14.75 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>',
                    ],
                ];
            @endphp

            <div class="-mx-1 mt-4 overflow-x-auto px-1 pb-1">
                <div class="flex min-w-max items-center gap-2">
                    @foreach ($chipTypes as $chipValue => $chip)
                        @php
                            $chipActive = ($chipValue === '' && $activeFilters['product_type'] === '') || $activeFilters['product_type'] === $chipValue;
                            $chipCount  = $chipValue === '' ? $summary['total_products'] : ($groupedOwnedProducts[$chipValue] ?? collect())->count();
                            $chipParams = array_filter([
                                'q'            => $activeFilters['q'] ?: null,
                                'product_type' => $chipValue !== '' ? $chipValue : null,
                                'status'       => $activeFilters['status'] !== 'active' ? $activeFilters['status'] : null,
                            ]);
                        @endphp
                        <a
                            href="{{ route('my-products.index', $chipParams) }}"
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-xs font-semibold transition-all duration-150 active:scale-[0.96] {{ $chipActive ? 'border-slate-900 bg-slate-900 text-white shadow-[0_4px_14px_rgba(15,23,42,0.18)]' : 'border-slate-200 bg-white text-slate-600 shadow-sm hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">{!! $chip['icon'] !!}</svg>
                            {{ $chip['label'] }}
                            @if ($chipCount > 0)
                                <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold leading-none {{ $chipActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $chipCount }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Active filter summary --}}
            @if (filled($activeFilters['q']) || filled($activeFilters['product_type']) || $activeFilters['status'] !== 'active')
                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                    <span class="font-semibold uppercase tracking-[0.14em] text-slate-400">Filter aktif:</span>
                    @if (filled($activeFilters['q']))
                        <span class="rounded-full border border-cyan-200 bg-cyan-50 px-2.5 py-1 font-semibold text-cyan-700">"{{ $activeFilters['q'] }}"</span>
                    @endif
                    @if (filled($activeFilters['product_type']))
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 font-semibold text-slate-700">{{ $chipTypes[$activeFilters['product_type']]['label'] ?? $activeFilters['product_type'] }}</span>
                    @endif
                    @if ($activeFilters['status'] !== 'active')
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 font-semibold text-slate-700">{{ collect($statusOptions)->firstWhere('value', $activeFilters['status'])['label'] ?? $activeFilters['status'] }}</span>
                    @endif
                    <span class="text-slate-400">— {{ $userProducts->total() }} produk</span>
                </div>
            @endif

            {{-- Product grid --}}
            @if ($userProducts->count() === 0)
                <div class="mt-8">
                    <x-ui.empty-state
                        title="Belum ada produk yang dimiliki"
                        description="Produk yang Anda beli akan muncul di sini setelah pembayaran berhasil."
                        action-label="Jelajahi Marketplace"
                        :action-href="route('marketplace.index')"
                    />
                </div>
            @else
                <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($userProducts as $userProduct)
                        @php
                            $product           = $userProduct->product;
                            $course            = $product?->course;
                            $event             = $product?->event;
                            $eventRegistration = $eventRegistrationsByUserProductId->get($userProduct->id);
                            $progress          = $courseProgressByUserProductId[$userProduct->id] ?? ['total' => 0, 'completed' => 0, 'percent' => 0];
                            $typeValue         = $product?->product_type?->value ?? (string) $product?->product_type;
                            $typeLabel         = $product?->product_type?->label() ?? ucfirst(str_replace('_', ' ', $typeValue ?: 'produk'));
                            $thumbnail         = $event?->banner ?: $course?->thumbnail ?: $product?->thumbnail;
                            $thumbnailUrl      = filled($thumbnail)
                                ? (\Illuminate\Support\Str::startsWith($thumbnail, ['http://', 'https://']) ? $thumbnail : asset('storage/'.$thumbnail))
                                : null;
                            $productTitle          = $product?->title ?? 'Produk';
                            $accessStatusLabel     = $userProduct->isRevoked() ? 'Dicabut' : ($userProduct->isExpired() ? 'Kedaluwarsa' : 'Aktif');
                            $accessStatusVariant   = $userProduct->isRevoked() ? 'danger' : ($userProduct->isExpired() ? 'warning' : 'success');
                            $primaryUrl   = route('my-products.show', $userProduct);
                            $primaryLabel = 'Lihat Akses';
                            $primaryIcon  = 'check';

                            switch ($typeValue) {
                                case 'ebook':
                                    $primaryLabel = 'Baca Ebook';
                                    $primaryIcon  = 'ebook';
                                    break;
                                case 'digital_file':
                                    $primaryLabel = 'Unduh File';
                                    $primaryIcon  = 'download';
                                    break;
                                case 'bundle':
                                    $primaryLabel = 'Akses Bundle';
                                    $primaryIcon  = 'bundle';
                                    break;
                                case 'membership':
                                    $primaryLabel = 'Lihat Akses';
                                    $primaryIcon  = 'shield';
                                    break;
                                case 'course':
                                    $primaryLabel = 'Masuk Kelas';
                                    $primaryIcon  = 'play';
                                    $primaryUrl   = $course && $course->isPublished()
                                        ? route('my-courses.show', $userProduct)
                                        : route('my-products.show', $userProduct);
                                    break;
                                case 'event':
                                    $primaryLabel = 'Lihat Event';
                                    $primaryIcon  = 'calendar';
                                    $primaryUrl   = $eventRegistration
                                        ? route('my-events.show', $eventRegistration)
                                        : route('my-events.index');
                                    break;
                            }
                        @endphp

                        <article class="group flex flex-col overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white shadow-[0_16px_40px_rgba(15,23,42,0.06)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_24px_55px_rgba(15,23,42,0.10)]">

                            {{-- Thumbnail --}}
                            <div class="relative aspect-[16/9] overflow-hidden bg-slate-100">
                                @if ($thumbnailUrl)
                                    <img
                                        src="{{ $thumbnailUrl }}"
                                        alt="{{ $productTitle }}"
                                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.04]"
                                        loading="lazy"
                                    />
                                @else
                                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(34,211,238,0.22),_transparent_42%),linear-gradient(135deg,#0f172a,#1d4ed8_45%,#f59e0b)]">
                                        <div class="flex h-full items-center justify-center">
                                            <svg viewBox="0 0 24 24" fill="none" class="size-11 text-white/15 transition-opacity duration-200 group-hover:text-white/25" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <rect x="3" y="5" width="18" height="14" rx="2.5" stroke="currentColor" stroke-width="1.4"/>
                                                <circle cx="8.5" cy="10" r="1.5" fill="currentColor"/>
                                                <path d="M3 15L8 10L11 13L15 9L21 15" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                    </div>
                                @endif

                                {{-- Badges --}}
                                <div class="absolute inset-x-0 top-0 flex flex-wrap items-center gap-1.5 p-3">
                                    <x-ui.badge variant="info">{{ $typeLabel }}</x-ui.badge>
                                    <x-ui.badge variant="{{ $accessStatusVariant }}">{{ $accessStatusLabel }}</x-ui.badge>
                                </div>
                            </div>

                            {{-- Body --}}
                            <div class="flex flex-1 flex-col p-5">
                                <h3 class="font-semibold leading-snug text-slate-900">{{ $productTitle }}</h3>

                                {{-- Type-specific metadata chips --}}
                                @if ($typeValue === 'course')
                                    <div class="mt-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Progress</span>
                                            <span class="text-[11px] font-semibold text-cyan-600">{{ $progress['percent'] }}%</span>
                                        </div>
                                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-[linear-gradient(90deg,#06b6d4,#2563eb)] transition-[width] duration-700" style="width: {{ max(4, $progress['percent']) }}%"></div>
                                        </div>
                                        <p class="mt-1.5 text-[11px] text-slate-400">{{ $progress['completed'] }}/{{ $progress['total'] }} lesson selesai</p>
                                    </div>
                                @elseif ($typeValue === 'event')
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @if ($event?->starts_at)
                                            <div class="flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                                <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path d="M7 3.75v2.5M17 3.75v2.5M5.75 7.75h12.5M6.75 5.75h10.5c1.105 0 2 .895 2 2v10.5c0 1.105-.895 2-2 2H6.75c-1.105 0-2-.895-2-2V7.75c0-1.105.895-2 2-2Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                                </svg>
                                                {{ $event->starts_at->translatedFormat('d M Y') }}
                                            </div>
                                        @endif
                                        @if ($eventRegistration)
                                            <div class="flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                                {{ $eventRegistration->status?->label() ?? 'Terdaftar' }}
                                            </div>
                                        @endif
                                    </div>
                                @elseif ($typeValue === 'bundle')
                                    <div class="mt-3">
                                        <div class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M12 4.75L19.25 8.75V15.25L12 19.25L4.75 15.25V8.75L12 4.75Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                                                <path d="M12 12L19.25 8.75M12 12v7.25M12 12L4.75 8.75" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                            </svg>
                                            {{ $product?->bundled_products_count ?? 0 }} item dalam bundle
                                        </div>
                                    </div>
                                @elseif ($typeValue === 'membership')
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <div class="flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M12 3.75L4.75 7.25V12C4.75 16.1023 7.59367 19.9093 12 20.75C16.4063 19.9093 19.25 16.1023 19.25 12V7.25L12 3.75Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                                            </svg>
                                            {{ $userProduct->expires_at?->translatedFormat('d M Y') ?? 'Selamanya' }}
                                        </div>
                                        @if ($userProduct->access_type)
                                            <div class="flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold capitalize text-slate-600">
                                                {{ str_replace('_', ' ', (string) $userProduct->access_type) }}
                                            </div>
                                        @endif
                                    </div>
                                @elseif (in_array($typeValue, ['ebook', 'digital_file'], true))
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <div class="flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M13.75 4.75H7.75C6.645 4.75 5.75 5.645 5.75 6.75v10.5c0 1.105.895 2 2 2h8.5c1.105 0 2-.895 2-2V8.75L13.75 4.75Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                                                <path d="M13.75 4.75v4h4" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                                            </svg>
                                            {{ $product?->files?->count() ?? 0 }} file
                                        </div>
                                        @if ($userProduct->granted_at)
                                            <div class="flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                                {{ $userProduct->granted_at->translatedFormat('d M Y') }}
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                {{-- Action buttons --}}
                                <div class="mt-auto flex flex-col gap-2 pt-5">
                                    <a
                                        href="{{ $primaryUrl }}"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-[1rem] bg-[linear-gradient(135deg,#2563eb,#1d4ed8)] px-4 py-2.5 text-sm font-semibold text-white shadow-[0_10px_24px_rgba(37,99,235,0.20)] transition-all duration-150 hover:-translate-y-0.5 hover:brightness-105 hover:shadow-[0_16px_32px_rgba(37,99,235,0.28)] active:scale-[0.97] active:translate-y-0"
                                    >
                                        @if ($primaryIcon === 'play')
                                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M7 4.75L19.25 12L7 19.25V4.75Z" fill="currentColor" fill-opacity=".2" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                            </svg>
                                        @elseif ($primaryIcon === 'calendar')
                                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M7 3.75v2.5M17 3.75v2.5M5.75 7.75h12.5M6.75 5.75h10.5c1.105 0 2 .895 2 2v10.5c0 1.105-.895 2-2 2H6.75c-1.105 0-2-.895-2-2V7.75c0-1.105.895-2 2-2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                        @elseif ($primaryIcon === 'ebook')
                                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M4.75 6.25c0-.828.672-1.5 1.5-1.5h11.5c.828 0 1.5.672 1.5 1.5v12c0 .828-.672 1.5-1.5 1.5H6.25c-.828 0-1.5-.672-1.5-1.5V6.25Z" stroke="currentColor" stroke-width="1.5"/>
                                                <path d="M8 8.75h8M8 11.75h8M8 14.75h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                            </svg>
                                        @elseif ($primaryIcon === 'download')
                                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M12 4.75v9.5M8.75 11.25L12 14.25L15.25 11.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M4.75 17.25v1a1.25 1.25 0 0 0 1.25 1.25h12a1.25 1.25 0 0 0 1.25-1.25v-1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                        @elseif ($primaryIcon === 'bundle')
                                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M12 4.75L19.25 8.75V15.25L12 19.25L4.75 15.25V8.75L12 4.75Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                                <path d="M12 12L19.25 8.75M12 12v7.25M12 12L4.75 8.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                        @elseif ($primaryIcon === 'shield')
                                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M12 3.75L4.75 7.25V12C4.75 16.1023 7.59367 19.9093 12 20.75C16.4063 19.9093 19.25 16.1023 19.25 12V7.25L12 3.75Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                                <path d="M9.5 11.75L11.25 13.5L14.75 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        @else
                                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M5 12L10 17L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        @endif
                                        {{ $primaryLabel }}
                                    </a>

                                    @if (filled($product?->slug))
                                        <a
                                            href="{{ route('catalog.products.show', $product->slug) }}"
                                            class="inline-flex w-full items-center justify-center gap-2 rounded-[1rem] border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-150 hover:-translate-y-0.5 hover:border-slate-300 hover:bg-slate-50 hover:shadow-[0_8px_20px_rgba(15,23,42,0.08)] active:scale-[0.97] active:translate-y-0"
                                        >
                                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M12 5.75H6.75C5.64543 5.75 4.75 6.64543 4.75 7.75V17.25C4.75 18.3546 5.64543 19.25 6.75 19.25H16.25C17.3546 19.25 18.25 18.3546 18.25 17.25V12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M19.25 4.75L12.75 11.25M14.75 4.75H19.25V9.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Detail Produk
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $userProducts->links() }}
                </div>
            @endif
        </section>

        @include('partials.user-dashboard-footer')
    </div>
@endcomponent
