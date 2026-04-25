<x-layouts::app :title="__('Komisi EPI Channel')">
    @include('epi-channel.partials.page-shell-start')
        <x-ui.section-header
            eyebrow="EPI Channel"
            title="Komisi"
            description="Ringkasan dan daftar komisi referral yang sudah tercatat untuk channel kamu."
        >
            <x-ui.button variant="ghost" size="sm" :href="route('epi-channel.dashboard')">
                Dashboard EPI Channel
            </x-ui.button>
        </x-ui.section-header>

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-ui.stat-card label="Pending" :value="'Rp '.number_format((float) $summary['pending_amount'], 0, ',', '.')" description="Menunggu approval" />
            <x-ui.stat-card label="Approved" :value="'Rp '.number_format((float) $summary['approved_amount'], 0, ',', '.')" description="Siap payout" />
            <x-ui.stat-card label="Paid" :value="'Rp '.number_format((float) $summary['paid_amount'], 0, ',', '.')" description="Sudah dibayar" />
            <x-ui.stat-card label="Rejected" :value="'Rp '.number_format((float) $summary['rejected_amount'], 0, ',', '.')" description="Tidak dibayarkan" />
        </div>

        <div class="mt-6">
            <x-ui.card class="overflow-hidden p-0">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                        <thead class="bg-zinc-50 dark:bg-zinc-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Produk</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Order</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-600 dark:text-zinc-300">Base Amount</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-600 dark:text-zinc-300">Komisi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Timeline</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-800 dark:bg-zinc-950">
                            @forelse ($commissions as $commission)
                                <tr>
                                    <td class="px-4 py-4 text-sm text-zinc-900 dark:text-white">
                                        <div class="font-semibold">{{ $commission->product?->title ?? '-' }}</div>
                                        <div class="mt-1 text-xs text-zinc-500">{{ $commission->created_at?->format('d M Y H:i') }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-zinc-600 dark:text-zinc-300">
                                        {{ $commission->order?->order_number ?? ('#'.$commission->order_id) }}
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm text-zinc-600 dark:text-zinc-300">
                                        Rp {{ number_format((float) $commission->base_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm font-semibold text-zinc-900 dark:text-white">
                                        Rp {{ number_format((float) $commission->commission_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-4 text-sm">
                                        @include('epi-channel.partials.status-badge', ['status' => $commission->status])
                                    </td>
                                    <td class="px-4 py-4 text-xs text-zinc-600 dark:text-zinc-300">
                                        <div>Dibuat: {{ $commission->created_at?->format('d M Y H:i') ?? '-' }}</div>
                                        <div class="mt-1">Approved: {{ $commission->approved_at?->format('d M Y H:i') ?? '-' }}</div>
                                        <div class="mt-1">Paid: {{ $commission->paid_at?->format('d M Y H:i') ?? '-' }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8">
                                        <x-ui.empty-state
                                            title="Belum ada komisi"
                                            description="Komisi akan muncul setelah referral order berhasil diproses."
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
            {{ $commissions->links() }}
        </div>
    @include('epi-channel.partials.page-shell-end')
</x-layouts::app>
