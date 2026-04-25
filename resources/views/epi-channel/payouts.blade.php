<x-layouts::public title="Payout">
    <section class="mx-auto max-w-[var(--container-5xl)] px-4 py-10">
        <x-ui.section-header
            title="Payout"
            description="Riwayat payout komisi kamu."
        >
            <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.index')">
                Dashboard penghasilan
            </x-ui.button>
        </x-ui.section-header>

        @if (! $channel || ! $channel->isActive())
            <div class="mt-6">
                <x-ui.empty-state
                    title="EPI Channel belum aktif"
                    description="Aktivasi dilakukan melalui OMS atau admin."
                />
            </div>
        @else
            <div class="mt-6 grid gap-4 md:grid-cols-3">
                <x-ui.stat-card label="Total paid" :value="'Rp '.number_format((float) $summary['paid_amount'], 0, ',', '.')" description="Akumulasi payout" />
            </div>

            <div class="mt-6">
                <x-ui.card class="p-0 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                            <thead class="bg-zinc-50 dark:bg-zinc-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Payout</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-600 dark:text-zinc-300">Total</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-600 dark:text-zinc-300">Komisi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-800 dark:bg-zinc-950">
                                @forelse ($payouts as $payout)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-white">
                                            <div class="font-semibold">{{ $payout->payout_number }}</div>
                                            <div class="mt-0.5 text-xs text-zinc-600 dark:text-zinc-300">{{ $payout->created_at?->format('d M Y') }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @php($status = $payout->status?->value ?? '')
                                            @if ($status === 'paid')
                                                <x-ui.badge variant="success">Paid</x-ui.badge>
                                            @elseif ($status === 'processing')
                                                <x-ui.badge variant="primary">Processing</x-ui.badge>
                                            @elseif ($status === 'cancelled')
                                                <x-ui.badge variant="danger">Cancelled</x-ui.badge>
                                            @else
                                                <x-ui.badge variant="warning">Draft</x-ui.badge>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-semibold text-zinc-900 dark:text-white">
                                            Rp {{ number_format((float) $payout->total_amount, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm text-zinc-600 dark:text-zinc-300">
                                            {{ $payout->commissions_count }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8">
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
        @endif
    </section>
</x-layouts::public>

