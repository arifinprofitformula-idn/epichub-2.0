@component('layouts::app', ['title' => __('Marketplace')])
    <div class="mx-auto flex min-h-[calc(100vh-1rem)] w-full max-w-[min(1520px,calc(100vw-40px))] flex-col px-0 pb-0 pt-0 md:min-h-screen md:pb-0">
        @include('partials.user-dashboard-header')

        <section class="px-1 py-8 md:px-6 lg:px-8">

            {{-- Page header --}}
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-slate-400">EPIC HUB</div>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 md:text-3xl">Marketplace</h1>
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

            {{-- Filter bar --}}
            <div class="mt-5">
                <form method="GET" action="{{ route('marketplace.index') }}">
                    {{-- product_type driven by chip links, preserved here --}}
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
                                placeholder="Cari produk..."
                                class="w-full rounded-[1rem] border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 shadow-sm outline-none transition-colors duration-150 focus:border-cyan-300"
                            />
                        </div>

                        {{-- Category --}}
                        <select
                            name="category"
                            onchange="this.form.submit()"
                            class="rounded-[1rem] border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition-colors duration-150 hover:border-slate-300 focus:border-cyan-300"
                        >
                            <option value="">Semua Kategori</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->slug }}" @selected($activeFilters['category'] === $cat->slug)>{{ $cat->name }}</option>
                            @endforeach
                        </select>

                        {{-- Ownership --}}
                        <select
                            name="ownership"
                            onchange="this.form.submit()"
                            class="rounded-[1rem] border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition-colors duration-150 hover:border-slate-300 focus:border-cyan-300"
                        >
                            <option value="all" @selected($activeFilters['ownership'] === 'all')>Semua</option>
                            <option value="owned" @selected($activeFilters['ownership'] === 'owned')>Dimiliki</option>
                            <option value="not_owned" @selected($activeFilters['ownership'] === 'not_owned')>Belum Beli</option>
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

                            @if (filled($activeFilters['q']) || filled($activeFilters['category']) || filled($activeFilters['product_type']) || $activeFilters['ownership'] !== 'all')
                                <a
                                    href="{{ route('marketplace.index') }}"
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

            {{-- Type filter chips (scrollable) --}}
            <div class="-mx-1 mt-4 overflow-x-auto px-1 pb-1">
                <div class="flex min-w-max items-center gap-2">
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

                    @foreach ($chipTypes as $chipValue => $chip)
                        @php
                            $chipActive = ($chipValue === '' && $activeFilters['product_type'] === '') || $activeFilters['product_type'] === $chipValue;
                            $chipParams = array_filter([
                                'q'            => $activeFilters['q'] ?: null,
                                'category'     => $activeFilters['category'] ?: null,
                                'product_type' => $chipValue !== '' ? $chipValue : null,
                                'ownership'    => $activeFilters['ownership'] !== 'all' ? $activeFilters['ownership'] : null,
                            ]);
                        @endphp
                        <a
                            href="{{ route('marketplace.index', $chipParams) }}"
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3.5 py-1.5 text-xs font-semibold transition-all duration-150 active:scale-[0.96] {{ $chipActive ? 'border-slate-900 bg-slate-900 text-white shadow-[0_4px_14px_rgba(15,23,42,0.18)]' : 'border-slate-200 bg-white text-slate-600 shadow-sm hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">{!! $chip['icon'] !!}</svg>
                            {{ $chip['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Active filter summary --}}
            @if (filled($activeFilters['q']) || filled($activeFilters['category']) || filled($activeFilters['product_type']) || $activeFilters['ownership'] !== 'all')
                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                    <span class="font-semibold text-slate-400 uppercase tracking-[0.14em]">Filter aktif:</span>
                    @if (filled($activeFilters['q']))
                        <span class="rounded-full border border-cyan-200 bg-cyan-50 px-2.5 py-1 font-semibold text-cyan-700">"{{ $activeFilters['q'] }}"</span>
                    @endif
                    @if (filled($activeFilters['product_type']))
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 font-semibold text-slate-700">{{ $chipTypes[$activeFilters['product_type']]['label'] ?? $activeFilters['product_type'] }}</span>
                    @endif
                    @if ($activeFilters['ownership'] !== 'all')
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 font-semibold text-slate-700">{{ $activeFilters['ownership'] === 'owned' ? 'Dimiliki' : 'Belum Beli' }}</span>
                    @endif
                    <span class="text-slate-400">— {{ $products->total() }} produk</span>
                </div>
            @endif

            {{-- Product grid --}}
            @if ($products->count() === 0)
                <div class="mt-8">
                    <x-ui.empty-state
                        title="Tidak ada produk yang sesuai"
                        description="Coba ubah kata kunci atau filter untuk melihat produk lain."
                        action-label="Reset Filter"
                        :action-href="route('marketplace.index')"
                    />
                </div>
            @else
                <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($products as $product)
                        @php
                            $userProduct       = $ownedUserProducts->get($product->id);
                            $eventRegistration = $eventRegistrationsByProductId->get($product->id);
                            $progress          = $userProduct ? ($progressByUserProductId[$userProduct->id] ?? null) : null;
                            $detailUrl         = route('catalog.products.show', $product->slug);
                            $checkoutUrl       = route('checkout.show', $product->slug);
                            $accessUrl         = null;
                            $primaryLabel      = 'Beli Sekarang';

                            if ($userProduct) {
                                $tv = $product->product_type?->value ?? (string) $product->product_type;
                                switch ($tv) {
                                    case 'course':
                                        $accessUrl    = route('my-courses.show', $userProduct);
                                        $primaryLabel = 'Masuk Kelas';
                                        break;
                                    case 'event':
                                        $accessUrl    = $eventRegistration
                                            ? route('my-events.show', $eventRegistration)
                                            : route('my-events.index');
                                        $primaryLabel = 'Lihat Event';
                                        break;
                                    case 'ebook':
                                        $accessUrl    = route('my-products.show', $userProduct);
                                        $primaryLabel = 'Baca Ebook';
                                        break;
                                    case 'digital_file':
                                        $accessUrl    = route('my-products.show', $userProduct);
                                        $primaryLabel = 'Unduh File';
                                        break;
                                    case 'bundle':
                                        $accessUrl    = route('my-products.show', $userProduct);
                                        $primaryLabel = 'Akses Bundle';
                                        break;
                                    case 'membership':
                                        $accessUrl    = route('my-products.show', $userProduct);
                                        $primaryLabel = 'Lihat Akses';
                                        break;
                                    default:
                                        $accessUrl    = route('my-products.show', $userProduct);
                                        $primaryLabel = 'Akses';
                                }
                            } else {
                                $primaryLabel = match ($product->product_type?->value ?? (string) $product->product_type) {
                                    'event'      => 'Beli Tiket',
                                    'bundle'     => 'Ambil Bundle',
                                    'membership' => 'Beli Membership',
                                    default      => 'Beli Sekarang',
                                };
                            }
                        @endphp

                        <x-marketplace.product-card
                            :product="$product"
                            :user-product="$userProduct"
                            :event-registration="$eventRegistration"
                            :progress="$progress"
                            :access-url="$accessUrl"
                            :checkout-url="$checkoutUrl"
                            :detail-url="$detailUrl"
                            :primary-label="$primaryLabel"
                        />
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            @endif
        </section>

        @include('partials.user-dashboard-footer')
    </div>
@endcomponent
