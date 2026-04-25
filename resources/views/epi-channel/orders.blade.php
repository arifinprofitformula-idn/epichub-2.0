<x-layouts::app :title="__('Referral Order EPI Channel')">
    @include('epi-channel.partials.page-shell-start')
        <x-ui.section-header
            eyebrow="EPI Channel"
            title="Referral Order"
            description="Order yang teratribusikan ke referral milik channel kamu."
        >
            <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.dashboard')">
                Dashboard EPI Channel
            </x-ui.button>
        </x-ui.section-header>

        <div class="mt-6">
            <x-ui.card class="overflow-hidden p-0">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                        <thead class="bg-zinc-50 dark:bg-zinc-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Order Number</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Buyer</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-600 dark:text-zinc-300">Total Order</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Attributed At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-800 dark:bg-zinc-950">
                            @forelse ($orders as $refOrder)
                                @php
                                    $buyerLabel = $refOrder->buyer?->name
                                        ? \Illuminate\Support\Str::mask($refOrder->buyer->name, '*', 2)
                                        : \Illuminate\Support\Str::mask($refOrder->order?->customer_email ?? '-', '*', 2);
                                @endphp
                                <tr>
                                    <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">
                                        <div class="font-semibold">{{ $refOrder->order?->order_number ?? ('#'.$refOrder->order_id) }}</div>
                                        <div class="mt-1 text-xs text-zinc-500">{{ $refOrder->order?->items->first()?->product_title ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $buyerLabel }}</td>
                                    <td class="px-4 py-4 text-right text-sm font-semibold text-zinc-900 dark:text-white">
                                        Rp {{ number_format((float) ($refOrder->order?->total_amount ?? 0), 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 text-sm">
                                        @include('epi-channel.partials.status-badge', ['status' => $refOrder->status])
                                    </td>
                                    <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $refOrder->attributed_at?->format('d M Y H:i') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8">
                                        <x-ui.empty-state
                                            title="Belum ada referral order"
                                            description="Order referral akan muncul setelah checkout berhasil menggunakan referensimu."
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
            {{ $orders->links() }}
        </div>
    @include('epi-channel.partials.page-shell-end')
</x-layouts::app>
