<x-layouts::app :title="__('Daftar Klien')">
    @include('epi-channel.partials.page-shell-start')
        <x-ui.section-header
            eyebrow="EPI Channel"
            title="Daftar Klien"
            description="Pantau pendaftar referral Anda, status pembelian, omzet, dan follow-up dari satu dashboard."
        >
            <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.dashboard')">
                Dashboard EPIC
            </x-ui.button>
        </x-ui.section-header>

        @if (! $channel || ! $channel->isActive())
            <div class="mt-6">
                @include('epi-channel.partials.inactive-state', ['channel' => $channel, 'referrerContact' => $referrerContact])
            </div>
        @else
            @if (session('client_notice'))
                <div class="mt-6 rounded-[1.35rem] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                    {{ session('client_notice') }}
                </div>
            @endif

            @if (! $notesEnabled)
                <div class="mt-6 rounded-[1.35rem] border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    Catatan follow-up akan aktif setelah migration `affiliate_client_notes` dijalankan.
                </div>
            @endif

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <x-ui.card class="overflow-hidden p-5">
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Total Klien</div>
                    <div class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ number_format($summary['total_clients']) }}</div>
                    <div class="mt-1 text-sm text-slate-500">Seluruh klien referral yang dimiliki channel ini</div>
                </x-ui.card>
                <x-ui.card class="overflow-hidden border-amber-200/70 bg-amber-50/70 p-5">
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-700/70">Prospek Belum Beli</div>
                    <div class="mt-3 text-3xl font-semibold tracking-tight text-amber-900">{{ number_format($summary['prospects']) }}</div>
                    <div class="mt-1 text-sm text-amber-800/80">Belum memiliki order paid</div>
                </x-ui.card>
                <x-ui.card class="overflow-hidden border-emerald-200/70 bg-emerald-50/70 p-5">
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700/70">Sudah Beli</div>
                    <div class="mt-3 text-3xl font-semibold tracking-tight text-emerald-900">{{ number_format($summary['buyers']) }}</div>
                    <div class="mt-1 text-sm text-emerald-800/80">Minimal satu order paid</div>
                </x-ui.card>
                <x-ui.card class="overflow-hidden border-cyan-200/70 bg-cyan-50/70 p-5">
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700/70">Repeat Buyer</div>
                    <div class="mt-3 text-3xl font-semibold tracking-tight text-cyan-900">{{ number_format($summary['repeat_buyers']) }}</div>
                    <div class="mt-1 text-sm text-cyan-800/80">Lebih dari satu order paid</div>
                </x-ui.card>
                <x-ui.card class="overflow-hidden border-violet-200/70 bg-violet-50/70 p-5">
                    <div class="text-xs font-semibold uppercase tracking-[0.16em] text-violet-700/70">Total Omzet Referral</div>
                    <div class="mt-3 text-2xl font-semibold tracking-tight text-violet-950">Rp {{ number_format((float) $summary['referral_revenue'], 0, ',', '.') }}</div>
                    <div class="mt-1 text-sm text-violet-800/80">Akumulasi order paid yang teratribusi</div>
                </x-ui.card>
            </div>

            <div class="mt-5">
                <x-ui.card class="p-5 md:p-6">
                    <form method="GET" action="{{ route('dashboard.clients.index') }}" class="grid gap-4 xl:grid-cols-6">
                        <div class="xl:col-span-2">
                            <label for="clients-search" class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Cari Klien</label>
                            <input
                                id="clients-search"
                                type="text"
                                name="search"
                                value="{{ $filters['search'] }}"
                                placeholder="Nama, email, atau WhatsApp"
                                class="mt-2 w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100"
                            >
                        </div>

                        <div>
                            <label for="clients-status" class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Status Klien</label>
                            <select id="clients-status" name="status" class="mt-2 w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="clients-product" class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Produk</label>
                            <select id="clients-product" name="product_id" class="mt-2 w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                                <option value="">Semua Produk</option>
                                @foreach ($productOptions as $productId => $productTitle)
                                    <option value="{{ $productId }}" @selected((string) $filters['product_id'] === (string) $productId)>{{ $productTitle }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="clients-sort" class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Urutkan</label>
                            <select id="clients-sort" name="sort" class="mt-2 w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                                @foreach ($sortOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-end">
                            <div class="grid w-full grid-cols-2 gap-3">
                                <x-ui.button variant="ghost" size="md" :href="route('dashboard.clients.index')" class="w-full">
                                    Reset
                                </x-ui.button>
                                <x-ui.button variant="primary" size="md" type="submit" class="w-full">
                                    Cari
                                </x-ui.button>
                            </div>
                        </div>

                        <div>
                            <label for="clients-registered-from" class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Daftar Dari</label>
                            <input
                                id="clients-registered-from"
                                type="date"
                                name="registered_from"
                                value="{{ $filters['registered_from'] }}"
                                class="mt-2 w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100"
                            >
                        </div>

                        <div>
                            <label for="clients-registered-to" class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Daftar Sampai</label>
                            <input
                                id="clients-registered-to"
                                type="date"
                                name="registered_to"
                                value="{{ $filters['registered_to'] }}"
                                class="mt-2 w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100"
                            >
                        </div>

                        <div>
                            <label for="clients-last-order-from" class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Order Terakhir Dari</label>
                            <input
                                id="clients-last-order-from"
                                type="date"
                                name="last_order_from"
                                value="{{ $filters['last_order_from'] }}"
                                class="mt-2 w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100"
                            >
                        </div>

                        <div>
                            <label for="clients-last-order-to" class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Order Terakhir Sampai</label>
                            <input
                                id="clients-last-order-to"
                                type="date"
                                name="last_order_to"
                                value="{{ $filters['last_order_to'] }}"
                                class="mt-2 w-full rounded-[1rem] border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100"
                            >
                        </div>
                    </form>
                </x-ui.card>
            </div>

            <div class="mt-5">
                <x-ui.card class="overflow-hidden p-0">
                    <div class="hidden lg:block">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50/80">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">
                                        <th class="px-6 py-4">Klien</th>
                                        <th class="px-6 py-4">Kontak</th>
                                        <th class="px-6 py-4">Status</th>
                                        <th class="px-6 py-4">Order</th>
                                        <th class="px-6 py-4">Follow Up</th>
                                        <th class="px-6 py-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse ($clients as $client)
                                        <tr class="align-top">
                                            <td class="px-6 py-5">
                                                <div class="flex items-start gap-4">
                                                    <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#dbeafe,#e0f2fe)] text-sm font-semibold text-cyan-700">
                                                        {{ $client->initials() }}
                                                    </div>
                                                    <div class="min-w-0">
                                                        <div class="font-semibold text-slate-900">{{ $client->name }}</div>
                                                        <div class="mt-1 text-sm text-slate-500">Daftar {{ $client->created_at?->translatedFormat('d M Y') ?? '-' }}</div>
                                                        <div class="mt-2 text-xs text-slate-400">Order terakhir: {{ $client->last_paid_order_at ? \Illuminate\Support\Carbon::parse($client->last_paid_order_at)->translatedFormat('d M Y H:i') : 'Belum ada' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-5">
                                                <div class="text-sm font-medium text-slate-900">{{ $client->email }}</div>
                                                <div class="mt-1 text-sm text-emerald-700">{{ $client->whatsapp_number ?: '-' }}</div>
                                            </td>
                                            <td class="px-6 py-5">
                                                @include('dashboard.clients.partials.client-status-badges', ['badges' => $client->status_badges])
                                                <div class="mt-3 text-sm text-slate-500">{{ $client->latest_product_label ?: 'Belum ada produk dibeli' }}</div>
                                            </td>
                                            <td class="px-6 py-5">
                                                <div class="text-sm font-semibold text-slate-900">{{ number_format((int) $client->paid_orders_count) }} paid order</div>
                                                <div class="mt-1 text-sm text-slate-500">Rp {{ number_format((float) $client->paid_orders_total_amount, 0, ',', '.') }}</div>
                                            </td>
                                            <td class="px-6 py-5">
                                                @include('dashboard.clients.partials.follow-up-status-badge', ['followUpStatus' => $client->latest_note?->follow_up_status])
                                                <div class="mt-3 text-sm text-slate-500">
                                                    {{ \Illuminate\Support\Str::limit($client->latest_note?->note ?: 'Belum ada catatan follow-up.', 72) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-5">
                                                <div class="flex flex-col items-end gap-2">
                                                    @if ($client->whatsapp_url)
                                                        <x-ui.button variant="primary" size="sm" :href="$client->whatsapp_url" target="_blank" rel="noopener noreferrer">
                                                            WhatsApp Klien
                                                        </x-ui.button>
                                                    @endif
                                                    <x-ui.button variant="ghost" size="sm" :href="route('dashboard.clients.show', $client)">
                                                        Lihat Detail
                                                    </x-ui.button>
                                                    <x-ui.button variant="ghost" size="sm" :href="route('dashboard.clients.show', $client).'#catatan-baru'">
                                                        Tambah Catatan
                                                    </x-ui.button>
                                                    @if ($notesEnabled)
                                                        <form method="POST" action="{{ route('dashboard.clients.follow-up.store', $client) }}">
                                                            @csrf
                                                            <x-ui.button variant="secondary" size="sm" type="submit">
                                                                Tandai Sudah Follow Up
                                                            </x-ui.button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-12">
                                                <x-ui.empty-state
                                                    title="Belum ada klien"
                                                    description="Klien referral akan muncul di sini setelah ada pendaftar atau order yang terhubung ke channel Anda."
                                                />
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="grid gap-4 p-4 lg:hidden">
                        @forelse ($clients as $client)
                            <div class="rounded-[1.5rem] border border-slate-200 bg-white p-4 shadow-[0_10px_28px_rgba(15,23,42,0.05)]">
                                <div class="flex items-start gap-3">
                                    <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,#dbeafe,#e0f2fe)] text-sm font-semibold text-cyan-700">
                                        {{ $client->initials() }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold text-slate-900">{{ $client->name }}</div>
                                        <div class="mt-1 text-sm text-slate-500">{{ $client->email }}</div>
                                        <div class="mt-1 text-sm text-emerald-700">{{ $client->whatsapp_number ?: 'WhatsApp belum ada' }}</div>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    @include('dashboard.clients.partials.client-status-badges', ['badges' => $client->status_badges])
                                </div>

                                <div class="mt-4 grid gap-3 rounded-[1.25rem] bg-slate-50 p-4 text-sm">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-slate-500">Tanggal daftar</span>
                                        <span class="font-medium text-slate-900">{{ $client->created_at?->translatedFormat('d M Y') ?? '-' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-slate-500">Produk terakhir</span>
                                        <span class="font-medium text-right text-slate-900">{{ $client->latest_product_label ?: '-' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-slate-500">Paid order</span>
                                        <span class="font-medium text-slate-900">{{ number_format((int) $client->paid_orders_count) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-slate-500">Omzet</span>
                                        <span class="font-medium text-slate-900">Rp {{ number_format((float) $client->paid_orders_total_amount, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    @include('dashboard.clients.partials.follow-up-status-badge', ['followUpStatus' => $client->latest_note?->follow_up_status])
                                    <div class="mt-2 text-sm text-slate-500">
                                        {{ \Illuminate\Support\Str::limit($client->latest_note?->note ?: 'Belum ada catatan follow-up.', 96) }}
                                    </div>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    @if ($client->whatsapp_url)
                                        <x-ui.button variant="primary" size="sm" :href="$client->whatsapp_url" target="_blank" rel="noopener noreferrer">
                                            WhatsApp
                                        </x-ui.button>
                                    @endif
                                    <x-ui.button variant="ghost" size="sm" :href="route('dashboard.clients.show', $client)">
                                        Detail
                                    </x-ui.button>
                                    <x-ui.button variant="ghost" size="sm" :href="route('dashboard.clients.show', $client).'#catatan-baru'">
                                        Catatan
                                    </x-ui.button>
                                    @if ($notesEnabled)
                                        <form method="POST" action="{{ route('dashboard.clients.follow-up.store', $client) }}">
                                            @csrf
                                            <x-ui.button variant="secondary" size="sm" type="submit" class="w-full">
                                                Follow Up
                                            </x-ui.button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <x-ui.empty-state
                                title="Belum ada klien"
                                description="Klien referral akan muncul di sini setelah ada pendaftar atau order yang terhubung ke channel Anda."
                            />
                        @endforelse
                    </div>
                </x-ui.card>
            </div>

            @if ($clients->hasPages())
                <div class="mt-5">
                    {{ $clients->links() }}
                </div>
            @endif
        @endif
    @include('epi-channel.partials.page-shell-end')
</x-layouts::app>
