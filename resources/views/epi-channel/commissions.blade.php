<x-layouts::app :title="__('Komisi EPI Channel')">
    @include('epi-channel.partials.page-shell-start')

    @php
        $tabs = [
            'summary' => 'Ringkasan',
            'v2' => 'Daftar Komisi',
            'payout' => 'Payout',
        ];

        $badgeClasses = [
            'success' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
            'info' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
            'warning' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
            'danger' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
            'gray' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
        ];

        $summaryCards = [
            [
                'label' => 'Komisi Total',
                'value' => $summary['v2_total'],
                'tone' => 'from-emerald-50 to-teal-50 border-emerald-100',
                'icon_color' => 'text-emerald-600',
            ],
            [
                'label' => 'Komisi Pending',
                'value' => $summary['pending_total'],
                'tone' => 'from-amber-50 to-yellow-50 border-amber-100',
                'icon_color' => 'text-amber-600',
            ],
            [
                'label' => 'Komisi Paid',
                'value' => $summary['paid_total'],
                'tone' => 'from-sky-50 to-blue-50 border-sky-100',
                'icon_color' => 'text-sky-600',
            ],
        ];
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <div class="flex size-8 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-sm">
                    <svg viewBox="0 0 24 24" fill="none" class="size-4 text-white" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5" fill="currentColor" fill-opacity=".18"/>
                        <path d="M7.5 14.25H16.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                        <path d="M9.25 10.25H14.75" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                </div>
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">EPI Channel</span>
            </div>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Komisi Saya</h1>
            <p class="mt-1 max-w-3xl text-sm text-zinc-500 dark:text-zinc-400">
                Ringkasan komisi dari aktivitas referral Anda.
            </p>
        </div>
        <a href="{{ route('epi-channel.dashboard') }}"
           class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-600 shadow-sm transition-all duration-200 hover:border-zinc-300 hover:bg-zinc-50 hover:shadow active:scale-[0.98] dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
            <svg viewBox="0 0 24 24" fill="none" class="size-4" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M9.25 19.25L4.75 12L9.25 4.75" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M4.75 12H19.25" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
            Dashboard
        </a>
    </div>

    <div class="mt-6 grid gap-3 md:grid-cols-3">
        @foreach ($summaryCards as $card)
            <div class="rounded-2xl border bg-gradient-to-br {{ $card['tone'] }} p-5 shadow-sm dark:border-zinc-800 dark:from-zinc-900 dark:to-zinc-950">
                <div class="text-xs font-bold uppercase tracking-[0.16em] {{ $card['icon_color'] }} dark:text-zinc-400">{{ $card['label'] }}</div>
                <div class="mt-3 text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                    Rp {{ number_format((float) $card['value'], 0, ',', '.') }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
        @foreach ($tabs as $tabKey => $tabLabel)
            @php
                $isActive = $activeTab === $tabKey;
            @endphp
            <a
                href="{{ route('epi-channel.commissions', ['tab' => $tabKey]) }}"
                class="{{ $isActive ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-white text-zinc-600 hover:bg-zinc-50 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800' }} inline-flex items-center rounded-full border border-zinc-200 px-4 py-2 text-sm font-semibold transition dark:border-zinc-700"
            >
                {{ $tabLabel }}
            </a>
        @endforeach
    </div>

    @if ($activeTab === 'summary')
        <div class="mt-6 rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Aktivitas Komisi Terbaru</h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Komisi dari order referral yang sudah diproses.</p>
            </div>

            @if ($recentLedgerRows->isEmpty())
                <div class="p-8">
                    <x-ui.empty-state
                        title="Belum ada data komisi"
                        description="Komisi akan muncul di sini setelah order referral selesai diproses."
                    />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-100 dark:divide-zinc-800">
                        <thead class="bg-zinc-50/90 dark:bg-zinc-950">
                            <tr class="text-left text-xs font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Produk</th>
                                <th class="px-5 py-3">Tipe</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($recentLedgerRows as $row)
                                <tr class="bg-white dark:bg-zinc-900">
                                    <td class="px-5 py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $row['date_label'] }}</td>
                                    <td class="px-5 py-4 text-sm font-medium text-zinc-900 dark:text-white">{{ $row['product'] }}</td>
                                    <td class="px-5 py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $row['type'] }}</td>
                                    <td class="px-5 py-4">
                                        <span class="{{ $badgeClasses[$row['status_color']] ?? $badgeClasses['gray'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold">
                                            {{ $row['status_label'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right text-sm font-semibold text-zinc-900 dark:text-white">
                                        Rp {{ number_format((float) $row['amount'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    @if ($activeTab === 'v2')
        <div class="mt-6 rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            @if ($v2Commissions->isEmpty())
                <div class="p-8">
                    <x-ui.empty-state
                        title="Belum ada komisi"
                        description="Komisi aktif akan muncul setelah order referral selesai diproses."
                    />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-100 dark:divide-zinc-800">
                        <thead class="bg-zinc-50/90 dark:bg-zinc-950">
                            <tr class="text-left text-xs font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Produk</th>
                                <th class="px-5 py-3">Tipe</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($v2Commissions as $commission)
                                @php
                                    $statusColor = $commission->status?->getColor() ?? 'gray';
                                    $date = $commission->paid_at ?? $commission->approved_at ?? $commission->created_at;
                                @endphp
                                <tr class="bg-white dark:bg-zinc-900">
                                    <td class="px-5 py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $date?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="px-5 py-4 text-sm font-medium text-zinc-900 dark:text-white">{{ $commission->product?->title ?? '-' }}</td>
                                    <td class="px-5 py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $commission->commission_type?->label() ?? '-' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="{{ $badgeClasses[$statusColor] ?? $badgeClasses['gray'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold">
                                            {{ $commission->status?->label() ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right text-sm font-semibold text-zinc-900 dark:text-white">
                                        Rp {{ number_format((float) $commission->commission_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    {{ $v2Commissions->links() }}
                </div>
            @endif
        </div>
    @endif

    @if ($activeTab === 'payout')
        <div class="mt-6 rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            @if ($payouts->isEmpty())
                <div class="p-8">
                    <x-ui.empty-state
                        title="Belum ada payout"
                        description="Payout komisi yang sudah dibuat admin akan tampil pada tab ini."
                    />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-100 dark:divide-zinc-800">
                        <thead class="bg-zinc-50/90 dark:bg-zinc-950">
                            <tr class="text-left text-xs font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                                <th class="px-5 py-3">Payout</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Jumlah Komisi</th>
                                <th class="px-5 py-3">Dibuat</th>
                                <th class="px-5 py-3">Dibayarkan</th>
                                <th class="px-5 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach ($payouts as $payout)
                                @php
                                    $statusColor = $payout->status?->getColor() ?? 'gray';
                                @endphp
                                <tr class="bg-white dark:bg-zinc-900">
                                    <td class="px-5 py-4 text-sm font-semibold text-zinc-900 dark:text-white">{{ $payout->payout_number }}</td>
                                    <td class="px-5 py-4">
                                        <span class="{{ $badgeClasses[$statusColor] ?? $badgeClasses['gray'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold">
                                            {{ $payout->status?->label() ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $payout->commissions_count }}</td>
                                    <td class="px-5 py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $payout->created_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="px-5 py-4 text-sm text-zinc-600 dark:text-zinc-300">{{ $payout->paid_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="px-5 py-4 text-right text-sm font-semibold text-zinc-900 dark:text-white">
                                        Rp {{ number_format((float) $payout->total_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-zinc-100 px-5 py-4 dark:border-zinc-800">
                    {{ $payouts->links() }}
                </div>
            @endif
        </div>
    @endif

    @include('epi-channel.partials.page-shell-end')
</x-layouts::app>
