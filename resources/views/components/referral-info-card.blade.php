@props([
    'channel' => null,
    'context' => 'checkout',
])

@php
    $isRegister = $context === 'register';
    $title = $channel
        ? ($isRegister ? 'Pendaftaran ini terhubung dengan pereferral Anda.' : 'Transaksi ini terhubung dengan pereferral Anda.')
        : 'Belum ada pereferral terhubung pada transaksi ini.';
    $description = $channel
        ? 'Pastikan data pereferral ini sudah sesuai agar atribusi referral tetap akurat.'
        : 'Jika Anda mendapatkan rekomendasi dari EPI Channel, gunakan link resmi dari pereferral Anda.';
@endphp

<div {{ $attributes->class('rounded-[1.75rem] border border-emerald-200/80 bg-[linear-gradient(135deg,rgba(236,253,245,0.96),rgba(240,253,250,0.92))] p-4 text-sm text-emerald-950 shadow-[0_18px_40px_rgba(16,185,129,0.10)]') }}>
    <div class="flex items-start gap-3">
        <div class="mt-0.5 flex size-10 shrink-0 items-center justify-center rounded-2xl bg-white/80 text-emerald-600 shadow-[0_10px_24px_rgba(16,185,129,0.12)]">
            @if ($channel)
                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12.5l4.2 4.2L19 7.8" />
                </svg>
            @else
                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M4.93 19h14.14a2 2 0 001.73-3L13.73 4a2 2 0 00-3.46 0L3.2 16a2 2 0 001.73 3z" />
                </svg>
            @endif
        </div>

        <div class="min-w-0 flex-1">
            <div class="font-semibold leading-6 text-emerald-950">{{ $title }}</div>
            <p class="mt-1 text-sm leading-6 text-emerald-900/75">{{ $description }}</p>

            @if ($channel)
                <dl class="mt-3 grid gap-2 rounded-[1.35rem] border border-emerald-200/80 bg-white/70 p-3 sm:grid-cols-2">
                    <div>
                        <dt class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-emerald-700/70">Nama Pereferral</dt>
                        <dd class="mt-1 font-semibold text-emerald-950">{{ $channel->user?->name ?? 'Pereferral EPIC Hub' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-emerald-700/70">Kode EPI Channel</dt>
                        <dd class="mt-1 font-semibold text-emerald-950">{{ $channel->epic_code }}</dd>
                    </div>
                    @if (filled($channel->store_name))
                        <div class="sm:col-span-2">
                            <dt class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-emerald-700/70">Store Name</dt>
                            <dd class="mt-1 font-semibold text-emerald-950">{{ $channel->store_name }}</dd>
                        </div>
                    @endif
                </dl>
            @endif
        </div>
    </div>
</div>
