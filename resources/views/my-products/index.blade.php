<x-layouts::public title="Produk Saya">
    <section class="mx-auto max-w-[var(--container-5xl)] px-4 py-10">
        <x-ui.section-header
            title="Produk saya"
            description="Daftar akses produk yang sedang aktif di akun Anda."
        >
            <x-ui.button variant="ghost" size="sm" :href="route('orders.index')">
                Riwayat order
            </x-ui.button>
        </x-ui.section-header>

        @if ($userProducts->count() === 0)
            <div class="mt-6">
                <x-ui.empty-state
                    title="Belum ada produk"
                    description="Setelah pembayaran diverifikasi, produk akan muncul otomatis di sini."
                >
                    <x-slot:action>
                        <x-ui.button variant="primary" :href="route('catalog.products.index')">
                            Jelajahi produk
                        </x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            </div>
        @else
            <div class="mt-6 grid gap-4 md:grid-cols-2">
                @foreach ($userProducts as $userProduct)
                    <x-ui.card class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ $userProduct->product?->title ?? 'Produk' }}
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-zinc-600 dark:text-zinc-300">
                                    <x-ui.badge variant="info">
                                        {{ $userProduct->product?->product_type?->label() ?? ($userProduct->product?->product_type ?? '-') }}
                                    </x-ui.badge>
                                    <x-ui.badge variant="{{ $userProduct->status->value === 'active' ? 'success' : 'neutral' }}">
                                        {{ $userProduct->status->label() }}
                                    </x-ui.badge>
                                </div>
                            </div>
                            <x-ui.button variant="secondary" size="sm" :href="route('my-products.show', $userProduct)">
                                Lihat akses
                            </x-ui.button>
                        </div>

                        <div class="mt-4 grid gap-2 text-xs text-zinc-600 dark:text-zinc-300">
                            <div class="flex items-center justify-between gap-4">
                                <div>Sumber</div>
                                <div class="font-semibold text-zinc-900 dark:text-white">
                                    @if ($userProduct->source_product_id)
                                        Bundle
                                    @elseif ($userProduct->order_id)
                                        Order
                                    @else
                                        Manual
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <div>Diberikan</div>
                                <div class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $userProduct->granted_at?->format('d M Y, H:i') ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </x-ui.card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $userProducts->links() }}
            </div>
        @endif
    </section>
</x-layouts::public>

