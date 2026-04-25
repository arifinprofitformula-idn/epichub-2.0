<x-layouts::public title="Checkout">
    <section class="mx-auto max-w-[var(--container-5xl)] px-4 py-10">
        <div class="mb-6 flex items-center justify-between gap-3">
            <x-ui.button variant="ghost" size="sm" :href="route('catalog.products.show', $product->slug)">
                ← Kembali ke produk
            </x-ui.button>

            <x-ui.button variant="ghost" size="sm" :href="route('orders.index')">
                Riwayat order
            </x-ui.button>
        </div>

        <div class="grid gap-6 lg:grid-cols-5">
            <div class="lg:col-span-3">
                <x-ui.card class="p-6 md:p-8">
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Ringkasan pembelian</div>

                    <div class="mt-4 rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 dark:border-zinc-800">
                        <div class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $product->title }}</div>
                        <div class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
                            {{ $product->product_type?->label() ?? $product->product_type }}
                        </div>

                        <div class="mt-4 flex items-end justify-between gap-4">
                            <div class="text-sm text-zinc-600 dark:text-zinc-300">Total</div>
                            <div class="text-xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                                Rp {{ number_format((float) $product->effective_price, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    @if ($errors->has('checkout'))
                        <div class="mt-4">
                            <x-ui.alert variant="danger" title="Checkout gagal">
                                {{ $errors->first('checkout') }}
                            </x-ui.alert>
                        </div>
                    @endif

                    @if (! $isEligible)
                        <div class="mt-4">
                            <x-ui.alert variant="warning" title="Belum tersedia">
                                {{ $eligibilityMessage ?? 'Produk ini belum tersedia untuk checkout saat ini.' }}
                            </x-ui.alert>
                        </div>
                    @endif

                    <form class="mt-6" method="POST" action="{{ route('checkout.store', $product->slug) }}">
                        @csrf

                        <x-ui.button variant="primary" size="lg" type="submit" :disabled="! $isEligible">
                            Buat pesanan & lanjut bayar
                        </x-ui.button>
                    </form>

                    <div class="mt-4 text-xs text-zinc-500 dark:text-zinc-400">
                        Anda akan diarahkan ke halaman instruksi transfer dan upload bukti pembayaran.
                    </div>
                </x-ui.card>
            </div>

            <div class="lg:col-span-2">
                <x-ui.card class="p-6 md:p-8">
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Metode pembayaran</div>
                    <div class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                        Transfer bank manual. Admin akan memverifikasi pembayaran setelah bukti diupload.
                    </div>

                    <div class="mt-4 rounded-[var(--radius-xl)] border border-zinc-200/70 p-4 text-sm dark:border-zinc-800">
                        <div class="font-semibold text-zinc-900 dark:text-white">
                            {{ data_get(config('epichub.payments.manual_bank_transfer'), 'bank_name') }}
                        </div>
                        <div class="mt-1 text-zinc-600 dark:text-zinc-300">
                            No. Rek: {{ data_get(config('epichub.payments.manual_bank_transfer'), 'account_number') }}
                        </div>
                        <div class="mt-1 text-zinc-600 dark:text-zinc-300">
                            A/N: {{ data_get(config('epichub.payments.manual_bank_transfer'), 'account_name') }}
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </section>
</x-layouts::public>

