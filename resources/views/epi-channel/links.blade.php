<x-layouts::public title="Link Produk">
    <section class="mx-auto max-w-[var(--container-5xl)] px-4 py-10">
        <x-ui.section-header
            title="Link produk"
            description="Bagikan link referral untuk produk yang affiliate-nya aktif."
        >
            <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.index')">
                Dashboard penghasilan
            </x-ui.button>
        </x-ui.section-header>

        @if (! $channel || ! $channel->isActive())
            <div class="mt-6">
                <x-ui.empty-state
                    title="EPI Channel belum aktif"
                    description="Aktivasi dilakukan melalui OMS atau admin."
                />
            </div>
        @else
            @if ($products->count() === 0)
                <div class="mt-6">
                    <x-ui.empty-state
                        title="Belum ada produk affiliate"
                        description="Admin belum mengaktifkan affiliate untuk produk manapun."
                    />
                </div>
            @else
                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    @foreach ($products as $product)
                        @php($refLink = route('catalog.products.show', $product->slug).'?ref='.$channel->epic_code)

                        <x-ui.card class="p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $product->title }}</div>
                                    <div class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
                                        Komisi:
                                        @if ($product->affiliate_commission_type)
                                            {{ $product->affiliate_commission_type->value }} {{ (float) $product->affiliate_commission_value }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                                <x-ui.button variant="ghost" size="sm" :href="route('catalog.products.show', $product->slug)">Buka</x-ui.button>
                            </div>

                            <div class="mt-4">
                                <input
                                    type="text"
                                    readonly
                                    value="{{ $refLink }}"
                                    class="w-full rounded-[var(--radius-lg)] border border-zinc-200 bg-white px-3 py-2 text-xs text-zinc-900 shadow-sm outline-none ring-0 focus:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                                />
                            </div>
                        </x-ui.card>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            @endif
        @endif
    </section>
</x-layouts::public>

