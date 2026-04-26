<x-layouts::app :title="__('Dashboard EPI Channel')">
    @include('epi-channel.partials.page-shell-start')
        <x-ui.section-header
            eyebrow="EPI Channel"
            title="Dashboard EPI Channel"
            description="Pantau status channel, link referral utama, performa klik, order referral, komisi, dan payout."
        >
            <x-ui.button variant="ghost" size="sm" :href="route('dashboard')">
                Dashboard utama
            </x-ui.button>
        </x-ui.section-header>

        @if (! $channel || ! $channel->isActive())
            <div class="mt-6">
                @include('epi-channel.partials.inactive-state', ['channel' => $channel, 'referrerContact' => $referrerContact])
            </div>
        @else
            @if ($whatsappReminderNeeded)
                <div class="mt-6 rounded-[1.5rem] border border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-900 shadow-[0_10px_24px_rgba(245,158,11,0.08)]">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <div class="font-semibold">Lengkapi nomor WhatsApp Anda</div>
                            <div class="mt-1 text-amber-800/80">
                                Lengkapi nomor WhatsApp di profil agar calon EPI Channel dapat menghubungi Anda.
                            </div>
                        </div>
                        <x-ui.button variant="secondary" size="sm" :href="route('profile.edit')">
                            Buka Profil
                        </x-ui.button>
                    </div>
                </div>
            @endif

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <x-ui.stat-card label="Status Channel" value="Aktif" :description="$channel->epic_code" />
                <x-ui.stat-card label="Total Clicks" :value="$stats['clicks']" description="Referral visits" />
                <x-ui.stat-card label="Referral Orders" :value="$stats['referral_orders']" description="Order teratribusikan" />
                <x-ui.stat-card label="Komisi Pending" :value="'Rp '.number_format((float) $stats['commission_pending_amount'], 0, ',', '.')" description="Menunggu approval" />
                <x-ui.stat-card label="Komisi Approved" :value="'Rp '.number_format((float) $stats['commission_approved_amount'], 0, ',', '.')" description="Siap dibayarkan" />
                <x-ui.stat-card label="Komisi Paid" :value="'Rp '.number_format((float) $stats['commission_paid_amount'], 0, ',', '.')" description="Sudah dibayarkan" />
                <x-ui.stat-card label="Payout Paid" :value="'Rp '.number_format((float) $stats['total_payout_paid'], 0, ',', '.')" description="Akumulasi payout" />
            </div>

            <div class="mt-6 grid gap-4 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.9fr)]">
                <x-ui.card class="p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <div class="text-sm font-semibold text-zinc-900 dark:text-white">Profil ringkas channel</div>
                            <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Informasi inti EPI Channel untuk aktivitas promosi harian.</div>
                        </div>
                        @include('epi-channel.partials.status-badge', ['status' => $channel->status])
                    </div>

                    <div class="mt-5 grid gap-3 text-sm text-zinc-600 dark:text-zinc-300 md:grid-cols-2">
                        <div class="rounded-[var(--radius-lg)] border border-zinc-200 bg-zinc-50/80 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/70">
                            <div class="text-xs uppercase tracking-[0.18em] text-zinc-400">EPIC Code</div>
                            <div class="mt-2 font-semibold text-zinc-900 dark:text-white">{{ $channel->epic_code }}</div>
                        </div>
                        <div class="rounded-[var(--radius-lg)] border border-zinc-200 bg-zinc-50/80 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/70">
                            <div class="text-xs uppercase tracking-[0.18em] text-zinc-400">Store Name</div>
                            <div class="mt-2 font-semibold text-zinc-900 dark:text-white">{{ $channel->store_name ?: '-' }}</div>
                        </div>
                    </div>

                    <div class="mt-5">
                        @include('epi-channel.partials.copy-field', [
                            'label' => 'Referral Link Utama',
                            'value' => $mainReferralLink,
                            'fieldId' => 'epi-channel-main-link',
                        ])
                    </div>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <x-ui.button variant="primary" size="sm" :href="route('epi-channel.links')">Link Promosi</x-ui.button>
                        <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.products')">Produk Promosi</x-ui.button>
                        <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.commissions')">Komisi</x-ui.button>
                        <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.payouts')">Payout</x-ui.button>
                    </div>
                </x-ui.card>

                <x-ui.card class="p-6">
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Recent commissions</div>
                    <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Lihat pergerakan komisi terbaru dari referral order milik channel kamu.</div>

                    <div class="mt-5 space-y-3">
                        @forelse ($recentCommissions as $commission)
                            <div class="rounded-[var(--radius-lg)] border border-zinc-200 px-4 py-3 dark:border-zinc-800">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="font-semibold text-zinc-900 dark:text-white">{{ $commission->product?->title ?? 'Produk' }}</div>
                                        <div class="mt-1 text-xs text-zinc-500">Order {{ $commission->order?->order_number ?? ('#'.$commission->order_id) }}</div>
                                    </div>
                                    @include('epi-channel.partials.status-badge', ['status' => $commission->status])
                                </div>
                                <div class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">
                                    Rp {{ number_format((float) $commission->commission_amount, 0, ',', '.') }}
                                </div>
                            </div>
                        @empty
                            <x-ui.empty-state
                                title="Belum ada komisi"
                                description="Komisi terbaru akan muncul di area ini setelah order referral diproses."
                            />
                        @endforelse
                    </div>
                </x-ui.card>
            </div>

            <div class="mt-6 grid gap-4 xl:grid-cols-[minmax(0,1.15fr)_minmax(320px,0.85fr)]">
                <x-ui.card class="p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-sm font-semibold text-zinc-900 dark:text-white">Produk promosi</div>
                            <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Produk affiliate aktif yang paling siap untuk kamu bagikan.</div>
                        </div>
                        <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.products')">
                            Lihat semua
                        </x-ui.button>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        @forelse ($featuredProducts as $product)
                            @php($productLink = route('catalog.products.show', $product->slug).'?ref='.$channel->epic_code)

                            <div class="rounded-[var(--radius-lg)] border border-zinc-200 p-4 dark:border-zinc-800">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="font-semibold text-zinc-900 dark:text-white">{{ $product->title }}</div>
                                        <div class="mt-1 text-xs text-zinc-500">{{ $product->product_type?->label() ?? $product->product_type?->value ?? '-' }}</div>
                                    </div>
                                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                        Rp {{ number_format((float) $product->effective_price, 0, ',', '.') }}
                                    </div>
                                </div>

                                <div class="mt-4">
                                    @include('epi-channel.partials.copy-field', [
                                        'label' => 'Link produk referral',
                                        'value' => $productLink,
                                        'fieldId' => 'dashboard-product-link-'.$product->id,
                                    ])
                                </div>
                            </div>
                        @empty
                            <div class="md:col-span-2">
                                <x-ui.empty-state
                                    title="Belum ada produk affiliate"
                                    description="Admin belum mengaktifkan affiliate untuk produk mana pun."
                                />
                            </div>
                        @endforelse
                    </div>
                </x-ui.card>

                <x-ui.card class="p-6">
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Top products by click</div>
                    <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">Produk yang paling banyak menerima referral visit dari channel kamu.</div>

                    <div class="mt-5 space-y-3">
                        @forelse ($topProductsByClick as $row)
                            <div class="flex items-center justify-between gap-4 rounded-[var(--radius-lg)] border border-zinc-200 px-4 py-3 dark:border-zinc-800">
                                <div class="min-w-0">
                                    <div class="font-semibold text-zinc-900 dark:text-white">{{ $row->product?->title ?? 'Produk tidak ditemukan' }}</div>
                                    <div class="mt-1 text-xs text-zinc-500">{{ $row->product?->slug ?? '-' }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $row->total_clicks }}</div>
                                    <div class="text-xs text-zinc-500">clicks</div>
                                </div>
                            </div>
                        @empty
                            <x-ui.empty-state
                                title="Belum ada data kunjungan"
                                description="Top product akan muncul setelah link referral mulai dikunjungi."
                            />
                        @endforelse
                    </div>
                </x-ui.card>
            </div>
        @endif
    @include('epi-channel.partials.page-shell-end')
</x-layouts::app>
