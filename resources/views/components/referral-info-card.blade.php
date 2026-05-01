@props([
    'channel' => null,
    'context' => 'checkout',
    'source' => 'default_system',
    'locked' => false,
])

@php
    $isRegister = $context === 'register';
    $isDefaultSystem = $source === 'default_system';
    $referrerName = $channel
        ? ($channel->user?->name ?? $channel->store_name ?? 'EPIC Hub Official')
        : 'EPIC Hub Official';
@endphp

@if ($isRegister)
    {{-- Tampilan ringkas untuk halaman register --}}
    <div {{ $attributes->class('rounded-[1.35rem] border border-emerald-200/60 bg-emerald-50/70 px-4 py-3 text-sm text-emerald-900') }}>
        <div class="flex items-center justify-center gap-2">
            <svg viewBox="0 0 24 24" class="size-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" />
            </svg>
            <span class="text-center">Anda diperkenalkan oleh <span class="font-bold text-emerald-950">{{ $referrerName }}</span></span>
        </div>
    </div>
@else
    {{-- Tampilan lengkap untuk konteks checkout / lainnya --}}
    @php
        $isDefaultSystem = $source === 'default_system';
        $title = $locked
            ? 'Akun Anda terhubung dengan pereferral:'
            : ($isDefaultSystem
                ? 'Pendaftaran/pembelian ini akan terhubung dengan pereferral sistem EPIC Hub Official.'
                : 'Pendaftaran/pembelian ini akan terhubung dengan pereferral:');
        $description = $locked
            ? 'Pereferral utama pada akun Anda sudah dikunci dan akan dipakai untuk transaksi berikutnya.'
            : 'Pereferral utama akan dikunci pada akun Anda untuk transaksi berikutnya.';
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
                            <dd class="mt-1 font-semibold text-emerald-950">{{ $referrerName }}</dd>
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

                <p class="mt-3 text-xs leading-5 text-emerald-900/70">
                    Pereferral utama akan dikunci pada akun Anda untuk transaksi berikutnya.
                </p>
            </div>
        </div>
    </div>
@endif
