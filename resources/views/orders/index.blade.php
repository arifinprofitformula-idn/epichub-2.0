<x-layouts::public title="Riwayat Order">
    <section class="mx-auto max-w-[var(--container-5xl)] px-4 py-10">
        <x-ui.section-header
            title="Riwayat order"
            description="Daftar pesanan yang pernah Anda buat."
        />

        @if ($orders->count() === 0)
            <div class="mt-6">
                <x-ui.empty-state
                    title="Belum ada order"
                    description="Mulai dari katalog produk untuk membuat pesanan pertama Anda."
                >
                    <x-slot:action>
                        <x-ui.button variant="primary" :href="route('catalog.products.index')">
                            Jelajahi produk
                        </x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            </div>
        @else
            <div class="mt-6 grid gap-4">
                @foreach ($orders as $order)
                    <x-ui.card class="p-5 md:p-6">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ $order->order_number }}
                                    </div>
                                    <x-ui.badge variant="{{ $order->status->value === 'paid' ? 'success' : ($order->status->value === 'cancelled' ? 'danger' : 'neutral') }}">
                                        {{ $order->status->label() }}
                                    </x-ui.badge>
                                </div>
                                <div class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
                                    Dibuat {{ $order->created_at?->format('d M Y, H:i') }}
                                </div>
                                <div class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                                    @php($firstItem = $order->items->first())
                                    {{ $firstItem?->product_title ?? 'Produk' }}
                                    @if ($order->items->count() > 1)
                                        + {{ $order->items->count() - 1 }} item
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-4 md:flex-col md:items-end md:justify-center">
                                <div class="text-sm text-zinc-600 dark:text-zinc-300">Total</div>
                                <div class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $order->formatted_total }}</div>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <x-ui.button variant="secondary" size="sm" :href="route('orders.show', $order)">
                                Lihat invoice
                            </x-ui.button>
                            @php($latestPayment = $order->latestPayment())
                            @if ($latestPayment)
                                <x-ui.button variant="ghost" size="sm" :href="route('payments.show', $latestPayment)">
                                    Lihat pembayaran
                                </x-ui.button>
                            @endif
                        </div>
                    </x-ui.card>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        @endif
    </section>
</x-layouts::public>

