<x-layouts::app title="Invoice">
    <div class="mx-auto flex min-h-[calc(100vh-1rem)] w-full max-w-[min(1520px,calc(100vw-40px))] flex-col px-0 pb-6 pt-0 md:min-h-screen md:pb-8">
        <section class="sticky top-0 z-20 mb-[10px] hidden flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 bg-white/95 px-1 py-5 backdrop-blur md:-mt-8 md:-mx-6 md:px-0 md:flex lg:-mx-8">
            <div class="flex items-center gap-3 md:pl-6 lg:pl-8">
                <flux:sidebar.toggle
                    class="hidden lg:inline-flex size-11 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:border-cyan-300 hover:text-cyan-700"
                    icon="bars-2"
                    inset="left"
                />
            </div>

            <div class="flex items-center gap-4 md:pr-6 lg:pr-8">
                <div class="text-right">
                    <div class="text-sm font-semibold text-slate-900">
                        {{ auth()->user()->name }}
                    </div>
                    <div class="mt-0.5 text-xs font-medium text-slate-500">
                        {{ auth()->user()->hasVerifiedEmail() ? 'Pengguna terverifikasi' : 'Menunggu verifikasi' }}
                    </div>
                </div>

                <a
                    href="{{ route('profile.edit') }}"
                    class="group inline-flex size-12 items-center justify-center rounded-full bg-[linear-gradient(135deg,#0f172a,#1d4ed8)] text-sm font-semibold text-white shadow-[0_12px_25px_rgba(37,99,235,0.18)] transition hover:brightness-110"
                    aria-label="Buka profil pengguna"
                >
                    <span class="group-hover:scale-105 transition">
                        {{ auth()->user()->initials() }}
                    </span>
                </a>
            </div>
        </section>

        <section class="rounded-[1.75rem] border border-slate-200/80 bg-[linear-gradient(180deg,#f8fbff_0%,#f4f7fb_100%)] px-4 py-5 shadow-[0_18px_45px_rgba(148,163,184,0.10)] md:rounded-[2rem] md:px-8 md:py-8">
            <div>
                <div class="text-[0.68rem] font-semibold uppercase tracking-[0.22em] text-slate-400 md:text-[0.72rem] md:tracking-[0.28em]">Invoice Center</div>
                <h1 class="mt-2 text-[2rem] font-semibold tracking-tight text-slate-900 md:text-3xl">Riwayat Pesanan</h1>
                <p class="mt-1 max-w-xl text-sm leading-6 text-slate-500 md:text-base">Pantau status pembayaran dan akses pembelian Anda.</p>
            </div>

            @if ($orders->count() === 0)
                <div class="mt-6">
                    <x-ui.empty-state
                        title="Belum ada invoice"
                        description="Mulai dari katalog produk untuk membuat pesanan pertama Anda."
                    >
                        <x-slot:action>
                            <x-ui.button variant="primary" :href="route('catalog.products.index')">
                                Jelajahi produk
                            </x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                </div>
            @else
                <div class="mt-5 grid grid-cols-2 gap-3 md:mt-6 md:gap-4 lg:grid-cols-2">
                    <div class="rounded-[1.4rem] border border-slate-200/80 bg-white px-4 py-4 shadow-[0_10px_24px_rgba(148,163,184,0.07)] md:rounded-[1.75rem] md:px-5 md:py-6">
                        <div class="flex flex-col items-center text-center md:flex-row md:items-center md:gap-4 md:text-left">
                            <div class="flex size-14 items-center justify-center rounded-[1.15rem] bg-blue-50 text-blue-600 md:size-16 md:rounded-[1.35rem]">
                                <svg viewBox="0 0 24 24" fill="none" class="size-7 md:size-8" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path d="M8 7V6a4 4 0 1 1 8 0v1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M6.5 8.5h11l-.72 8.26a2 2 0 0 1-1.99 1.82H9.21a2 2 0 0 1-1.99-1.82L6.5 8.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="mt-3 md:mt-0">
                                <div class="text-[0.64rem] font-semibold uppercase tracking-[0.14em] text-slate-400 md:text-xs md:tracking-[0.18em]">Total pesanan</div>
                                <div class="mt-1 text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">{{ $invoiceSummary['total_orders'] }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[1.4rem] border border-slate-200/80 bg-white px-4 py-4 shadow-[0_10px_24px_rgba(148,163,184,0.07)] md:rounded-[1.75rem] md:px-5 md:py-6">
                        <div class="flex flex-col items-center text-center md:flex-row md:items-center md:gap-4 md:text-left">
                            <div class="flex size-14 items-center justify-center rounded-[1.15rem] bg-amber-50 text-amber-600 md:size-16 md:rounded-[1.35rem]">
                                <svg viewBox="0 0 24 24" fill="none" class="size-7 md:size-8" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.8"/>
                                    <path d="M12 8v4l2.5 2.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <div class="mt-3 md:mt-0">
                                <div class="text-[0.64rem] font-semibold uppercase tracking-[0.14em] text-slate-400 md:text-xs md:tracking-[0.18em]">Belum bayar</div>
                                <div class="mt-1 text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">{{ $invoiceSummary['unpaid_orders'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 overflow-hidden rounded-[1.5rem] border border-slate-200/80 bg-white shadow-[0_18px_40px_rgba(148,163,184,0.08)] md:mt-6 md:rounded-[2rem]">
                    <div class="hidden grid-cols-[1.25fr_2.4fr_1.25fr_0.8fr_0.5fr] gap-6 border-b border-slate-200/80 px-8 py-5 text-xs font-semibold uppercase tracking-[0.24em] text-slate-400 lg:grid">
                        <div>Invoice / Tanggal</div>
                        <div>Item Kursus</div>
                        <div>Rincian Biaya</div>
                        <div>Status</div>
                        <div class="text-right">Aksi</div>
                    </div>

                    <div class="space-y-3 p-3 md:space-y-0 md:p-0 lg:divide-y lg:divide-slate-200/80">
                        @foreach ($orders as $order)
                            @php
                                $firstItem = $order->items->first();
                                $latestPayment = $order->latestPayment();
                                $statusClasses = match ($order->status->value) {
                                    'paid' => 'bg-emerald-100 text-emerald-700',
                                    'cancelled', 'failed' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-amber-100 text-amber-700',
                                };
                            @endphp
                            <div class="rounded-[1.25rem] border border-slate-200/70 bg-slate-50/70 p-4 shadow-[0_10px_24px_rgba(148,163,184,0.05)] md:px-6 md:py-4.5 lg:grid lg:rounded-none lg:border-0 lg:bg-transparent lg:p-5 lg:shadow-none lg:grid-cols-[1.25fr_2.4fr_1.25fr_0.8fr_0.5fr] lg:items-center lg:gap-5">
                                <div class="flex items-start justify-between gap-3 lg:block">
                                    <div>
                                        <div class="mb-1 text-[0.62rem] font-semibold uppercase tracking-[0.14em] text-slate-400 lg:hidden">Invoice / Tanggal</div>
                                        <a href="{{ route('orders.show', $order) }}" class="text-[0.95rem] font-semibold tracking-tight text-blue-600 transition hover:text-blue-700">
                                            {{ strtoupper(str_replace('ORD', 'INV', $order->order_number)) }}
                                        </a>
                                        <div class="mt-0.5 text-[0.72rem] font-medium text-slate-400">
                                            {{ $order->created_at?->translatedFormat('d M Y, H:i') }}
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-end gap-2 lg:hidden">
                                        <span class="inline-flex rounded-full px-3 py-1 text-[0.62rem] font-semibold uppercase tracking-[0.06em] {{ $statusClasses }}">
                                            {{ $order->status->label() }}
                                        </span>
                                        <a
                                            href="{{ route('orders.show', $order) }}"
                                            class="inline-flex h-8 items-center justify-center gap-1 rounded-full bg-blue-50 px-3 text-[0.72rem] font-semibold text-blue-600 transition hover:bg-blue-100 hover:text-blue-700"
                                            aria-label="Lihat detail invoice {{ $order->order_number }}"
                                        >
                                            Detail
                                            <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>

                                <div class="mt-3 flex items-center gap-3 lg:mt-0">
                                    <div class="flex h-11 w-[4.5rem] shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-100 text-[0.58rem] font-semibold uppercase tracking-[0.12em] text-slate-400">
                                        @if (filled($firstItem?->product?->thumbnail))
                                            <img
                                                src="{{ asset('storage/'.$firstItem->product->thumbnail) }}"
                                                alt="{{ $firstItem->product_title }}"
                                                class="h-full w-full object-cover"
                                            >
                                        @else
                                            <span>Item</span>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <div class="mb-1 text-[0.62rem] font-semibold uppercase tracking-[0.14em] text-slate-400 lg:hidden">Item Kursus</div>
                                        <div class="truncate text-[0.88rem] font-semibold leading-snug tracking-tight text-slate-900">
                                            {{ $firstItem?->product_title ?? 'Produk digital' }}
                                        </div>
                                        @if ($order->items->count() > 1)
                                            <div class="mt-0.5 text-[0.72rem] font-medium text-slate-400">
                                                + {{ $order->items->count() - 1 }} item tambahan
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-3 grid grid-cols-2 gap-3 rounded-xl border border-slate-200/70 bg-white px-3 py-3 lg:mt-0 lg:block lg:rounded-none lg:border-0 lg:bg-transparent lg:p-0">
                                    <div class="lg:hidden">
                                        <div class="text-[0.6rem] font-semibold uppercase tracking-[0.12em] text-slate-400">Total</div>
                                        <div class="mt-1 text-[0.95rem] font-semibold tracking-tight text-slate-900">{{ $order->formatted_total }}</div>
                                    </div>
                                    <div class="lg:hidden">
                                        <div class="text-[0.6rem] font-semibold uppercase tracking-[0.12em] text-slate-400">Metode</div>
                                        <div class="mt-1 text-[0.7rem] font-semibold uppercase tracking-[0.08em] text-slate-500">
                                            {{ $latestPayment?->payment_method?->label() ?? 'Belum ada metode bayar' }}
                                        </div>
                                    </div>

                                    <div class="hidden lg:block">
                                        <div class="mb-1 text-[0.62rem] font-semibold uppercase tracking-[0.14em] text-slate-400 lg:hidden">Rincian Biaya</div>
                                        <div class="text-[0.64rem] font-semibold uppercase tracking-[0.14em] text-blue-600">Biaya registrasi</div>
                                        <div class="mt-1 text-[1.05rem] font-semibold tracking-tight text-slate-900">{{ $order->formatted_total }}</div>
                                        <div class="mt-0.5 text-[0.64rem] font-semibold uppercase tracking-[0.1em] text-slate-400">
                                            {{ $latestPayment?->payment_method?->label() ?? 'Belum ada metode bayar' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="hidden lg:block">
                                    <div class="mb-1 text-[0.62rem] font-semibold uppercase tracking-[0.14em] text-slate-400 lg:hidden">Status</div>
                                    <span class="inline-flex rounded-full px-3 py-1 text-[0.62rem] font-semibold uppercase tracking-[0.06em] {{ $statusClasses }}">
                                        {{ $order->status->label() }}
                                    </span>
                                </div>

                                <div class="hidden lg:flex lg:flex-col lg:gap-1 lg:items-end">
                                    <div class="mb-1 text-[0.62rem] font-semibold uppercase tracking-[0.14em] text-slate-400 lg:hidden">Aksi</div>
                                    <a
                                        href="{{ route('orders.show', $order) }}"
                                        class="inline-flex size-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 transition hover:bg-blue-100 hover:text-blue-700"
                                        aria-label="Lihat detail invoice {{ $order->order_number }}"
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
    </div>
</x-layouts::app>
