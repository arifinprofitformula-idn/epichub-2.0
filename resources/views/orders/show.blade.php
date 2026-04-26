<x-layouts::app :title="'Invoice '.$order->order_number">
    @php
        $latestPayment = $order->latestPayment();
        $statusClasses = match ($order->status->value) {
            'paid' => 'bg-emerald-500 text-white',
            'cancelled', 'failed' => 'bg-rose-500 text-white',
            default => 'bg-amber-400 text-slate-900',
        };
        $statusPillClasses = match ($order->status->value) {
            'paid' => 'bg-emerald-100 text-emerald-700',
            'cancelled', 'failed' => 'bg-rose-100 text-rose-700',
            default => 'bg-amber-100 text-amber-700',
        };
        $bankConfig = config('epichub.payments.manual_bank_transfer');
    @endphp

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        @media print {
            [data-print-hide] {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            [data-print-root] {
                max-width: none !important;
                min-height: auto !important;
                padding: 0 !important;
            }

            [data-print-shell] {
                background: #fff !important;
                border: 0 !important;
                box-shadow: none !important;
                padding: 0 !important;
            }

            [data-print-card] {
                box-shadow: none !important;
                border: 1px solid #e2e8f0 !important;
            }

            [data-print-card-inner] {
                padding: 1rem !important;
            }

            [data-print-tight] {
                margin-top: 0.75rem !important;
                margin-bottom: 0.75rem !important;
            }

            [data-print-card] {
                border-radius: 0 !important;
            }

            [data-print-status] {
                padding: 0.45rem 0.75rem !important;
                font-size: 9pt !important;
            }

            [data-print-title] {
                font-size: 21pt !important;
                margin-top: 0.5rem !important;
            }

            [data-print-subtitle] {
                font-size: 11pt !important;
                margin-top: 0.25rem !important;
            }

            [data-print-meta] {
                font-size: 9pt !important;
                margin-top: 0.2rem !important;
            }

            [data-print-block] {
                padding: 0.8rem !important;
            }

            [data-print-item-row] {
                padding-top: 0.65rem !important;
                padding-bottom: 0.65rem !important;
            }

            [data-print-item-title] {
                font-size: 10.5pt !important;
            }

            [data-print-item-price] {
                font-size: 11pt !important;
            }

            [data-print-total] {
                margin-top: 0.9rem !important;
                padding-top: 0.75rem !important;
            }

            [data-print-total-value] {
                font-size: 22pt !important;
            }

            [data-print-note] {
                margin-top: 0.9rem !important;
                padding: 0.8rem 1rem !important;
                font-size: 8.5pt !important;
            }
        }
    </style>

    <div data-print-root class="mx-auto flex min-h-[calc(100vh-1rem)] w-full max-w-[min(1520px,calc(100vw-40px))] flex-col px-0 pb-6 pt-0 md:min-h-screen md:pb-8">
        <section data-print-hide class="sticky top-0 z-20 mb-[10px] hidden flex-wrap items-center justify-between gap-4 border-b border-slate-200/80 bg-white/95 px-1 py-5 backdrop-blur md:-mt-8 md:-mx-6 md:px-0 md:flex lg:-mx-8">
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

        <section data-print-shell class="rounded-[1.75rem] border border-slate-200/80 bg-[linear-gradient(180deg,#f8fbff_0%,#f4f7fb_100%)] px-4 py-5 shadow-[0_18px_45px_rgba(148,163,184,0.10)] md:rounded-[2rem] md:px-8 md:py-8">
            <div class="mx-auto w-full max-w-[940px] print:max-w-none">
                <div data-print-hide class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <a
                        href="{{ route('orders.index') }}"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-slate-900"
                    >
                        <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M15 6L9 12L15 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Kembali ke Daftar
                    </a>

                    <button
                        type="button"
                        onclick="window.print()"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-[0_12px_24px_rgba(37,99,235,0.22)] transition hover:bg-blue-700"
                    >
                        <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M7 17H6.75C5.7835 17 5 16.2165 5 15.25V7.75C5 6.7835 5.7835 6 6.75 6H14.25C15.2165 6 16 6.7835 16 7.75V8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 10.75C10 9.7835 10.7835 9 11.75 9H17.25C18.2165 9 19 9.7835 19 10.75V16.25C19 17.2165 18.2165 18 17.25 18H11.75C10.7835 18 10 17.2165 10 16.25V10.75Z" stroke="currentColor" stroke-width="1.7"/>
                            <path d="M12.75 14H16.25" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        </svg>
                        Cetak Invoice
                    </button>
                </div>

            <div data-print-card class="overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white shadow-[0_20px_40px_rgba(148,163,184,0.10)]">
                <div data-print-status class="px-5 py-4 text-center text-sm font-semibold uppercase tracking-[0.16em] {{ $statusClasses }} md:px-8">
                    Status: {{ strtoupper($order->status->label()) }}
                </div>

                <div data-print-card-inner class="px-5 py-6 md:px-8 md:py-8">
                    <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
                        <div>
                            <div class="inline-flex items-center gap-3 rounded-[1.25rem] border border-slate-200/80 bg-slate-50/80 px-4 py-3">
                                <img
                                    src="{{ asset('epic-hub-auth-logo.png') }}"
                                    alt="EPIC HUB"
                                    class="h-[60px] w-[60px] object-contain"
                                />
                                <div class="text-left">
                                    <div class="text-sm font-extrabold tracking-[0.18em] text-slate-900">EPIC HUB</div>
                                    <div class="mt-1 text-[0.5rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Connect Grow Impact</div>
                                </div>
                            </div>
                            <div data-print-title class="mt-4 text-[1.8rem] font-semibold tracking-tight text-blue-600 md:text-[2.2rem]">INVOICE</div>
                            <div data-print-subtitle class="mt-2 text-base font-semibold tracking-tight text-slate-900 md:text-lg">{{ strtoupper(str_replace('ORD', 'INV', $order->order_number)) }}</div>
                            <div data-print-meta class="mt-1.5 text-xs text-slate-400 md:text-sm">Tanggal Pesan: {{ $order->created_at?->translatedFormat('d F Y') }}</div>
                        </div>

                        <div class="text-left lg:text-right">
                            <div class="text-lg font-semibold tracking-tight text-slate-900 md:text-2xl">EPIC HUB</div>
                            <div class="mt-1 text-[0.5rem] font-semibold uppercase tracking-[0.12em] text-slate-500">Connect Grow Impact</div>
                            <div class="mt-2 space-y-1 text-[0.72rem] text-slate-500 md:text-sm">
                                <div>Email: {{ config('mail.from.address', $order->customer_email ?: auth()->user()->email) }}</div>
                                <div>Bank: {{ $bankConfig['bank_name'] ?? '-' }}</div>
                                <div>No. Rek: {{ $bankConfig['account_number'] ?? '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <div data-print-tight class="my-8 border-t border-slate-200/80"></div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div data-print-block class="rounded-[1.4rem] border border-slate-200/80 bg-slate-50/70 p-5">
                            <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-400">Ditagihkan Kepada:</div>
                            <div class="mt-2.5 text-lg font-semibold tracking-tight text-slate-900 md:text-2xl">
                                {{ $order->customer_name ?: auth()->user()->name }}
                            </div>
                            <div class="mt-1 text-sm text-slate-500 md:text-base">
                                {{ $order->customer_email ?: auth()->user()->email }}
                            </div>
                            @if ($order->customer_phone)
                                <div class="mt-1 text-xs text-slate-400 md:text-sm">
                                    {{ $order->customer_phone }}
                                </div>
                            @endif
                        </div>

                        <div data-print-block class="rounded-[1.4rem] border border-slate-200/80 bg-slate-50/70 p-5">
                            <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-400">Metode Pembayaran:</div>
                            <div class="mt-2.5 text-lg font-semibold tracking-tight text-slate-900 md:text-2xl">
                                {{ $latestPayment?->payment_method?->label() ?? 'Belum tersedia' }}
                            </div>
                            <div class="mt-2 inline-flex rounded-full px-3 py-1 text-[0.64rem] font-semibold uppercase tracking-[0.08em] {{ $statusPillClasses }}">
                                {{ $latestPayment?->status?->label() ?? $order->status->label() }}
                            </div>
                            <div class="mt-2 text-xs font-semibold italic text-emerald-600 md:text-sm">
                                Diproses pada:
                                {{ $latestPayment?->paid_at?->translatedFormat('d M Y, H:i')
                                    ?? $latestPayment?->created_at?->translatedFormat('d M Y, H:i')
                                    ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <div data-print-tight class="my-8 border-t border-slate-200/80"></div>

                    <div class="overflow-hidden rounded-[1.5rem] border border-slate-200/80">
                        <div class="grid grid-cols-[1fr_auto] gap-4 border-b border-slate-200/80 bg-slate-50/80 px-5 py-3 text-[0.62rem] font-semibold uppercase tracking-[0.14em] text-slate-400 md:px-6">
                            <div>Deskripsi Produk</div>
                            <div>Harga</div>
                        </div>

                        <div class="divide-y divide-slate-200/80">
                            @foreach ($order->items as $item)
                                <div data-print-item-row class="grid grid-cols-[1fr_auto] gap-4 px-5 py-3.5 md:px-6">
                                    <div class="min-w-0">
                                        <div data-print-item-title class="text-sm font-semibold tracking-tight text-slate-900 md:text-base">{{ $item->product_title }}</div>
                                        <div class="mt-1 text-[0.62rem] font-semibold uppercase tracking-[0.12em] text-blue-600 md:text-[0.72rem]">
                                            {{ strtoupper($item->product_type ?: 'Produk digital') }}
                                        </div>
                                        <div class="mt-1 text-xs text-slate-400 md:text-sm">Qty {{ $item->quantity }}</div>
                                    </div>

                                    <div data-print-item-price class="text-right text-sm font-semibold tracking-tight text-slate-900 md:text-lg">
                                        Rp {{ number_format((float) $item->subtotal_amount, 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div data-print-total class="mt-6 flex items-center justify-between gap-4 border-t-2 border-slate-800 pt-5 md:mt-8 md:pt-6">
                        <div class="text-[0.72rem] font-semibold uppercase tracking-[0.16em] text-slate-400">Total Pembayaran</div>
                        <div data-print-total-value class="text-3xl font-semibold tracking-tight text-slate-900 md:text-4xl">{{ $order->formatted_total }}</div>
                    </div>

                    <div data-print-note class="mt-6 rounded-[1.4rem] border border-slate-200/80 bg-slate-50/80 px-5 py-4 text-xs italic text-slate-500 md:mt-8 md:text-sm">
                        Invoice ini adalah bukti pesanan resmi. Akses produk akan aktif mengikuti status pembayaran dan validasi transaksi pada sistem.
                    </div>

                    <div data-print-hide class="mt-6 flex flex-wrap items-center gap-3">
                        @if ($latestPayment)
                            <x-ui.button variant="secondary" :href="route('payments.show', $latestPayment)">
                                Lihat Pembayaran
                            </x-ui.button>
                        @endif

                        @if ($order->status->value !== 'paid')
                            <form method="POST" action="{{ route('orders.cancel', $order) }}">
                                @csrf
                                <x-ui.button variant="danger" size="sm" type="submit">
                                    Batalkan Order
                                </x-ui.button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            </div>
        </section>
    </div>
</x-layouts::app>
