<x-layouts::public :title="'Invoice '.$order->order_number">
    <section class="mx-auto max-w-[var(--container-5xl)] px-4 py-10">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <x-ui.button variant="ghost" size="sm" :href="route('orders.index')">
                ← Kembali
            </x-ui.button>

            <div class="flex items-center gap-2">
                <x-ui.badge variant="{{ $order->status->value === 'paid' ? 'success' : ($order->status->value === 'cancelled' ? 'danger' : 'neutral') }}">
                    {{ $order->status->label() }}
                </x-ui.badge>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-5">
            <div class="lg:col-span-3">
                <x-ui.card class="p-6 md:p-8">
                    <div class="flex flex-col gap-1">
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Invoice</div>
                        <div class="text-xs text-zinc-600 dark:text-zinc-300">
                            {{ $order->order_number }} • {{ $order->created_at?->format('d M Y, H:i') }}
                        </div>
                    </div>

                    <div class="mt-6 rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 dark:border-zinc-800">
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Pembeli</div>
                        <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                            <div>{{ $order->customer_name ?: '-' }}</div>
                            <div>{{ $order->customer_email ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">Item</div>
                        <div class="mt-3 grid gap-3">
                            @foreach ($order->items as $item)
                                <div class="rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 dark:border-zinc-800">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                                {{ $item->product_title }}
                                            </div>
                                            <div class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
                                                {{ $item->product_type }}
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                                Rp {{ number_format((float) $item->subtotal_amount, 0, ',', '.') }}
                                            </div>
                                            <div class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
                                                Qty {{ $item->quantity }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-6 rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 dark:border-zinc-800">
                        <div class="flex items-center justify-between gap-4">
                            <div class="text-sm text-zinc-600 dark:text-zinc-300">Total</div>
                            <div class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $order->formatted_total }}</div>
                        </div>
                    </div>

                    @php($latestPayment = $order->latestPayment())
                    @if ($latestPayment)
                        <div class="mt-6">
                            <x-ui.button variant="primary" :href="route('payments.show', $latestPayment)">
                                Lanjut pembayaran
                            </x-ui.button>
                        </div>
                    @endif
                </x-ui.card>
            </div>

            <div class="lg:col-span-2">
                <x-ui.card class="p-6 md:p-8">
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Pembayaran</div>

                    @if (! $latestPayment)
                        <div class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                            Belum ada payment untuk order ini.
                        </div>
                    @else
                        <div class="mt-4 rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 dark:border-zinc-800">
                            <div class="text-xs text-zinc-600 dark:text-zinc-300">Payment No.</div>
                            <div class="mt-1 text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ $latestPayment->payment_number }}
                            </div>
                            <div class="mt-3 text-xs text-zinc-600 dark:text-zinc-300">Status</div>
                            <div class="mt-1">
                                <x-ui.badge variant="{{ $latestPayment->status->value === 'success' ? 'success' : 'neutral' }}">
                                    {{ $latestPayment->status->label() }}
                                </x-ui.badge>
                            </div>
                        </div>
                    @endif

                    @if ($order->status->value !== 'paid')
                        <form class="mt-6" method="POST" action="{{ route('orders.cancel', $order) }}">
                            @csrf
                            <x-ui.button variant="danger" size="sm" type="submit">
                                Batalkan order
                            </x-ui.button>
                        </form>
                    @endif
                </x-ui.card>
            </div>
        </div>
    </section>
</x-layouts::public>

