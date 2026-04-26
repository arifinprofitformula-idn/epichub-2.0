@props([
    'product',
    'userProduct'       => null,
    'eventRegistration' => null,
    'progress'          => null,
    'accessUrl'         => null,
    'checkoutUrl'       => null,
    'detailUrl'         => null,
    'primaryLabel'      => 'Beli Sekarang',
])

@php
    $typeValue      = $product->product_type?->value ?? (string) $product->product_type;
    $typeLabel      = $product->product_type?->label() ?? ucfirst(str_replace('_', ' ', $typeValue));
    $isOwned        = $userProduct !== null;
    $event          = $product->event;
    $effectivePrice = (float) $product->effective_price;
    $originalPrice  = (float) $product->price;
    $hasDiscount    = $product->has_discount && $originalPrice > $effectivePrice;
    $discountPct    = ($hasDiscount && $originalPrice > 0)
        ? (int) round((($originalPrice - $effectivePrice) / $originalPrice) * 100)
        : 0;
@endphp

<article class="group flex flex-col overflow-hidden rounded-[var(--radius-2xl)] border border-slate-200/80 bg-white shadow-[0_16px_40px_rgba(15,23,42,0.06)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_24px_55px_rgba(15,23,42,0.10)]">

    {{-- Thumbnail --}}
    <div class="relative aspect-[16/9] overflow-hidden bg-slate-100">
        @if ($product->thumbnail)
            <img
                src="{{ asset('storage/'.$product->thumbnail) }}"
                alt="{{ $product->title }}"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.04]"
                loading="lazy"
            />
        @else
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(34,211,238,0.26),_transparent_45%),linear-gradient(135deg,#0f172a,#1e293b_45%,#0f766e)]">
                <div class="flex h-full items-center justify-center">
                    <svg viewBox="0 0 24 24" fill="none" class="size-11 text-white/15 transition-opacity duration-200 group-hover:text-white/25" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="5" width="18" height="14" rx="2.5" stroke="currentColor" stroke-width="1.4"/>
                        <circle cx="8.5" cy="10" r="1.5" fill="currentColor"/>
                        <path d="M3 15L8 10L11 13L15 9L21 15" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        @endif

        {{-- Top badges --}}
        <div class="absolute inset-x-0 top-0 flex flex-wrap items-center gap-1.5 p-3">
            <x-ui.badge variant="info">{{ $typeLabel }}</x-ui.badge>
            @if ($product->category)
                <x-ui.badge variant="neutral">{{ $product->category->name }}</x-ui.badge>
            @endif
            @if ($isOwned)
                <x-ui.badge variant="success">Dimiliki</x-ui.badge>
            @endif
        </div>

        {{-- Discount badge --}}
        @if ($hasDiscount && $discountPct > 0)
            <div class="absolute right-3 top-3">
                <div class="rounded-full bg-rose-500 px-2.5 py-1 text-[11px] font-bold text-white shadow-[0_4px_12px_rgba(239,68,68,0.35)]">
                    -{{ $discountPct }}%
                </div>
            </div>
        @endif

        {{-- Featured ribbon --}}
        @if ($product->is_featured)
            <div class="absolute bottom-3 right-3">
                <div class="flex items-center gap-1 rounded-full bg-amber-500/90 px-2.5 py-1 text-[11px] font-bold text-white backdrop-blur-sm">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="size-3" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6L12 2z"/>
                    </svg>
                    Unggulan
                </div>
            </div>
        @endif
    </div>

    {{-- Body --}}
    <div class="flex flex-1 flex-col p-5">

        {{-- Title --}}
        <h3 class="font-semibold leading-snug text-slate-900">{{ $product->title }}</h3>

        {{-- Type-specific metadata chips --}}
        @if ($typeValue === 'event' && $event)
            <div class="mt-3 flex flex-wrap gap-2">
                @if ($event->starts_at)
                    <div class="flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600">
                        <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M7 3.75v2.5M17 3.75v2.5M5.75 7.75h12.5M6.75 5.75h10.5c1.105 0 2 .895 2 2v10.5c0 1.105-.895 2-2 2H6.75c-1.105 0-2-.895-2-2V7.75c0-1.105.895-2 2-2Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                        </svg>
                        {{ $event->starts_at->translatedFormat('d M Y') }}
                    </div>
                @endif
                @php($remaining = $event->remainingSeats())
                @if ($remaining !== null)
                    <div class="flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold {{ $remaining < 10 ? 'border-rose-200 bg-rose-50 text-rose-600' : 'border-slate-200 bg-slate-50 text-slate-600' }}">
                        <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M12 12.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5ZM4.75 20.25c1.6-3.1 4.3-4.5 7.25-4.5s5.65 1.4 7.25 4.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                        </svg>
                        {{ $remaining }} seat
                    </div>
                @endif
                @if ($event->status)
                    <div class="flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600">
                        {{ $event->status->label() }}
                    </div>
                @endif
            </div>
        @endif

        @if ($typeValue === 'bundle')
            <div class="mt-3">
                <div class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600">
                    <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 4.75L19.25 8.75V15.25L12 19.25L4.75 15.25V8.75L12 4.75Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                        <path d="M12 12L19.25 8.75M12 12v7.25M12 12L4.75 8.75" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                    </svg>
                    {{ $product->bundled_products_count }} item
                </div>
            </div>
        @endif

        {{-- Progress bar (course owned) --}}
        @if ($typeValue === 'course' && $progress)
            <div class="mt-3">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Progress</span>
                    <span class="text-[11px] font-semibold text-cyan-600">{{ $progress['percent'] }}%</span>
                </div>
                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-[linear-gradient(90deg,#06b6d4,#2563eb)] transition-[width] duration-700" style="width: {{ max(4, $progress['percent']) }}%"></div>
                </div>
            </div>
        @endif

        {{-- Push price + buttons to bottom --}}
        <div class="mt-auto">
            {{-- Price --}}
            <div class="mt-4 flex items-end justify-between gap-2">
                <div>
                    @if ($hasDiscount)
                        <div class="text-xs text-slate-400 line-through">Rp {{ number_format($originalPrice, 0, ',', '.') }}</div>
                    @endif
                    <div class="text-xl font-semibold tracking-tight text-slate-900">
                        Rp {{ number_format($effectivePrice, 0, ',', '.') }}
                    </div>
                </div>
                @if ($hasDiscount && $discountPct > 0)
                    <div class="shrink-0 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-600 ring-1 ring-rose-100">
                        Hemat {{ $discountPct }}%
                    </div>
                @endif
            </div>

            {{-- Action buttons --}}
            <div class="mt-4 flex flex-col gap-2">
                @if ($isOwned && $accessUrl)
                    <a
                        href="{{ $accessUrl }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-[1rem] bg-[linear-gradient(135deg,#2563eb,#1d4ed8)] px-4 py-2.5 text-sm font-semibold text-white shadow-[0_10px_24px_rgba(37,99,235,0.20)] transition-all duration-150 hover:-translate-y-0.5 hover:brightness-105 hover:shadow-[0_16px_32px_rgba(37,99,235,0.28)] active:scale-[0.97] active:translate-y-0"
                    >
                        @if ($typeValue === 'course')
                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M7 4.75L19.25 12L7 19.25V4.75Z" fill="currentColor" fill-opacity=".2" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                            </svg>
                        @elseif ($typeValue === 'event')
                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M7 3.75v2.5M17 3.75v2.5M5.75 7.75h12.5M6.75 5.75h10.5c1.105 0 2 .895 2 2v10.5c0 1.105-.895 2-2 2H6.75c-1.105 0-2-.895-2-2V7.75c0-1.105.895-2 2-2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        @elseif ($typeValue === 'ebook')
                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M4.75 6.25c0-.828.672-1.5 1.5-1.5h11.5c.828 0 1.5.672 1.5 1.5v12c0 .828-.672 1.5-1.5 1.5H6.25c-.828 0-1.5-.672-1.5-1.5V6.25Z" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M8 8.75h8M8 11.75h8M8 14.75h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                            </svg>
                        @elseif ($typeValue === 'digital_file')
                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M12 4.75v9.5M8.75 11.25L12 14.25L15.25 11.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M4.75 17.25v1a1.25 1.25 0 0 0 1.25 1.25h12a1.25 1.25 0 0 0 1.25-1.25v-1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        @else
                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M5 12L10 17L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        @endif
                        {{ $primaryLabel }}
                    </a>
                @elseif ($checkoutUrl)
                    <a
                        href="{{ $checkoutUrl }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-[1rem] bg-[linear-gradient(135deg,#f59e0b,#f97316)] px-4 py-2.5 text-sm font-semibold text-slate-950 shadow-[0_10px_24px_rgba(245,158,11,0.20)] transition-all duration-150 hover:-translate-y-0.5 hover:brightness-95 hover:shadow-[0_16px_32px_rgba(245,158,11,0.28)] active:scale-[0.97] active:translate-y-0"
                    >
                        @if ($typeValue === 'event')
                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M7 3.75v2.5M17 3.75v2.5M5.75 7.75h12.5M6.75 5.75h10.5c1.105 0 2 .895 2 2v10.5c0 1.105-.895 2-2 2H6.75c-1.105 0-2-.895-2-2V7.75c0-1.105.895-2 2-2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            </svg>
                        @else
                            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M5.5 7.5H18.5L17 17.5H7L5.5 7.5Z" fill="currentColor" fill-opacity=".18" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                <path d="M5.5 7.5L4.5 4.5H2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="9.5" cy="19.75" r="1.25" fill="currentColor"/>
                                <circle cx="15.5" cy="19.75" r="1.25" fill="currentColor"/>
                            </svg>
                        @endif
                        {{ $primaryLabel }}
                    </a>
                @endif

                @if ($detailUrl)
                    <a
                        href="{{ $detailUrl }}"
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
    </div>
</article>
