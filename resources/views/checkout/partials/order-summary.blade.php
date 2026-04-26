@php
    $bankName      = $bankName      ?? data_get(config('epichub.payments.manual_bank_transfer'), 'bank_name');
    $accountNumber = $accountNumber ?? data_get(config('epichub.payments.manual_bank_transfer'), 'account_number');
    $accountName   = $accountName   ?? data_get(config('epichub.payments.manual_bank_transfer'), 'account_name');
    $isOnSale      = $isOnSale      ?? ((float)($product->sale_price ?? 0) > 0 && (float)$product->sale_price < (float)$product->price);
    $originalPrice = $originalPrice ?? (float) $product->price;
    $effectivePrice= $effectivePrice?? (float) $product->effective_price;
    $thumbnailSrc  = $thumbnailSrc  ?? (filled($product->thumbnail) ? asset('storage/'.$product->thumbnail) : null);
    $discountPercent = $discountPercent ?? ($isOnSale && $originalPrice > 0 ? round((($originalPrice - $effectivePrice) / $originalPrice) * 100) : 0);
@endphp

<div class="overflow-hidden rounded-2xl border border-zinc-200/70 bg-white shadow-[0_4px_20px_rgba(0,0,0,0.06)]">

    {{-- Product thumbnail --}}
    @if ($thumbnailSrc)
        <div class="relative h-36 overflow-hidden bg-zinc-900 sm:h-40">
            <img src="{{ $thumbnailSrc }}" alt="{{ $product->title }}" class="h-full w-full object-cover opacity-90">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
            @if ($isOnSale && $discountPercent > 0)
                <div class="absolute right-3 top-3">
                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-500 px-2.5 py-1 text-[11px] font-black text-white shadow-lg">
                        <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11.25a.75.75 0 0 0-1.5 0v2.5h-2.5a.75.75 0 0 0 0 1.5h2.5v2.5a.75.75 0 0 0 1.5 0v-2.5h2.5a.75.75 0 0 0 0-1.5h-2.5v-2.5Z" clip-rule="evenodd"/></svg>
                        Hemat {{ $discountPercent }}%
                    </span>
                </div>
            @endif
        </div>
    @endif

    <div class="p-5">
        {{-- Header --}}
        <div class="flex items-center gap-2.5">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 shadow-[0_3px_8px_rgba(52,211,153,0.3)]">
                <svg class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M1 1.75A.75.75 0 0 1 1.75 1h1.628a1.75 1.75 0 0 1 1.734 1.51L5.18 3a65.25 65.25 0 0 1 13.36 1.412.75.75 0 0 1 .58.875 48.645 48.645 0 0 1-1.618 6.2.75.75 0 0 1-.712.513H6a2.503 2.503 0 0 0-2.292 1.5H17.25a.75.75 0 0 1 0 1.5H2.76a.75.75 0 0 1-.748-.807 4.002 4.002 0 0 1 2.716-3.486L3.626 2.716a.25.25 0 0 0-.248-.216H1.75A.75.75 0 0 1 1 1.75Z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="text-sm font-bold text-zinc-900">Ringkasan Pesanan</div>
        </div>

        {{-- Product info --}}
        <div class="mt-4 rounded-xl border border-zinc-100 bg-zinc-50 p-4">
            <div class="flex items-start gap-3">
                @if (! $thumbnailSrc)
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-zinc-700 to-zinc-800">
                        <svg class="h-5 w-5 text-white/50" viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 16.82A7.462 7.462 0 0 1 10 17c-.386 0-.766-.02-1.138-.06l-.136-.021a7.5 7.5 0 1 1 2.55-.079l-.276.04Z"/></svg>
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <div class="font-bold text-zinc-900 leading-snug">{{ $product->title }}</div>
                    <div class="mt-1 flex items-center gap-1.5 text-[11px] text-zinc-500">
                        <svg class="h-3 w-3 text-zinc-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/></svg>
                        {{ $product->product_type?->label() ?? $product->product_type }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Pricing --}}
        <div class="mt-4 space-y-2">
            @if ($isOnSale)
                <div class="flex items-center justify-between gap-3 text-sm">
                    <span class="text-zinc-500">Harga normal</span>
                    <span class="text-zinc-400 line-through">Rp {{ number_format($originalPrice, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between gap-3 text-sm">
                    <span class="flex items-center gap-1.5 text-rose-600 font-semibold">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm-.75-4.75a.75.75 0 0 0 1.5 0V8.66l1.95 2.1a.75.75 0 1 0 1.1-1.02l-3.25-3.5a.75.75 0 0 0-1.1 0L6.2 9.74a.75.75 0 1 0 1.1 1.02l1.95-2.1v4.59Z" clip-rule="evenodd"/></svg>
                        Diskon {{ $discountPercent }}%
                    </span>
                    <span class="font-semibold text-rose-600">- Rp {{ number_format($originalPrice - $effectivePrice, 0, ',', '.') }}</span>
                </div>
                <div class="my-2 border-t border-zinc-100"></div>
            @endif

            <div class="flex items-end justify-between gap-3">
                <span class="text-sm font-semibold text-zinc-600">Total Pembayaran</span>
                <div class="text-right">
                    <div class="text-2xl font-black tracking-tight text-zinc-950">
                        Rp {{ number_format($effectivePrice, 0, ',', '.') }}
                    </div>
                    @if ($effectivePrice == 0)
                        <div class="text-xs font-semibold text-emerald-600">GRATIS</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Submit button --}}
        @if ($isEligible)
            <button
                type="submit"
                form="checkout-form"
                class="btn-3d-checkout mt-5 inline-flex w-full items-center justify-center gap-2.5 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 py-3.5 text-base font-black text-white"
            >
                @if ($effectivePrice == 0)
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                    Ambil Gratis Sekarang
                @else
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M1 1.75A.75.75 0 0 1 1.75 1h1.628a1.75 1.75 0 0 1 1.734 1.51L5.18 3a65.25 65.25 0 0 1 13.36 1.412.75.75 0 0 1 .58.875 48.645 48.645 0 0 1-1.618 6.2.75.75 0 0 1-.712.513H6a2.503 2.503 0 0 0-2.292 1.5H17.25a.75.75 0 0 1 0 1.5H2.76a.75.75 0 0 1-.748-.807 4.002 4.002 0 0 1 2.716-3.486L3.626 2.716a.25.25 0 0 0-.248-.216H1.75A.75.75 0 0 1 1 1.75Z" clip-rule="evenodd"/></svg>
                    Lanjutkan Pembayaran
                @endif
            </button>
        @else
            <div class="mt-5 inline-flex w-full cursor-not-allowed items-center justify-center gap-2 rounded-2xl border border-zinc-200 bg-zinc-100 py-3.5 text-sm font-bold text-zinc-400">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>
                Checkout Tidak Tersedia
            </div>
        @endif

        {{-- Trust signals --}}
        <div class="mt-4 flex flex-wrap items-center justify-center gap-3 text-[11px] text-zinc-400">
            <span class="flex items-center gap-1">
                <svg class="h-3.5 w-3.5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd"/></svg>
                Data aman & terenkripsi
            </span>
            <span class="flex items-center gap-1">
                <svg class="h-3.5 w-3.5 text-sky-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9.661 2.237a.531.531 0 0 1 .678 0 11.947 11.947 0 0 0 7.078 2.749.5.5 0 0 1 .479.425c.069.52.104 1.05.104 1.589 0 5.162-3.26 9.563-7.834 11.256a.48.48 0 0 1-.332 0C5.26 16.564 2 12.163 2 7c0-.538.035-1.069.104-1.589a.5.5 0 0 1 .48-.425 11.947 11.947 0 0 0 7.077-2.749Z" clip-rule="evenodd"/></svg>
                Pembayaran terverifikasi admin
            </span>
        </div>

        {{-- Referral info --}}
        <x-referral-info-card
            :channel="data_get($referralInfo ?? [], 'channel')"
            :source="data_get($referralInfo ?? [], 'source', 'default_system')"
            :locked="(bool) data_get($referralInfo ?? [], 'is_locked', false)"
            class="mt-4"
        />
    </div>
</div>
