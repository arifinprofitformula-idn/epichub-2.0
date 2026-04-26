@component('layouts::app', ['title' => 'Riwayat Pesanan'])
    <div class="mx-auto flex min-h-[calc(100vh-1rem)] w-full max-w-[min(1520px,calc(100vw-40px))] flex-col px-0 pb-0 pt-0 md:min-h-screen md:pb-0">
        @include('partials.user-dashboard-header')

        <section class="px-1 py-8 md:px-6 lg:px-8">

            {{-- Page header --}}
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <div class="text-[11px] font-semibold uppercase tracking-[0.20em] text-slate-400">INVOICE CENTER</div>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 md:text-3xl">Riwayat Pesanan</h1>
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

            @if ($orders->count() === 0)
                <div class="mt-8">
                    <x-ui.empty-state
                        title="Belum ada pesanan"
                        description="Mulai dari katalog produk untuk membuat pesanan pertama Anda."
                        action-label="Jelajahi Produk"
                        :action-href="route('marketplace.index')"
                    />
                </div>
            @else
                {{-- Summary stat cards --}}
                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-blue-50">
                            <svg viewBox="0 0 24 24" fill="none" class="size-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M8 7V6a4 4 0 1 1 8 0v1" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                <path d="M6.5 8.5h11l-.72 8.26a2 2 0 0 1-1.99 1.82H9.21a2 2 0 0 1-1.99-1.82L6.5 8.5Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[10px] font-semibold uppercase leading-tight tracking-[0.12em] text-slate-400">Total Pesanan</div>
                            <div class="mt-0.5 text-xl font-bold leading-none tracking-tight text-slate-900">{{ $invoiceSummary['total_orders'] }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-amber-50">
                            <svg viewBox="0 0 24 24" fill="none" class="size-4 text-amber-600" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.7"/>
                                <path d="M12 8v4l2.5 2.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[10px] font-semibold uppercase leading-tight tracking-[0.12em] text-slate-400">Belum Bayar</div>
                            <div class="mt-0.5 text-xl font-bold leading-none tracking-tight text-slate-900">{{ $invoiceSummary['unpaid_orders'] }}</div>
                        </div>
                    </div>
                </div>

                {{-- Orders table --}}
                <div class="mt-6 overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white shadow-[0_16px_40px_rgba(15,23,42,0.06)]">

                    {{-- Desktop table header --}}
                    <div class="hidden grid-cols-[1.5fr_2.2fr_1.3fr_0.85fr_auto] items-center gap-5 border-b border-slate-100 bg-slate-50/80 px-6 py-4 lg:grid">
                        <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.20em] text-slate-500">
                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <rect x="4.75" y="4.75" width="14.5" height="14.5" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8.75 9.75h6.5M8.75 12h6.5M8.75 14.25h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                            </svg>
                            Invoice
                        </div>
                        <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.20em] text-slate-500">
                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <rect x="3" y="5" width="18" height="14" rx="2.5" stroke="currentColor" stroke-width="1.4"/>
                                <circle cx="8.5" cy="10" r="1.5" fill="currentColor"/>
                                <path d="M3 15L8 10L11 13L15 9L21 15" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Item Produk
                        </div>
                        <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.20em] text-slate-500">
                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M12 4.75v14.5M8.75 8.25c0-1.1.895-2 2-2h2.5a2 2 0 0 1 0 4h-2.5a2 2 0 0 0 0 4h2.5a2 2 0 0 1 0 4h-2.5a2 2 0 0 1-2-2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                            Biaya
                        </div>
                        <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.20em] text-slate-500">
                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Status
                        </div>
                        <div class="text-right text-[11px] font-semibold uppercase tracking-[0.20em] text-slate-500">Aksi</div>
                    </div>

                    {{-- Rows --}}
                    <div class="divide-y divide-slate-100 p-3 md:p-0">
                        @foreach ($orders as $order)
                            @php
                                $firstItem     = $order->items->first();
                                $latestPayment = $order->latestPayment();
                                $statusValue   = $order->status->value;
                                $statusLabel   = $order->status->label();

                                [$statusPill, $statusDot, $statusBorder] = match ($statusValue) {
                                    'paid'                   => ['bg-emerald-100 text-emerald-700', 'bg-emerald-500', 'border-l-emerald-400'],
                                    'cancelled', 'failed'    => ['bg-rose-100 text-rose-700',       'bg-rose-500',    'border-l-rose-400'],
                                    'refunded'               => ['bg-violet-100 text-violet-700',   'bg-violet-500',  'border-l-violet-400'],
                                    default                  => ['bg-amber-100 text-amber-700',     'bg-amber-500',   'border-l-amber-400'],
                                };

                                $thumbnailSrc = filled($firstItem?->product?->thumbnail)
                                    ? asset('storage/' . $firstItem->product->thumbnail)
                                    : null;
                            @endphp

                            {{-- Mobile card --}}
                            <div class="group overflow-hidden rounded-[1.25rem] border border-slate-200/70 bg-white shadow-[0_4px_16px_rgba(15,23,42,0.04)] transition-all duration-150 hover:shadow-[0_8px_24px_rgba(15,23,42,0.09)] border-l-4 {{ $statusBorder }} lg:hidden">
                                <div class="p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            {{-- Thumbnail --}}
                                            <div class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100">
                                                @if ($thumbnailSrc)
                                                    <img src="{{ $thumbnailSrc }}" alt="{{ $firstItem->product_title }}" class="h-full w-full object-cover"/>
                                                @else
                                                    <svg viewBox="0 0 24 24" fill="none" class="size-5 text-slate-300" xmlns="http://www.w3.org/2000/svg">
                                                        <rect x="3" y="5" width="18" height="14" rx="2.5" stroke="currentColor" stroke-width="1.4"/>
                                                        <circle cx="8.5" cy="10" r="1.5" fill="currentColor"/>
                                                        <path d="M3 15L8 10L11 13L15 9L21 15" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold leading-snug text-slate-900">{{ $firstItem?->product_title ?? 'Produk digital' }}</p>
                                                @if ($order->items->count() > 1)
                                                    <p class="mt-0.5 text-[11px] font-medium text-slate-400">+ {{ $order->items->count() - 1 }} item lain</p>
                                                @endif
                                            </div>
                                        </div>

                                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusPill }}">
                                            <span class="size-1.5 rounded-full {{ $statusDot }}"></span>
                                            {{ $statusLabel }}
                                        </span>
                                    </div>

                                    <div class="mt-3 flex items-center justify-between gap-3">
                                        <div>
                                            <a href="{{ route('orders.show', $order) }}" class="text-sm font-semibold text-blue-600 transition-colors hover:text-blue-700">
                                                {{ strtoupper(str_replace('ORD', 'INV', $order->order_number)) }}
                                            </a>
                                            <p class="mt-0.5 text-[11px] text-slate-400">{{ $order->created_at?->translatedFormat('d M Y, H:i') }}</p>
                                        </div>

                                        <div class="text-right">
                                            <p class="text-base font-bold tracking-tight text-slate-900">{{ $order->formatted_total }}</p>
                                            <p class="mt-0.5 text-[11px] font-medium text-slate-400">{{ $latestPayment?->payment_method?->label() ?? '—' }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-3 flex items-center gap-2">
                                        <a
                                            href="{{ route('orders.show', $order) }}"
                                            class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-[0.875rem] bg-blue-50 py-2.5 text-sm font-semibold text-blue-600 transition-all duration-150 hover:bg-blue-100 hover:text-blue-700 active:scale-[0.97]"
                                        >
                                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M4.75 6.75L9.25 12L4.75 17.25" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M11.75 6.75L16.25 12L11.75 17.25" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            Lihat Invoice
                                        </a>
                                    </div>
                                </div>
                            </div>

                            {{-- Desktop row --}}
                            <div class="group hidden grid-cols-[1.5fr_2.2fr_1.3fr_0.85fr_auto] items-center gap-5 border-l-4 px-6 py-4 transition-all duration-150 hover:bg-slate-50/70 {{ $statusBorder }} lg:grid">

                                {{-- Invoice / Date --}}
                                <div>
                                    <a
                                        href="{{ route('orders.show', $order) }}"
                                        class="text-sm font-semibold tracking-tight text-blue-600 transition-colors duration-150 hover:text-blue-700"
                                    >
                                        {{ strtoupper(str_replace('ORD', 'INV', $order->order_number)) }}
                                    </a>
                                    <p class="mt-0.5 text-[11px] font-medium text-slate-400">{{ $order->created_at?->translatedFormat('d M Y, H:i') }}</p>
                                </div>

                                {{-- Item Produk --}}
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="flex h-10 w-[3.5rem] shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100">
                                        @if ($thumbnailSrc)
                                            <img
                                                src="{{ $thumbnailSrc }}"
                                                alt="{{ $firstItem->product_title }}"
                                                class="h-full w-full object-cover"
                                                loading="lazy"
                                            />
                                        @else
                                            <svg viewBox="0 0 24 24" fill="none" class="size-5 text-slate-300" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <rect x="3" y="5" width="18" height="14" rx="2.5" stroke="currentColor" stroke-width="1.4"/>
                                                <circle cx="8.5" cy="10" r="1.5" fill="currentColor"/>
                                                <path d="M3 15L8 10L11 13L15 9L21 15" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold leading-snug text-slate-900">
                                            {{ $firstItem?->product_title ?? 'Produk digital' }}
                                        </p>
                                        @if ($order->items->count() > 1)
                                            <p class="mt-0.5 text-[11px] font-medium text-slate-400">+ {{ $order->items->count() - 1 }} item lain</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Biaya --}}
                                <div>
                                    <p class="text-base font-bold tracking-tight text-slate-900">{{ $order->formatted_total }}</p>
                                    <p class="mt-0.5 text-[11px] font-medium text-slate-400">{{ $latestPayment?->payment_method?->label() ?? '—' }}</p>
                                </div>

                                {{-- Status --}}
                                <div>
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusPill }}">
                                        <span class="size-1.5 rounded-full {{ $statusDot }}"></span>
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                {{-- Aksi --}}
                                <div class="flex justify-end">
                                    <a
                                        href="{{ route('orders.show', $order) }}"
                                        class="inline-flex size-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 transition-all duration-150 hover:bg-blue-100 hover:text-blue-700 hover:shadow-[0_4px_12px_rgba(37,99,235,0.18)] active:scale-[0.94]"
                                        aria-label="Lihat invoice {{ $order->order_number }}"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
            @endif
        </section>

        @include('partials.user-dashboard-footer')
    </div>
@endcomponent
