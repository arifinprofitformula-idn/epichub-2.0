<x-layouts::app :title="__('Produk Promosi — EPI Channel')">
    @include('epi-channel.partials.page-shell-start')
        <x-ui.section-header
            eyebrow="EPI Channel"
            title="Produk Promosi"
            description="Produk affiliate aktif dengan link referral dan estimasi komisi siap pakai."
        >
            <div class="flex items-center gap-2">
                <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.dashboard')">
                    Dashboard
                </x-ui.button>
                <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.links')">
                    Link Promosi
                </x-ui.button>
            </div>
        </x-ui.section-header>

        @if ($products->count() === 0)
            <div class="mt-8">
                <x-ui.empty-state
                    title="Belum ada produk affiliate"
                    description="Admin belum mengaktifkan affiliate untuk produk mana pun. Cek kembali nanti."
                />
            </div>
        @else
            <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($products as $product)
                    @php
                        $productLink  = route('catalog.products.show', $product->slug).'?ref='.$channel->epic_code;
                        $landingLink  = $product->landing_page_enabled
                            ? route('offer.show', $product->slug).'?ref='.$channel->epic_code
                            : null;
                        $effectivePrice    = (float) $product->effective_price;
                        $commissionValue   = (float) $product->affiliate_commission_value;
                        $isPercentage      = $product->affiliate_commission_type?->value === 'percentage';
                        $estimatedComm     = $isPercentage
                            ? ($effectivePrice * ($commissionValue / 100))
                            : $commissionValue;
                        $shareLink  = $landingLink ?? $productLink;
                        $waMessage  = 'Hai! Cek produk ini: '.$product->title."\n".$shareLink;
                        $waUrl      = 'https://wa.me/?text='.rawurlencode($waMessage);
                        $productPageId = 'prod-'.$product->id;
                    @endphp

                    <div class="group flex flex-col overflow-hidden rounded-[var(--radius-2xl)] border border-zinc-200/80 bg-white shadow-[var(--shadow-soft)] transition-all duration-200 hover:-translate-y-0.5 hover:shadow-[0_16px_40px_rgba(0,0,0,0.10)] dark:border-zinc-800 dark:bg-zinc-900 dark:hover:shadow-[0_16px_40px_rgba(0,0,0,0.35)]">

                        {{-- Thumbnail --}}
                        <div class="relative aspect-[16/9] overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                            @if ($product->thumbnail)
                                <img
                                    src="{{ asset('storage/'.$product->thumbnail) }}"
                                    alt="{{ $product->title }}"
                                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]"
                                    loading="lazy"
                                />
                            @else
                                <div class="relative h-full w-full bg-[radial-gradient(circle_at_top_left,_rgba(34,211,238,0.35),_transparent_45%),linear-gradient(135deg,#18181b,#0f172a_45%,#1d4ed8)]">
                                    <div class="flex h-full items-center justify-center">
                                        <svg viewBox="0 0 24 24" fill="none" class="size-12 text-white/20 transition-opacity duration-200 group-hover:text-white/30" xmlns="http://www.w3.org/2000/svg">
                                            <rect x="3" y="5" width="18" height="14" rx="2.5" stroke="currentColor" stroke-width="1.4"/>
                                            <circle cx="8.5" cy="10" r="1.5" fill="currentColor"/>
                                            <path d="M3 15L8 10L11 13L15 9L21 15" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                </div>
                            @endif

                            {{-- Type badge --}}
                            @if ($product->product_type)
                                <div class="absolute left-3 top-3">
                                    <span class="rounded-full bg-black/50 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur-sm">
                                        {{ $product->product_type->label() ?? $product->product_type->value }}
                                    </span>
                                </div>
                            @endif

                            {{-- Landing page badge --}}
                            @if ($landingLink)
                                <div class="absolute right-3 top-3">
                                    <span class="rounded-full bg-emerald-500/90 px-2.5 py-1 text-[11px] font-semibold text-white backdrop-blur-sm">
                                        ✦ Landing Page
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Body --}}
                        <div class="flex flex-1 flex-col p-5">

                            {{-- Title + Price --}}
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="min-w-0 font-semibold leading-snug text-zinc-900 dark:text-white">
                                    {{ $product->title }}
                                </h3>
                                <div class="shrink-0 text-right">
                                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                        Rp&nbsp;{{ number_format($effectivePrice, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>

                            {{-- Commission pill --}}
                            <div class="mt-3">
                                <div class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 dark:border-emerald-800/60 dark:bg-emerald-900/30">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-3.5 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <circle cx="12" cy="12" r="8.25" fill="currentColor" fill-opacity=".15" stroke="currentColor" stroke-width="1.5"/>
                                        <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                                        Komisi Rp&nbsp;{{ number_format($estimatedComm, 0, ',', '.') }}
                                        @if ($isPercentage)
                                            <span class="font-normal opacity-70">({{ rtrim(rtrim(number_format($commissionValue, 2), '0'), '.') }}%)</span>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            {{-- Copy fields --}}
                            <div class="mt-4 flex flex-1 flex-col gap-3">
                                @include('epi-channel.partials.copy-field', [
                                    'label'   => 'Link produk referral',
                                    'value'   => $productLink,
                                    'fieldId' => 'product-link-'.$product->id,
                                ])

                                @if ($landingLink)
                                    <div class="rounded-[var(--radius-lg)] border border-emerald-100 bg-emerald-50/50 p-3 dark:border-emerald-900/50 dark:bg-emerald-950/20">
                                        <div class="mb-0.5 flex items-center gap-1.5">
                                            <span class="text-[11px] font-bold uppercase tracking-[0.15em] text-emerald-600 dark:text-emerald-500">Konversi tinggi</span>
                                        </div>
                                        @include('epi-channel.partials.copy-field', [
                                            'label'   => 'Link landing page',
                                            'value'   => $landingLink,
                                            'fieldId' => 'landing-link-'.$product->id,
                                        ])
                                    </div>
                                @endif
                            </div>

                            {{-- Action buttons --}}
                            <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-800">
                                {{-- Detail Produk --}}
                                <a
                                    href="{{ route('catalog.products.show', $product->slug) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1.5 rounded-[var(--radius-md)] border border-zinc-200 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-700 shadow-sm transition-all duration-150 hover:border-zinc-300 hover:bg-zinc-50 active:scale-95 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M12 5.75H6.75C5.64543 5.75 4.75 6.64543 4.75 7.75V17.25C4.75 18.3546 5.64543 19.25 6.75 19.25H16.25C17.3546 19.25 18.25 18.3546 18.25 17.25V12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M19.25 4.75L12.75 11.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M14.75 4.75H19.25V9.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Detail
                                </a>

                                {{-- Bagikan via WhatsApp --}}
                                <a
                                    href="{{ $waUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1.5 rounded-[var(--radius-md)] border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 shadow-sm transition-all duration-150 hover:border-emerald-300 hover:bg-emerald-100 active:scale-95 dark:border-emerald-800/60 dark:bg-emerald-900/30 dark:text-emerald-400 dark:hover:bg-emerald-900/50"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M12 4.75C7.99594 4.75 4.75 7.99594 4.75 12C4.75 13.343 5.10595 14.6026 5.73317 15.6893L4.75 19.25L8.39513 18.2897C9.45886 18.8892 10.6878 19.25 12 19.25C16.0041 19.25 19.25 16.0041 19.25 12C19.25 7.99594 16.0041 4.75 12 4.75Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M10 9C10 9 10.25 9.75 11 10.5C11.75 11.25 12.75 11.75 12.75 11.75L13.5 11C13.5 11 14 11.25 14.5 11.75C15 12.25 15 12.75 15 13C15 13.25 14.5 13.75 14 13.75C13.5 13.75 12 13.5 10.75 12.25C9.5 11 9.25 9.5 9.25 9C9.25 8.5 9.75 8 10 8C10.25 8 10.5 8 10.75 8.25C11 8.5 11.5 9.25 11.5 9.5C11.5 9.75 11.25 10 11 10.25" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    Bagikan WA
                                </a>

                                {{-- Buka Landing Page --}}
                                @if ($landingLink)
                                    <a
                                        href="{{ $landingLink }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 rounded-[var(--radius-md)] border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 shadow-sm transition-all duration-150 hover:border-blue-300 hover:bg-blue-100 active:scale-95 dark:border-blue-800/60 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <rect x="3.75" y="5.75" width="16.5" height="12.5" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M7.5 9.5H13M7.5 12H14.5M7.5 14.5H11" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                        </svg>
                                        Landing Page
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        @endif
    @include('epi-channel.partials.page-shell-end')
</x-layouts::app>
