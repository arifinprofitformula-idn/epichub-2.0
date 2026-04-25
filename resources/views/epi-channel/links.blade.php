<x-layouts::app :title="__('Link Promosi EPI Channel')">
    @include('epi-channel.partials.page-shell-start')
        <x-ui.section-header
            eyebrow="EPI Channel"
            title="Link Promosi"
            description="Bagikan referral link utama, link produk, landing page affiliate, dan checkout link untuk produk affiliate aktif."
        >
            <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.dashboard')">
                Dashboard EPI Channel
            </x-ui.button>
        </x-ui.section-header>

        <div class="mt-6">
            <x-ui.card class="p-6">
                <div class="text-sm font-semibold text-zinc-900 dark:text-white">Referral link utama</div>
                <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Link global untuk membawa visitor ke referral redirect EPI Channel kamu.</div>

                <div class="mt-5">
                    @include('epi-channel.partials.copy-field', [
                        'label' => '/r/{epicCode}',
                        'value' => $mainReferralLink,
                        'fieldId' => 'epi-channel-main-referral-link',
                    ])
                </div>
            </x-ui.card>
        </div>

        @if ($products->count() === 0)
            <div class="mt-6">
                <x-ui.empty-state
                    title="Belum ada produk affiliate"
                    description="Admin belum mengaktifkan affiliate untuk produk mana pun."
                />
            </div>
        @else
            <div class="mt-6 grid gap-4 xl:grid-cols-2">
                @foreach ($products as $product)
                    @php($productLink = route('catalog.products.show', $product->slug).'?ref='.$channel->epic_code)
                    @php($landingLink = $product->landing_page_enabled ? route('offer.affiliate', ['product' => $product->slug, 'epicCode' => $channel->epic_code]) : null)
                    @php($checkoutLink = route('checkout.show', $product->slug).'?ref='.$channel->epic_code)

                    <x-ui.card class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-base font-semibold text-zinc-900 dark:text-white">{{ $product->title }}</div>
                                <div class="mt-1 text-sm text-zinc-500">{{ $product->product_type?->label() ?? $product->product_type?->value ?? '-' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    Rp {{ number_format((float) $product->effective_price, 0, ',', '.') }}
                                </div>
                                <div class="mt-1 text-xs text-zinc-500">
                                    @if ($product->affiliate_commission_type)
                                        {{ $product->affiliate_commission_type->label() }} {{ (float) $product->affiliate_commission_value }}
                                    @else
                                        Komisi belum diatur
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 space-y-4">
                            @include('epi-channel.partials.copy-field', [
                                'label' => 'Product referral link',
                                'value' => $productLink,
                                'fieldId' => 'product-ref-link-'.$product->id,
                            ])

                            @if ($landingLink)
                                @include('epi-channel.partials.copy-field', [
                                    'label' => 'Landing page affiliate link',
                                    'value' => $landingLink,
                                    'fieldId' => 'product-landing-link-'.$product->id,
                                ])
                            @endif

                            @include('epi-channel.partials.copy-field', [
                                'label' => 'Checkout link',
                                'value' => $checkoutLink,
                                'fieldId' => 'product-checkout-link-'.$product->id,
                            ])
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <x-ui.button variant="ghost" size="sm" :href="route('catalog.products.show', $product->slug)">Detail Produk</x-ui.button>
                            @if ($landingLink)
                                <x-ui.button variant="secondary" size="sm" :href="$landingLink">Landing Page</x-ui.button>
                            @endif
                            <x-ui.button variant="ghost" size="sm" :href="route('checkout.show', $product->slug)">Checkout</x-ui.button>
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
