<x-layouts::app :title="'Pembayaran '.$payment->payment_number">
    @php
        $statusClasses = match ($payment->status->value) {
            'success' => 'bg-emerald-500 text-white',
            'failed', 'expired', 'refunded' => 'bg-rose-500 text-white',
            default => 'bg-amber-400 text-slate-900',
        };
        $statusPillClasses = match ($payment->status->value) {
            'success' => 'bg-emerald-100 text-emerald-700',
            'failed', 'expired', 'refunded' => 'bg-rose-100 text-rose-700',
            default => 'bg-amber-100 text-amber-700',
        };
    @endphp
    @php
        $order = $payment->order;
        $currentUser = auth()->user();
        $backUrl = $order ? route('orders.show', $order) : route('orders.index');
    @endphp

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
                    <div class="text-sm font-semibold text-slate-900">{{ $currentUser?->name ?? 'Pengguna' }}</div>
                    <div class="mt-0.5 text-xs font-medium text-slate-500">
                        {{ $currentUser?->hasVerifiedEmail() ? 'Pengguna terverifikasi' : 'Menunggu verifikasi' }}
                    </div>
                </div>

                <a
                    href="{{ route('profile.edit') }}"
                    class="group inline-flex size-12 items-center justify-center rounded-full bg-[linear-gradient(135deg,#0f172a,#1d4ed8)] text-sm font-semibold text-white shadow-[0_12px_25px_rgba(37,99,235,0.18)] transition hover:brightness-110"
                    aria-label="Buka profil pengguna"
                >
                    <span class="group-hover:scale-105 transition">{{ $currentUser?->initials() ?? 'U' }}</span>
                </a>
            </div>
        </section>

        <section class="rounded-[1.75rem] border border-slate-200/80 bg-[linear-gradient(180deg,#f8fbff_0%,#f4f7fb_100%)] px-4 py-5 shadow-[0_18px_45px_rgba(148,163,184,0.10)] md:rounded-[2rem] md:px-8 md:py-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-slate-900">
                    <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 6L9 12L15 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Kembali ke Invoice
                </a>

                <div class="inline-flex rounded-full px-3 py-1.5 text-[0.68rem] font-semibold uppercase tracking-[0.08em] {{ $statusPillClasses }}">
                    {{ $payment->status->label() }}
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white shadow-[0_20px_40px_rgba(148,163,184,0.10)]">
                <div class="px-5 py-4 text-center text-sm font-semibold uppercase tracking-[0.16em] {{ $statusClasses }} md:px-8">
                    Status Pembayaran: {{ strtoupper($payment->status->label()) }}
                </div>

                <div class="grid gap-6 px-5 py-6 md:px-8 md:py-8 lg:grid-cols-[1.25fr_0.75fr]">
                    <div>
                        <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-400">Pembayaran</div>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Instruksi Pembayaran</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                            Selesaikan transfer sesuai nominal berikut lalu unggah bukti pembayaran. Semua proses tetap ditampilkan di member area.
                        </p>

                        <div class="mt-6 grid gap-3 rounded-[1.4rem] border border-slate-200/80 bg-slate-50/80 p-4 text-sm md:p-5">
                            <div class="flex items-center justify-between gap-4">
                                <div class="text-slate-500">Order No.</div>
                                <div class="font-semibold text-slate-900">{{ $order?->order_number ?? '-' }}</div>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <div class="text-slate-500">Payment No.</div>
                                <div class="font-semibold text-slate-900">{{ $payment->payment_number }}</div>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <div class="text-slate-500">Total</div>
                                <div class="font-semibold text-slate-900">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</div>
                            </div>
                        </div>

                        <div class="mt-5 rounded-[1.4rem] border border-blue-100 bg-blue-50/70 p-4 text-sm text-blue-900">
                            Transfer sesuai total di atas ke rekening berikut, lalu upload bukti pembayaran agar pesanan bisa diverifikasi.
                        </div>

                        <div class="mt-5 rounded-[1.4rem] border border-slate-200/80 bg-white p-5">
                            <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-400">Rekening Tujuan</div>
                            <div class="mt-3 text-xl font-semibold tracking-tight text-slate-900">
                                {{ data_get(config('epichub.payments.manual_bank_transfer'), 'bank_name') }}
                            </div>
                            <div class="mt-2 space-y-1 text-sm text-slate-500">
                                <div>No. Rek: {{ data_get(config('epichub.payments.manual_bank_transfer'), 'account_number') }}</div>
                                <div>A/N: {{ data_get(config('epichub.payments.manual_bank_transfer'), 'account_name') }}</div>
                            </div>
                        </div>

                        @if ($payment->status->value === 'success')
                            <div class="mt-5 rounded-[1.4rem] border border-emerald-100 bg-emerald-50/80 p-4 text-sm text-emerald-800">
                                Pembayaran telah diverifikasi
                                @if ($payment->verifiedBy)
                                    oleh {{ $payment->verifiedBy->name }}
                                @endif
                                @if ($payment->verified_at)
                                    pada {{ $payment->verified_at->format('d M Y, H:i') }}.
                                @endif
                            </div>
                        @endif
                    </div>

                    <div>
                        <div class="rounded-[1.4rem] border border-slate-200/80 bg-slate-50/80 p-5">
                            <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-400">Bukti Pembayaran</div>

                            @if ($payment->proof_of_payment)
                                <div class="mt-3 text-sm text-slate-500">
                                    Bukti pembayaran sudah diupload dan bisa dilihat tanpa keluar dari dashboard.
                                </div>
                                <div class="mt-4">
                                    <x-ui.button variant="secondary" size="sm" :href="route('payments.proof.show', $payment)">
                                        Lihat bukti
                                    </x-ui.button>
                                </div>
                            @else
                                <div class="mt-3 text-sm text-slate-500">
                                    Upload bukti pembayaran (JPG/PNG/PDF, maks 5MB).
                                </div>
                            @endif

                            @if ($payment->status->value !== 'success')
                                <form class="mt-5 grid gap-3" method="POST" action="{{ route('payments.proof.store', $payment) }}" enctype="multipart/form-data">
                                    @csrf

                                    <div>
                                        <input
                                            type="file"
                                            name="proof"
                                            accept=".jpg,.jpeg,.png,.pdf"
                                            class="block w-full rounded-[1rem] border border-slate-200/80 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm file:mr-3 file:rounded-xl file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-900"
                                        />
                                        @error('proof')
                                            <div class="mt-2 text-xs text-rose-600">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <x-ui.button variant="primary" size="sm" type="submit">
                                        Upload bukti
                                    </x-ui.button>

                                    <div class="text-xs text-slate-400">
                                        Pastikan storage publik aktif agar preview bukti bekerja dengan baik.
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-layouts::app>
