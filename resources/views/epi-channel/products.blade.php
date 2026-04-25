<x-layouts::app :title="__('Produk Promosi EPI Channel')">
    @include('epi-channel.partials.page-shell-start')
        <x-ui.section-header
            eyebrow="EPI Channel"
            title="Produk Promosi"
            description="Daftar produk affiliate aktif yang siap dipromosikan oleh channel kamu."
        >
            <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.links')">
                Link Promosi
            </x-ui.button>
        </x-ui.section-header>

        @if ($products->count() === 0)
            <div class="mt-6">
                <x-ui.empty-state
                    title="Belum ada produk affiliate"
                    description="Admin belum mengaktifkan affiliate untuk produk mana pun."
                />
            </div>
        @else
            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($products as $product)
                    @php
                        $productLink = route('catalog.products.show', $product->slug).'?ref='.$channel->epic_code;
                        $landingLink = $product->landing_page_enabled
                            ? route('offer.affiliate', ['product' => $product->slug, 'epicCode' => $channel->epic_code])
                            : null;
                        $effectivePrice = (float) $product->effective_price;
                        $estimatedCommission = $product->affiliate_commission_type?->value === 'percentage'
                            ? ($effectivePrice * ((float) $product->affiliate_commission_value / 100))
                            : (float) $product->affiliate_commission_value;
                    @endphp

                    <x-ui.card class="overflow-hidden p-0">
                        <div class="aspect-[16/9] bg-zinc-100 dark:bg-zinc-900">
                            @if ($product->thumbnail)
                                <img
                                    src="{{ asset('storage/'.$product->thumbnail) }}"
                                    alt="{{ $product->title }}"
                                    class="h-full w-full object-cover"
                                    loading="lazy"
                                />
                            @else
                                <div class="h-full w-full bg-[radial-gradient(circle_at_top_left,_rgba(34,211,238,0.35),_transparent_45%),linear-gradient(135deg,#18181b,#0f172a_45%,#1d4ed8)]"></div>
                            @endif
                        </div>

                        <div class="p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="text-base font-semibold text-zinc-900 dark:text-white">{{ $product->title }}</div>
                                    <div class="mt-1 text-sm text-zinc-500">{{ $product->product_type?->label() ?? $product->product_type?->value ?? '-' }}</div>
                                </div>
                                <div class="text-right text-sm font-semibold text-zinc-900 dark:text-white">
                                    Rp {{ number_format($effectivePrice, 0, ',', '.') }}
                                </div>
                            </div>

                            <div class="mt-5 grid gap-3 text-sm text-zinc-600 dark:text-zinc-300">
                                <div class="flex items-center justify-between gap-4">
                                    <span>Komisi</span>
                                    <span class="font-semibold text-zinc-900 dark:text-white">
                                        {{ $product->affiliate_commission_type?->label() ?? '-' }} {{ (float) $product->affiliate_commission_value }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span>Estimasi Komisi</span>
                                    <span class="font-semibold text-zinc-900 dark:text-white">
                                        Rp {{ number_format($estimatedCommission, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-5">
                                @include('epi-channel.partials.copy-field', [
                                    'label' => 'Copy link produk',
                                    'value' => $productLink,
                                    'fieldId' => 'promo-product-link-'.$product->id,
                                ])
                            </div>

                            <div class="mt-5 flex flex-wrap gap-2">
                                <x-ui.button variant="ghost" size="sm" :href="route('catalog.products.show', $product->slug)">Detail Produk</x-ui.button>
                                @if ($landingLink)
                                    <x-ui.button variant="secondary" size="sm" :href="$landingLink">Landing Page</x-ui.button>
                                @endif
                                <x-ui.button variant="ghost" size="sm" type="button" onclick="navigator.clipboard.writeText('{{ $productLink }}')">
                                    Copy Link
                                </x-ui.button>
                            </div>
                        </div>
                    </x-ui.card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        @endif
    @include('epi-channel.partials.page-shell-end')
</x-layouts::app>
