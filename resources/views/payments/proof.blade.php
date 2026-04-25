<x-layouts::app :title="'Bukti Pembayaran '.$payment->payment_number">
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
                    <div class="text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</div>
                    <div class="mt-0.5 text-xs font-medium text-slate-500">
                        {{ auth()->user()->hasVerifiedEmail() ? 'Pengguna terverifikasi' : 'Menunggu verifikasi' }}
                    </div>
                </div>

                <a
                    href="{{ route('profile.edit') }}"
                    class="group inline-flex size-12 items-center justify-center rounded-full bg-[linear-gradient(135deg,#0f172a,#1d4ed8)] text-sm font-semibold text-white shadow-[0_12px_25px_rgba(37,99,235,0.18)] transition hover:brightness-110"
                    aria-label="Buka profil pengguna"
                >
                    <span class="group-hover:scale-105 transition">{{ auth()->user()->initials() }}</span>
                </a>
            </div>
        </section>

        <section class="rounded-[1.75rem] border border-slate-200/80 bg-[linear-gradient(180deg,#f8fbff_0%,#f4f7fb_100%)] px-4 py-5 shadow-[0_18px_45px_rgba(148,163,184,0.10)] md:rounded-[2rem] md:px-8 md:py-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <a href="{{ route('payments.show', $payment) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-slate-900">
                    <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 6L9 12L15 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Kembali ke Pembayaran
                </a>

                <a href="{{ $proofUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-[0_12px_24px_rgba(37,99,235,0.22)] transition hover:bg-blue-700">
                    Buka File Asli
                </a>
            </div>

            <div class="mt-6 overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-white shadow-[0_20px_40px_rgba(148,163,184,0.10)]">
                <div class="border-b border-slate-200/80 px-5 py-4 md:px-8">
                    <div class="text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-slate-400">Preview Bukti Pembayaran</div>
                    <div class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">{{ $payment->payment_number }}</div>
                    <div class="mt-1 text-sm text-slate-500">Order {{ $payment->order->order_number }}</div>
                </div>

                <div class="p-4 md:p-6">
                    @if ($proofKind === 'image')
                        <div class="overflow-hidden rounded-[1.5rem] border border-slate-200/80 bg-slate-50 p-2">
                            <img src="{{ $proofUrl }}" alt="Bukti pembayaran {{ $payment->payment_number }}" class="h-auto w-full rounded-[1rem] object-contain">
                        </div>
                    @elseif ($proofKind === 'pdf')
                        <div class="overflow-hidden rounded-[1.5rem] border border-slate-200/80 bg-slate-50">
                            <iframe src="{{ $proofUrl }}" class="h-[75vh] w-full" title="Preview bukti pembayaran {{ $payment->payment_number }}"></iframe>
                        </div>
                    @else
                        <div class="rounded-[1.4rem] border border-slate-200/80 bg-slate-50/80 p-5 text-sm text-slate-500">
                            File bukti pembayaran tidak bisa dipreview langsung. Gunakan tombol <span class="font-semibold text-slate-900">Buka File Asli</span> untuk melihatnya.
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
</x-layouts::app>
