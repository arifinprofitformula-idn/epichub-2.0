<x-layouts::app :title="__('Payout EPI Channel')">
    @include('epi-channel.partials.page-shell-start')
        <x-ui.section-header
            eyebrow="EPI Channel"
            title="Payout"
            description="Riwayat payout komisi milik channel kamu."
        >
            <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.dashboard')">
                Dashboard EPI Channel
            </x-ui.button>
        </x-ui.section-header>

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card label="Total Paid" :value="'Rp '.number_format((float) $summary['paid_amount'], 0, ',', '.')" description="Akumulasi payout paid" />
            <x-ui.stat-card label="Processing" :value="'Rp '.number_format((float) $summary['processing_amount'], 0, ',', '.')" description="Sedang diproses" />
        </div>

        <div class="mt-6">
            <x-ui.card class="overflow-hidden p-0">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                        <thead class="bg-zinc-50 dark:bg-zinc-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Payout Number</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-600 dark:text-zinc-300">Total Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Paid At</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-800 dark:bg-zinc-950">
                            @forelse ($payouts as $payout)
                                <tr>
                                    <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">
                                        <div class="font-semibold">{{ $payout->payout_number }}</div>
                                        <div class="mt-1 text-xs text-zinc-500">{{ $payout->created_at?->format('d M Y H:i') }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm font-semibold text-zinc-900 dark:text-white">
                                        Rp {{ number_format((float) $payout->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 text-sm">
                                        @include('epi-channel.partials.status-badge', ['status' => $payout->status])
                                    </td>
                                    <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-300">
                                        {{ $payout->paid_at?->format('d M Y H:i') ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-300">
                                        {{ $payout->notes ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8">
                                        <x-ui.empty-state
                                            title="Belum ada payout"
                                            description="Payout dibuat dan dibayarkan manual oleh admin."
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
            {{ $payouts->links() }}
        </div>
    @include('epi-channel.partials.page-shell-end')
</x-layouts::app>
