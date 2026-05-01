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
                    <form method="GET" action="{{ route('dashboard.clients.index') }}" class="grid gap-4 xl:grid-cols-[minmax(0,1.45fr)_minmax(220px,0.8fr)_minmax(220px,0.8fr)_auto]">
                        <div>
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
                                                <div class="flex items-center justify-end gap-2">
                                                    @if ($client->whatsapp_url)
                                                        <a
                                                            href="{{ $client->whatsapp_url }}"
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            class="inline-flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100 hover:text-emerald-700"
                                                            title="WhatsApp Klien"
                                                            aria-label="WhatsApp Klien"
                                                        >
                                                            <svg viewBox="0 0 24 24" fill="none" class="size-4.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                                <path d="M12 4.75C8.00594 4.75 4.75 8.00594 4.75 12C4.75 13.3769 5.13832 14.666 5.81265 15.7654L4.75 19.25L8.38052 18.2263C9.44352 18.8367 10.6811 19.25 12 19.25C15.9941 19.25 19.25 15.9941 19.25 12C19.25 8.00594 15.9941 4.75 12 4.75Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                                                <path d="M9.8 9.35C9.55 8.8 9.28 8.79 9.05 8.78C8.85 8.77 8.63 8.77 8.42 8.77C8.21 8.77 7.87 8.85 7.58 9.16C7.29 9.47 6.46 10.23 6.46 11.79C6.46 13.36 7.61 14.87 7.77 15.08C7.92 15.28 9.96 18.43 13.15 19.67C15.79 20.69 16.33 20.49 16.91 20.44C17.49 20.39 18.78 19.68 19.05 18.93C19.32 18.18 19.32 17.55 19.24 17.42C19.16 17.29 18.95 17.21 18.64 17.06C18.34 16.91 16.84 16.17 16.56 16.06C16.28 15.96 16.08 15.91 15.88 16.22C15.68 16.53 15.09 17.21 14.92 17.41C14.74 17.62 14.57 17.64 14.27 17.49C13.97 17.33 13.01 17.02 11.87 15.99C10.99 15.2 10.39 14.22 10.21 13.91C10.04 13.6 10.19 13.43 10.34 13.28C10.48 13.14 10.64 12.91 10.79 12.73C10.94 12.55 10.99 12.42 11.09 12.22C11.19 12.01 11.14 11.83 11.06 11.67C10.99 11.5 10.39 9.99 9.8 9.35Z" fill="currentColor"/>
                                                            </svg>
                                                            <span class="sr-only">WhatsApp Klien</span>
                                                        </a>
                                                    @endif
                                                    <a
                                                        href="{{ route('dashboard.clients.show', $client) }}"
                                                        class="inline-flex size-9 items-center justify-center rounded-xl bg-slate-100 text-slate-600 transition hover:bg-slate-200 hover:text-slate-800"
                                                        title="Lihat Detail"
                                                        aria-label="Lihat Detail"
                                                    >
                                                        <svg viewBox="0 0 24 24" fill="none" class="size-4.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                            <path d="M2.75 12S6.5 6.75 12 6.75S21.25 12 21.25 12S17.5 17.25 12 17.25S2.75 12 2.75 12Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                                            <circle cx="12" cy="12" r="2.75" stroke="currentColor" stroke-width="1.5"/>
                                                        </svg>
                                                        <span class="sr-only">Lihat Detail</span>
                                                    </a>
                                                    <a
                                                        href="{{ route('dashboard.clients.show', $client).'#catatan-baru' }}"
                                                        class="inline-flex size-9 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600 transition hover:bg-cyan-100 hover:text-cyan-700"
                                                        title="Tambah Catatan"
                                                        aria-label="Tambah Catatan"
                                                    >
                                                        <svg viewBox="0 0 24 24" fill="none" class="size-4.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                            <path d="M4.75 19.25H8.25L18.25 9.25C18.9404 8.55964 18.9404 7.44036 18.25 6.75V6.75C17.5596 6.05964 16.4404 6.05964 15.75 6.75L5.75 16.75V19.25Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                                            <path d="M13.75 8.75L16.25 11.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                        </svg>
                                                        <span class="sr-only">Tambah Catatan</span>
                                                    </a>
                                                    @if ($notesEnabled)
                                                        <form method="POST" action="{{ route('dashboard.clients.follow-up.store', $client) }}">
                                                            @csrf
                                                            <button
                                                                type="submit"
                                                                class="inline-flex size-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 transition hover:bg-amber-100 hover:text-amber-700"
                                                                title="Tandai Sudah Follow Up"
                                                                aria-label="Tandai Sudah Follow Up"
                                                            >
                                                                <svg viewBox="0 0 24 24" fill="none" class="size-4.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                                    <path d="M5 12.5L9.25 16.75L19 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                                </svg>
                                                                <span class="sr-only">Tandai Sudah Follow Up</span>
                                                            </button>
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

                                <div class="mt-4 flex flex-wrap gap-2">
                                    @if ($client->whatsapp_url)
                                        <a
                                            href="{{ $client->whatsapp_url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100 hover:text-emerald-700"
                                            title="WhatsApp Klien"
                                            aria-label="WhatsApp Klien"
                                        >
                                            <svg viewBox="0 0 24 24" fill="none" class="size-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M12 4.75C8.00594 4.75 4.75 8.00594 4.75 12C4.75 13.3769 5.13832 14.666 5.81265 15.7654L4.75 19.25L8.38052 18.2263C9.44352 18.8367 10.6811 19.25 12 19.25C15.9941 19.25 19.25 15.9941 19.25 12C19.25 8.00594 15.9941 4.75 12 4.75Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                                <path d="M9.8 9.35C9.55 8.8 9.28 8.79 9.05 8.78C8.85 8.77 8.63 8.77 8.42 8.77C8.21 8.77 7.87 8.85 7.58 9.16C7.29 9.47 6.46 10.23 6.46 11.79C6.46 13.36 7.61 14.87 7.77 15.08C7.92 15.28 9.96 18.43 13.15 19.67C15.79 20.69 16.33 20.49 16.91 20.44C17.49 20.39 18.78 19.68 19.05 18.93C19.32 18.18 19.32 17.55 19.24 17.42C19.16 17.29 18.95 17.21 18.64 17.06C18.34 16.91 16.84 16.17 16.56 16.06C16.28 15.96 16.08 15.91 15.88 16.22C15.68 16.53 15.09 17.21 14.92 17.41C14.74 17.62 14.57 17.64 14.27 17.49C13.97 17.33 13.01 17.02 11.87 15.99C10.99 15.2 10.39 14.22 10.21 13.91C10.04 13.6 10.19 13.43 10.34 13.28C10.48 13.14 10.64 12.91 10.79 12.73C10.94 12.55 10.99 12.42 11.09 12.22C11.19 12.01 11.14 11.83 11.06 11.67C10.99 11.5 10.39 9.99 9.8 9.35Z" fill="currentColor"/>
                                            </svg>
                                            <span class="sr-only">WhatsApp Klien</span>
                                        </a>
                                    @endif
                                    <a
                                        href="{{ route('dashboard.clients.show', $client) }}"
                                        class="inline-flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 transition hover:bg-slate-200 hover:text-slate-800"
                                        title="Lihat Detail"
                                        aria-label="Lihat Detail"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" class="size-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M2.75 12S6.5 6.75 12 6.75S21.25 12 21.25 12S17.5 17.25 12 17.25S2.75 12 2.75 12Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                            <circle cx="12" cy="12" r="2.75" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                        <span class="sr-only">Lihat Detail</span>
                                    </a>
                                    <a
                                        href="{{ route('dashboard.clients.show', $client).'#catatan-baru' }}"
                                        class="inline-flex size-10 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600 transition hover:bg-cyan-100 hover:text-cyan-700"
                                        title="Tambah Catatan"
                                        aria-label="Tambah Catatan"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none" class="size-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M4.75 19.25H8.25L18.25 9.25C18.9404 8.55964 18.9404 7.44036 18.25 6.75V6.75C17.5596 6.05964 16.4404 6.05964 15.75 6.75L5.75 16.75V19.25Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                            <path d="M13.75 8.75L16.25 11.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                        <span class="sr-only">Tambah Catatan</span>
                                    </a>
                                    @if ($notesEnabled)
                                        <form method="POST" action="{{ route('dashboard.clients.follow-up.store', $client) }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="inline-flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 transition hover:bg-amber-100 hover:text-amber-700"
                                                title="Tandai Sudah Follow Up"
                                                aria-label="Tandai Sudah Follow Up"
                                            >
                                                <svg viewBox="0 0 24 24" fill="none" class="size-5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path d="M5 12.5L9.25 16.75L19 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                <span class="sr-only">Tandai Sudah Follow Up</span>
                                            </button>
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
