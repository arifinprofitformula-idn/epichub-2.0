@props([
    'product',
    'userProduct' => null,
    'eventRegistration' => null,
    'progress' => null,
    'accessUrl' => null,
    'checkoutUrl' => null,
    'detailUrl' => null,
    'primaryLabel' => 'Beli Sekarang',
])

@php
    $typeValue = $product->product_type?->value ?? (string) $product->product_type;
    $typeLabel = $product->product_type?->label() ?? ucfirst(str_replace('_', ' ', $typeValue));
    $isOwned = $userProduct !== null;
    $event = $product->event;
@endphp

<x-ui.card class="overflow-hidden p-0 shadow-[0_20px_40px_rgba(15,23,42,0.06)]">
    <div class="relative aspect-[16/10] bg-slate-100">
        @if ($product->thumbnail)
            <img
                src="{{ asset('storage/'.$product->thumbnail) }}"
                alt="{{ $product->title }}"
                class="h-full w-full object-cover"
                loading="lazy"
            />
        @else
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(34,211,238,0.26),_transparent_45%),linear-gradient(135deg,#0f172a,#1e293b_45%,#0f766e)]"></div>
        @endif

        <div class="absolute inset-x-0 top-0 flex flex-wrap items-center gap-2 px-4 py-4">
            <x-ui.badge variant="info">{{ $typeLabel }}</x-ui.badge>
            @if ($product->category)
                <x-ui.badge variant="neutral">{{ $product->category->name }}</x-ui.badge>
            @endif
            @if ($isOwned)
                <x-ui.badge variant="success">Sudah Dimiliki</x-ui.badge>
            @endif
        </div>
    </div>

    <div class="p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <div class="text-lg font-semibold tracking-tight text-slate-900">{{ $product->title }}</div>
                <p class="mt-2 line-clamp-3 text-sm text-slate-500">
                    {{ $product->short_description ?: 'Produk pilihan EPIC HUB untuk kebutuhan belajar dan digital delivery kamu.' }}
                </p>
            </div>
        </div>

        <div class="mt-4 space-y-2 text-xs text-slate-500">
            @if ($typeValue === 'course' && $progress)
                <div class="flex items-center justify-between gap-4 font-semibold uppercase tracking-[0.18em] text-slate-400">
                    <span>Progress</span>
                    <span class="text-cyan-600">{{ $progress['percent'] }}%</span>
                </div>
                <div class="h-2 rounded-full bg-slate-100">
                    <div class="h-2 rounded-full bg-[linear-gradient(90deg,#06b6d4,#2563eb)]" style="width: {{ max(6, $progress['percent']) }}%"></div>
                </div>
            @endif

            @if ($typeValue === 'event' && $event)
                <div class="flex items-center justify-between gap-4">
                    <span>Tanggal</span>
                    <span class="font-semibold text-slate-900">
                        {{ $event->starts_at?->format('d M Y, H:i') ?? 'Jadwal menyusul' }}
                    </span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span>Status</span>
                    <span class="font-semibold text-slate-900">{{ $event->status?->label() ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span>Sisa seat</span>
                    <span class="font-semibold text-slate-900">{{ $event->remainingSeats() ?? 'Unlimited' }}</span>
                </div>
            @endif

            @if ($typeValue === 'bundle')
                <div class="flex items-center justify-between gap-4">
                    <span>Isi bundle</span>
                    <span class="font-semibold text-slate-900">{{ $product->bundled_products_count }} item</span>
                </div>
            @endif
        </div>

        <div class="mt-5">
            @if ($product->has_discount)
                <div class="text-xs text-slate-400 line-through">Rp {{ number_format((float) $product->price, 0, ',', '.') }}</div>
            @endif
            <div class="mt-1 text-xl font-semibold tracking-tight text-slate-900">
                Rp {{ number_format((float) $product->effective_price, 0, ',', '.') }}
            </div>
        </div>

        <div class="mt-5 flex flex-col gap-3">
            @if ($isOwned && $accessUrl)
                <x-ui.button variant="primary" size="md" :href="$accessUrl">
                    {{ $primaryLabel }}
                </x-ui.button>
            @elseif ($checkoutUrl)
                <x-ui.button variant="primary" size="md" :href="$checkoutUrl">
                    {{ $primaryLabel }}
                </x-ui.button>
            @endif

            @if ($detailUrl)
                <x-ui.button variant="ghost" size="md" :href="$detailUrl">
                    Detail Produk
                </x-ui.button>
            @endif
        </div>
    </div>
</x-ui.card>
