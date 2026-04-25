<x-layouts::app :title="__('Kunjungan EPI Channel')">
    @include('epi-channel.partials.page-shell-start')
        <x-ui.section-header
            eyebrow="EPI Channel"
            title="Kunjungan"
            description="Referral visit yang tercatat untuk link promosi milik channel kamu."
        >
            <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.dashboard')">
                Dashboard EPI Channel
            </x-ui.button>
        </x-ui.section-header>

        <div class="mt-6">
            <x-ui.card class="p-6">
                <form method="GET" class="grid gap-4 md:grid-cols-4">
                    <div>
                        <label for="product_id" class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Produk</label>
                        <select id="product_id" name="product_id" class="mt-2 w-full rounded-[var(--radius-lg)] border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                            <option value="">Semua produk</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected((string) ($filters['product_id'] ?? '') === (string) $product->id)>{{ $product->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date_from" class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Dari tanggal</label>
                        <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}" class="mt-2 w-full rounded-[var(--radius-lg)] border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none dark:border-zinc-800 dark:bg-zinc-950 dark:text-white" />
                    </div>
                    <div>
                        <label for="date_to" class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-400">Sampai tanggal</label>
                        <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}" class="mt-2 w-full rounded-[var(--radius-lg)] border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none dark:border-zinc-800 dark:bg-zinc-950 dark:text-white" />
                    </div>
                    <div class="flex items-end gap-2">
                        <x-ui.button variant="primary" size="sm" type="submit">Filter</x-ui.button>
                        <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.visits')">Reset</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>

        <div class="mt-6">
            <x-ui.card class="overflow-hidden p-0">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                        <thead class="bg-zinc-50 dark:bg-zinc-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Clicked At</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Landing URL</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Source URL</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Visitor / Session</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Device / User Agent</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-800 dark:bg-zinc-950">
                            @forelse ($visits as $visit)
                                <tr>
                                    <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $visit->clicked_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm font-semibold text-zinc-900 dark:text-white">{{ $visit->product?->title ?? '-' }}</td>
                                    <td class="px-4 py-4 text-xs text-zinc-600 dark:text-zinc-300">{{ \Illuminate\Support\Str::limit($visit->landing_url ?? '-', 70) }}</td>
                                    <td class="px-4 py-4 text-xs text-zinc-600 dark:text-zinc-300">{{ \Illuminate\Support\Str::limit($visit->source_url ?? '-', 70) }}</td>
                                    <td class="px-4 py-4 text-xs text-zinc-600 dark:text-zinc-300">
                                        <div>{{ $visit->visitor_id ?: '-' }}</div>
                                        <div class="mt-1">{{ $visit->session_id ?: '-' }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-xs text-zinc-600 dark:text-zinc-300">{{ \Illuminate\Support\Str::limit($visit->user_agent ?? '-', 70) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8">
                                        <x-ui.empty-state
                                            title="Belum ada kunjungan"
                                            description="Data kunjungan akan muncul setelah link referral kamu mulai dikunjungi."
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>

        <div class="mt-6">
            {{ $visits->links() }}
        </div>
    @include('epi-channel.partials.page-shell-end')
</x-layouts::app>
