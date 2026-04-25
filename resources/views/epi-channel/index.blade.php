<x-layouts::public title="Dashboard Penghasilan">
    <section class="mx-auto max-w-[var(--container-5xl)] px-4 py-10">
        <x-ui.section-header
            title="Dashboard Penghasilan"
            description="Pantau klik, order referral, dan komisi kamu di EPI Channel."
        >
            <x-ui.button variant="ghost" size="sm" :href="route('dashboard')">
                Kembali ke dashboard
            </x-ui.button>
        </x-ui.section-header>

        @if (! $channel || ! $channel->isActive())
            <div class="mt-6">
                <x-ui.empty-state
                    title="EPI Channel belum aktif"
                    description="Aktivasi dilakukan melalui OMS atau admin. Setelah aktif, dashboard penghasilan akan muncul di sini."
                >
                    <x-slot:action>
                        <x-ui.button variant="primary" :href="route('catalog.products.index')">
                            Jelajahi produk
                        </x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            </div>
        @else
            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <x-ui.stat-card label="Klik" :value="$stats['clicks']" description="Total referral visit" />
                <x-ui.stat-card label="Order Referral" :value="$stats['referral_orders']" description="Tercatat" />
                <x-ui.stat-card label="Komisi Pending" :value="'Rp '.number_format((float) $stats['commission_pending_amount'], 0, ',', '.')" :description="$stats['commission_pending_count'].' item'" />
                <x-ui.stat-card label="Komisi Approved" :value="'Rp '.number_format((float) $stats['commission_approved_amount'], 0, ',', '.')" :description="$stats['commission_approved_count'].' item'" />
                <x-ui.stat-card label="Komisi Paid" :value="'Rp '.number_format((float) $stats['commission_paid_amount'], 0, ',', '.')" :description="$stats['commission_paid_count'].' item'" />
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <x-ui.card class="p-6">
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Profil EPI Channel</div>
                    <div class="mt-3 grid gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                        <div class="flex items-center justify-between gap-4">
                            <div>EPIC Code</div>
                            <div class="font-semibold text-zinc-900 dark:text-white">{{ $channel->epic_code }}</div>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <div>Status</div>
                            <x-ui.badge variant="success">Aktif</x-ui.badge>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <div>Nama store</div>
                            <div class="font-semibold text-zinc-900 dark:text-white">{{ $channel->store_name ?: '-' }}</div>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="p-6">
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Referral link utama</div>
                    <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                        Gunakan link ini saat membagikan halaman produk.
                    </div>
                    <div class="mt-4">
                        <input
                            type="text"
                            readonly
                            value="{{ $mainReferralLink }}"
                            class="w-full rounded-[var(--radius-lg)] border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none ring-0 focus:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                        />
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <x-ui.button variant="secondary" size="sm" :href="route('epi-channel.links')">Link Produk</x-ui.button>
                        <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.commissions')">Komisi</x-ui.button>
                        <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.payouts')">Payout</x-ui.button>
                        <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.promo-assets')">Materi</x-ui.button>
                    </div>
                </x-ui.card>
            </div>

            <div class="mt-6">
                <x-ui.card class="p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-sm font-semibold text-zinc-900 dark:text-white">Produk untuk dipromosikan</div>
                            <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Pilih produk, lalu bagikan link referral.</div>
                        </div>
                        <x-ui.button variant="secondary" size="sm" :href="route('epi-channel.links')">Lihat semua</x-ui.button>
                    </div>

                    @if ($featuredProducts->count() === 0)
                        <div class="mt-6">
                            <x-ui.empty-state
                                title="Belum ada produk affiliate"
                                description="Admin belum mengaktifkan affiliate untuk produk manapun."
                            />
                        </div>
                    @else
                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            @foreach ($featuredProducts as $product)
                                @php($refLink = route('catalog.products.show', $product->slug).'?ref='.$channel->epic_code)

                                <x-ui.card class="p-5">
                                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $product->title }}</div>
                                    <div class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
                                        Komisi:
                                        @if ($product->affiliate_commission_type)
                                            {{ $product->affiliate_commission_type->value }} {{ (float) $product->affiliate_commission_value }}
                                        @else
                                            -
                                        @endif
                                    </div>

                                    <div class="mt-4">
                                        <input
                                            type="text"
                                            readonly
                                            value="{{ $refLink }}"
                                            class="w-full rounded-[var(--radius-lg)] border border-zinc-200 bg-white px-3 py-2 text-xs text-zinc-900 shadow-sm outline-none ring-0 focus:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                                        />
                                    </div>

                                    <div class="mt-4">
                                        <x-ui.button variant="ghost" size="sm" :href="route('catalog.products.show', $product->slug)">Buka produk</x-ui.button>
                                    </div>
                                </x-ui.card>
                            @endforeach
                        </div>
                    @endif
                </x-ui.card>
            </div>
        @endif
    </section>
</x-layouts::public>

