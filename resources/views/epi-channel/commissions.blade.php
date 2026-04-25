<x-layouts::public title="Komisi">
    <section class="mx-auto max-w-[var(--container-5xl)] px-4 py-10">
        <x-ui.section-header
            title="Komisi"
            description="Daftar komisi dari order referral kamu."
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
            <div class="mt-6">
                <x-ui.card class="p-0 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                            <thead class="bg-zinc-50 dark:bg-zinc-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Produk</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Order</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-600 dark:text-zinc-300">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-600 dark:text-zinc-300">Komisi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-800 dark:bg-zinc-950">
                                @forelse ($commissions as $commission)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                            {{ $commission->created_at?->format('d M Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-white">
                                            {{ $commission->product?->title ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                            {{ $commission->order?->order_number ?? ('#'.$commission->order_id) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @php($status = $commission->status?->value ?? '')
                                            @if ($status === 'paid')
                                                <x-ui.badge variant="success">Paid</x-ui.badge>
                                            @elseif ($status === 'approved')
                                                <x-ui.badge variant="primary">Approved</x-ui.badge>
                                            @elseif ($status === 'rejected')
                                                <x-ui.badge variant="danger">Rejected</x-ui.badge>
                                            @else
                                                <x-ui.badge variant="warning">Pending</x-ui.badge>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-semibold text-zinc-900 dark:text-white">
                                            Rp {{ number_format((float) $commission->commission_amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8">
                                            <x-ui.empty-state
                                                title="Belum ada komisi"
                                                description="Komisi akan muncul setelah order referral dibayar dan diverifikasi."
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
        @endif
    </section>
</x-layouts::public>

