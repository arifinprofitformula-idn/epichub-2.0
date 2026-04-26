<x-layouts::app :title="__('Dashboard EPI Channel')">
    @include('epi-channel.partials.page-shell-start')
        <x-ui.section-header
            eyebrow="EPI Channel"
            title="Dashboard EPI Channel"
            description="Pantau status channel, link referral utama, performa kunjungan, order referral, komisi, dan payout."
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
            {{-- WhatsApp reminder --}}
            @if ($whatsappReminderNeeded)
                <div class="mt-6 rounded-[1.5rem] border border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-900 shadow-[0_10px_24px_rgba(245,158,11,0.08)]">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div class="flex items-start gap-3">
                            <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 size-4 shrink-0 text-amber-600" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M12 9V13M12 16.5V17" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                <path d="M4.9 19H19.1C20.3 19 21.1 17.7 20.5 16.6L13.4 4.1C12.8 3 11.2 3 10.6 4.1L3.5 16.6C2.9 17.7 3.7 19 4.9 19Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                            </svg>
                            <div>
                                <div class="font-semibold">Lengkapi nomor WhatsApp Anda</div>
                                <div class="mt-0.5 text-amber-800/80">Nomor WhatsApp diperlukan agar calon EPI Channel dapat menghubungi Anda.</div>
                            </div>
                        </div>
                        <x-ui.button variant="secondary" size="sm" :href="route('profile.edit')">
                            Buka Profil
                        </x-ui.button>
                    </div>
                </div>
            @endif

            {{-- Hero: Channel profile + Referral link --}}
            <div class="mt-6">
                <x-ui.card class="overflow-hidden p-0">
                    <div class="grid xl:grid-cols-[1fr_minmax(380px,0.6fr)]">
                        <div class="p-6 md:p-7">
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="flex size-10 shrink-0 items-center justify-center rounded-[var(--radius-lg)] bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M12 3.75L4.75 7.25V12C4.75 16.1023 7.59367 19.9093 12 20.75C16.4063 19.9093 19.25 16.1023 19.25 12V7.25L12 3.75Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                        <path d="M9.5 11.75L11.25 13.5L14.75 10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-400 dark:text-zinc-500">EPI Channel</div>
                                    <div class="truncate text-base font-semibold text-zinc-900 dark:text-white">
                                        {{ $channel->store_name ?: $channel->epic_code }}
                                    </div>
                                </div>
                                <div class="ml-auto">
                                    @include('epi-channel.partials.status-badge', ['status' => $channel->status])
                                </div>
                            </div>

                            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-2">
                                <div class="rounded-[var(--radius-lg)] border border-zinc-100 bg-zinc-50/70 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/60">
                                    <div class="text-xs font-medium uppercase tracking-[0.14em] text-zinc-400 dark:text-zinc-500">EPIC Code</div>
                                    <div class="mt-1.5 font-semibold text-zinc-900 dark:text-white">{{ $channel->epic_code }}</div>
                                </div>
                                <div class="rounded-[var(--radius-lg)] border border-zinc-100 bg-zinc-50/70 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/60">
                                    <div class="text-xs font-medium uppercase tracking-[0.14em] text-zinc-400 dark:text-zinc-500">Nama Toko</div>
                                    <div class="mt-1.5 font-semibold text-zinc-900 dark:text-white">{{ $channel->store_name ?: '—' }}</div>
                                </div>
                            </div>

                            <div class="mt-5 flex flex-wrap gap-2">
                                <x-ui.button variant="primary" size="sm" :href="route('epi-channel.links')">Link Promosi</x-ui.button>
                                <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.products')">Produk</x-ui.button>
                                <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.commissions')">Komisi</x-ui.button>
                                <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.payouts')">Payout</x-ui.button>
                            </div>
                        </div>

                        <div class="flex flex-col justify-center border-t border-zinc-100 bg-zinc-50/50 p-6 dark:border-zinc-800 dark:bg-zinc-900/40 md:p-7 xl:border-l xl:border-t-0">
                            <div class="text-xs font-semibold uppercase tracking-[0.16em] text-zinc-500 dark:text-zinc-400">Link Referral Utama</div>
                            <div class="mt-3">
                                @include('epi-channel.partials.copy-field', [
                                    'label' => '',
                                    'value' => $mainReferralLink,
                                    'fieldId' => 'epi-channel-main-link',
                                ])
                            </div>
                            <div class="mt-3 text-xs text-zinc-500 dark:text-zinc-400">
                                Bagikan link ini untuk mendapatkan komisi dari setiap order referral.
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            {{-- Primary stats row --}}
            <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">
                <x-ui.card class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Kunjungan Referral</div>
                            <div class="mt-1 text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ number_format($stats['clicks']) }}</div>
                            <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Total klik link</div>
                        </div>
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-[var(--radius-lg)] bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                            <svg viewBox="0 0 24 24" fill="none" class="size-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M3.75 12H12M12 12L8.5 8.5M12 12L8.5 15.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 4.75C16.0041 4.75 19.25 7.99594 19.25 12C19.25 16.0041 16.0041 19.25 12 19.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Order Referral</div>
                            <div class="mt-1 text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ number_format($stats['referral_orders']) }}</div>
                            <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Teratribusikan</div>
                        </div>
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-[var(--radius-lg)] bg-violet-50 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400">
                            <svg viewBox="0 0 24 24" fill="none" class="size-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M5.5 7.5H18.5L17 17.5H7L5.5 7.5Z" fill="currentColor" fill-opacity=".15" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                <path d="M5.5 7.5L4.5 4.5H2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="9" cy="20.25" r="1.25" fill="currentColor"/>
                                <circle cx="15.5" cy="20.25" r="1.25" fill="currentColor"/>
                            </svg>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Komisi Disetujui</div>
                            <div class="mt-1 text-xl font-semibold tracking-tight text-zinc-900 dark:text-white">Rp&nbsp;{{ number_format((float) $stats['commission_approved_amount'], 0, ',', '.') }}</div>
                            <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Siap dibayarkan</div>
                        </div>
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-[var(--radius-lg)] bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                            <svg viewBox="0 0 24 24" fill="none" class="size-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <circle cx="12" cy="12" r="8.25" fill="currentColor" fill-opacity=".12" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Total Payout</div>
                            <div class="mt-1 text-xl font-semibold tracking-tight text-zinc-900 dark:text-white">Rp&nbsp;{{ number_format((float) $stats['total_payout_paid'], 0, ',', '.') }}</div>
                            <div class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Akumulasi dibayar</div>
                        </div>
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-[var(--radius-lg)] bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                            <svg viewBox="0 0 24 24" fill="none" class="size-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <rect x="3.75" y="6.75" width="16.5" height="11.5" rx="2.25" fill="currentColor" fill-opacity=".12" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M3.75 10H20.25" stroke="currentColor" stroke-width="1.5"/>
                                <circle cx="8.5" cy="14" r="1" fill="currentColor"/>
                            </svg>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            {{-- Commission breakdown strip --}}
            <div class="mt-3 grid grid-cols-3 gap-3">
                <div class="rounded-[var(--radius-xl)] border border-zinc-200/80 bg-white px-4 py-3.5 shadow-[var(--shadow-soft)] dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Komisi Pending</div>
                    <div class="mt-1 text-base font-semibold tracking-tight text-amber-600 dark:text-amber-400">Rp&nbsp;{{ number_format((float) $stats['commission_pending_amount'], 0, ',', '.') }}</div>
                    <div class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">Menunggu approval</div>
                </div>
                <div class="rounded-[var(--radius-xl)] border border-zinc-200/80 bg-white px-4 py-3.5 shadow-[var(--shadow-soft)] dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Komisi Disetujui</div>
                    <div class="mt-1 text-base font-semibold tracking-tight text-emerald-600 dark:text-emerald-400">Rp&nbsp;{{ number_format((float) $stats['commission_approved_amount'], 0, ',', '.') }}</div>
                    <div class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">Siap dicairkan</div>
                </div>
                <div class="rounded-[var(--radius-xl)] border border-zinc-200/80 bg-white px-4 py-3.5 shadow-[var(--shadow-soft)] dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Komisi Dibayar</div>
                    <div class="mt-1 text-base font-semibold tracking-tight text-zinc-900 dark:text-white">Rp&nbsp;{{ number_format((float) $stats['commission_paid_amount'], 0, ',', '.') }}</div>
                    <div class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">Sudah dicairkan</div>
                </div>
            </div>

            {{-- Products + Recent commissions --}}
            <div class="mt-4 grid gap-4 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.9fr)]">
                <x-ui.card class="p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-sm font-semibold text-zinc-900 dark:text-white">Produk promosi</div>
                            <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Produk affiliate aktif yang siap kamu bagikan.</div>
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
                                    <div class="shrink-0 text-sm font-semibold text-zinc-900 dark:text-white">
                                        Rp&nbsp;{{ number_format((float) $product->effective_price, 0, ',', '.') }}
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
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-sm font-semibold text-zinc-900 dark:text-white">Komisi terbaru</div>
                            <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Pergerakan komisi terbaru channel kamu.</div>
                        </div>
                        <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.commissions')">
                            Lihat semua
                        </x-ui.button>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse ($recentCommissions as $commission)
                            <div class="rounded-[var(--radius-lg)] border border-zinc-200 px-4 py-3 dark:border-zinc-800">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="truncate font-semibold text-zinc-900 dark:text-white">{{ $commission->product?->title ?? 'Produk' }}</div>
                                        <div class="mt-1 text-xs text-zinc-500">Order {{ $commission->order?->order_number ?? ('#'.$commission->order_id) }}</div>
                                    </div>
                                    @include('epi-channel.partials.status-badge', ['status' => $commission->status])
                                </div>
                                <div class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">
                                    Rp&nbsp;{{ number_format((float) $commission->commission_amount, 0, ',', '.') }}
                                </div>
                            </div>
                        @empty
                            <x-ui.empty-state
                                title="Belum ada komisi"
                                description="Komisi terbaru muncul setelah order referral diproses."
                            />
                        @endforelse
                    </div>
                </x-ui.card>
            </div>

            {{-- Top products by click --}}
            <div class="mt-4">
                <x-ui.card class="p-6">
                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">Top produk berdasarkan kunjungan</div>
                    <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Produk yang paling banyak menerima kunjungan dari link referral channel kamu.</div>

                    <div class="mt-5">
                        @forelse ($topProductsByClick as $index => $row)
                            <div class="flex items-center justify-between gap-4 {{ ! $loop->first ? 'mt-3 border-t border-zinc-100 pt-3 dark:border-zinc-800' : '' }}">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div class="flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-bold tabular-nums {{ $index === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate font-semibold text-zinc-900 dark:text-white">{{ $row->product?->title ?? 'Produk tidak ditemukan' }}</div>
                                        <div class="mt-0.5 truncate text-xs text-zinc-500">{{ $row->product?->slug ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <div class="text-sm font-semibold text-zinc-900 dark:text-white">{{ number_format($row->total_clicks) }}</div>
                                    <div class="text-xs text-zinc-500">kunjungan</div>
                                </div>
                            </div>
                        @empty
                            <x-ui.empty-state
                                title="Belum ada data kunjungan"
                                description="Top produk akan muncul setelah link referral mulai dikunjungi."
                            />
                        @endforelse
                    </div>
                </x-ui.card>
            </div>
        @endif
    @include('epi-channel.partials.page-shell-end')
</x-layouts::app>
