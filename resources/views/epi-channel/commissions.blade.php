<x-layouts::app :title="__('Komisi EPI Channel')">
    @include('epi-channel.partials.page-shell-start')

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <div class="flex size-8 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-sm">
                        <svg viewBox="0 0 24 24" fill="none" class="size-4 text-white" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5" fill="currentColor" fill-opacity=".2"/>
                            <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="text-xs font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400">EPI Channel</span>
                </div>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Komisi</h1>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Ringkasan dan daftar komisi referral yang sudah tercatat untuk channel kamu.</p>
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

        {{-- Summary Cards --}}
        <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-4">

            {{-- Pending --}}
            <div class="relative overflow-hidden rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-orange-50 p-4 shadow-sm shadow-amber-100 dark:border-amber-800/40 dark:from-amber-900/20 dark:to-orange-900/10 dark:shadow-amber-900/20">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/40">
                        <svg viewBox="0 0 24 24" fill="none" class="size-4 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M12 8V12L14.5 14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-900/50 dark:text-amber-400">Pending</span>
                </div>
                <div class="mt-3 text-lg font-bold tracking-tight text-amber-700 dark:text-amber-400 leading-tight">
                    Rp {{ number_format((float) $summary['pending_amount'], 0, ',', '.') }}
                </div>
                <div class="mt-0.5 text-xs text-amber-600/70 dark:text-amber-500">Menunggu approval</div>
            </div>

            {{-- Approved --}}
            <div class="relative overflow-hidden rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-teal-50 p-4 shadow-sm shadow-emerald-100 dark:border-emerald-800/40 dark:from-emerald-900/20 dark:to-teal-900/10 dark:shadow-emerald-900/20">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/40">
                        <svg viewBox="0 0 24 24" fill="none" class="size-4 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5" fill="currentColor" fill-opacity=".12"/>
                            <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-400">Approved</span>
                </div>
                <div class="mt-3 text-lg font-bold tracking-tight text-emerald-700 dark:text-emerald-400 leading-tight">
                    Rp {{ number_format((float) $summary['approved_amount'], 0, ',', '.') }}
                </div>
                <div class="mt-0.5 text-xs text-emerald-600/70 dark:text-emerald-500">Siap payout</div>
            </div>

            {{-- Paid --}}
            <div class="relative overflow-hidden rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50 p-4 shadow-sm shadow-blue-100 dark:border-blue-800/40 dark:from-blue-900/20 dark:to-indigo-900/10 dark:shadow-blue-900/20">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/40">
                        <svg viewBox="0 0 24 24" fill="none" class="size-4 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect x="3.75" y="6.75" width="16.5" height="11.5" rx="2.25" fill="currentColor" fill-opacity=".12" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M3.75 10H20.25" stroke="currentColor" stroke-width="1.5"/>
                            <circle cx="8.5" cy="14" r="1" fill="currentColor"/>
                        </svg>
                    </div>
                    <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/50 dark:text-blue-400">Paid</span>
                </div>
                <div class="mt-3 text-lg font-bold tracking-tight text-blue-700 dark:text-blue-400 leading-tight">
                    Rp {{ number_format((float) $summary['paid_amount'], 0, ',', '.') }}
                </div>
                <div class="mt-0.5 text-xs text-blue-600/70 dark:text-blue-500">Sudah dibayar</div>
            </div>

            {{-- Rejected --}}
            <div class="relative overflow-hidden rounded-2xl border border-rose-100 bg-gradient-to-br from-rose-50 to-pink-50 p-4 shadow-sm shadow-rose-100 dark:border-rose-800/40 dark:from-rose-900/20 dark:to-pink-900/10 dark:shadow-rose-900/20">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-rose-100 dark:bg-rose-900/40">
                        <svg viewBox="0 0 24 24" fill="none" class="size-4 text-rose-600 dark:text-rose-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5" fill="currentColor" fill-opacity=".1"/>
                            <path d="M9.5 9.5L14.5 14.5M14.5 9.5L9.5 14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <span class="rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-700 dark:bg-rose-900/50 dark:text-rose-400">Rejected</span>
                </div>
                <div class="mt-3 text-lg font-bold tracking-tight text-rose-700 dark:text-rose-400 leading-tight">
                    Rp {{ number_format((float) $summary['rejected_amount'], 0, ',', '.') }}
                </div>
                <div class="mt-0.5 text-xs text-rose-600/70 dark:text-rose-500">Tidak dibayarkan</div>
            </div>
        </div>

        {{-- Commission List --}}
        <div class="mt-6">
            @if ($commissions->isEmpty())
                <x-ui.card class="p-10">
                    <x-ui.empty-state
                        title="Belum ada komisi"
                        description="Komisi akan muncul setelah referral order berhasil diproses."
                    />
                </x-ui.card>
            @else
                {{-- Desktop table (md+) --}}
                <div class="hidden md:block">
                    <x-ui.card class="overflow-hidden p-0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b border-zinc-100 bg-zinc-50/80 dark:border-zinc-800 dark:bg-zinc-900/60">
                                        <th class="px-5 py-3.5 text-left">
                                            <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                                                <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path d="M4.75 5.75A1 1 0 0 1 5.75 4.75h12.5a1 1 0 0 1 1 1v12.5a1 1 0 0 1-1 1H5.75a1 1 0 0 1-1-1V5.75Z" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M8.5 10.5h7M8.5 13.5h4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                </svg>
                                                Produk
                                            </span>
                                        </th>
                                        <th class="px-5 py-3.5 text-left">
                                            <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                                                <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path d="M5.5 7.5H18.5L17 17.5H7L5.5 7.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                                    <path d="M5.5 7.5L4.5 4.5H2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Order
                                            </span>
                                        </th>
                                        <th class="px-5 py-3.5 text-right">
                                            <span class="flex items-center justify-end gap-1.5 text-xs font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                                                <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <rect x="3.75" y="6.75" width="16.5" height="11.5" rx="2.25" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M3.75 10H20.25" stroke="currentColor" stroke-width="1.5"/>
                                                </svg>
                                                Base Amount
                                            </span>
                                        </th>
                                        <th class="px-5 py-3.5 text-right">
                                            <span class="flex items-center justify-end gap-1.5 text-xs font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                                                <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                Komisi
                                            </span>
                                        </th>
                                        <th class="px-5 py-3.5 text-left">
                                            <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                                                <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M12 8V12L14.5 14.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                </svg>
                                                Status
                                            </span>
                                        </th>
                                        <th class="px-5 py-3.5 text-left">
                                            <span class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
                                                <svg viewBox="0 0 24 24" fill="none" class="size-3.5" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <rect x="4.75" y="4.75" width="14.5" height="15.5" rx="2" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M8.75 4.75V3.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                    <path d="M15.25 4.75V3.25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                    <path d="M4.75 9.25H19.25" stroke="currentColor" stroke-width="1.5"/>
                                                </svg>
                                                Timeline
                                            </span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                    @foreach ($commissions as $commission)
                                        @php
                                            $statusVal = $commission->status?->value ?? $commission->status ?? '';
                                            $rowColor = match($statusVal) {
                                                'approved' => 'hover:bg-emerald-50/50 dark:hover:bg-emerald-900/10',
                                                'paid' => 'hover:bg-blue-50/50 dark:hover:bg-blue-900/10',
                                                'rejected' => 'hover:bg-rose-50/50 dark:hover:bg-rose-900/10',
                                                default => 'hover:bg-amber-50/40 dark:hover:bg-amber-900/10',
                                            };
                                            $commissionColor = match($statusVal) {
                                                'approved' => 'text-emerald-600 dark:text-emerald-400',
                                                'paid' => 'text-blue-600 dark:text-blue-400',
                                                'rejected' => 'text-rose-500 line-through dark:text-rose-500',
                                                default => 'text-amber-600 dark:text-amber-400',
                                            };
                                        @endphp
                                        <tr class="bg-white transition-colors duration-150 dark:bg-zinc-950 {{ $rowColor }}">
                                            <td class="px-5 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                                                        <svg viewBox="0 0 24 24" fill="none" class="size-4 text-zinc-500 dark:text-zinc-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                            <path d="M4.75 5.75A1 1 0 0 1 5.75 4.75h12.5a1 1 0 0 1 1 1v12.5a1 1 0 0 1-1 1H5.75a1 1 0 0 1-1-1V5.75Z" stroke="currentColor" stroke-width="1.5"/>
                                                            <path d="M8.5 10.5h7M8.5 13.5h4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                        </svg>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <div class="font-semibold text-zinc-900 dark:text-white">{{ $commission->product?->title ?? '-' }}</div>
                                                        <div class="mt-0.5 text-xs text-zinc-400">{{ $commission->created_at?->format('d M Y') }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4">
                                                <span class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-mono font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                                    {{ $commission->order?->order_number ?? ('#'.$commission->order_id) }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 text-right text-sm text-zinc-500 dark:text-zinc-400">
                                                Rp {{ number_format((float) $commission->base_amount, 0, ',', '.') }}
                                            </td>
                                            <td class="px-5 py-4 text-right">
                                                <span class="text-sm font-bold {{ $commissionColor }}">
                                                    Rp {{ number_format((float) $commission->commission_amount, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4">
                                                @include('epi-channel.partials.commission-status-badge', ['status' => $commission->status])
                                            </td>
                                            <td class="px-5 py-4">
                                                <div class="space-y-1.5">
                                                    <div class="flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                                                        <svg viewBox="0 0 24 24" fill="none" class="size-3 shrink-0 text-zinc-400" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                            <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.4"/>
                                                            <path d="M12 8V12L14.5 14.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                                                        </svg>
                                                        {{ $commission->created_at?->format('d M Y H:i') ?? '-' }}
                                                    </div>
                                                    @if ($commission->approved_at)
                                                        <div class="flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400">
                                                            <svg viewBox="0 0 24 24" fill="none" class="size-3 shrink-0" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                                <path d="M5 12L10 17L19 7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                            {{ $commission->approved_at->format('d M Y H:i') }}
                                                        </div>
                                                    @endif
                                                    @if ($commission->paid_at)
                                                        <div class="flex items-center gap-1.5 text-xs text-blue-600 dark:text-blue-400">
                                                            <svg viewBox="0 0 24 24" fill="none" class="size-3 shrink-0" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                                <rect x="3.75" y="6.75" width="16.5" height="11.5" rx="2.25" stroke="currentColor" stroke-width="1.4"/>
                                                                <path d="M3.75 10H20.25" stroke="currentColor" stroke-width="1.4"/>
                                                                <circle cx="8.5" cy="14" r="1" fill="currentColor"/>
                                                            </svg>
                                                            {{ $commission->paid_at->format('d M Y H:i') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-ui.card>
                </div>

                {{-- Mobile card list (< md) --}}
                <div class="flex flex-col gap-3 md:hidden">
                    @foreach ($commissions as $commission)
                        @php
                            $statusVal = $commission->status?->value ?? $commission->status ?? '';
                            $cardAccent = match($statusVal) {
                                'approved' => 'from-emerald-500 to-teal-500',
                                'paid' => 'from-blue-500 to-indigo-500',
                                'rejected' => 'from-rose-500 to-pink-500',
                                default => 'from-amber-500 to-orange-400',
                            };
                            $commissionColor = match($statusVal) {
                                'approved' => 'text-emerald-600 dark:text-emerald-400',
                                'paid' => 'text-blue-600 dark:text-blue-400',
                                'rejected' => 'text-rose-500 line-through dark:text-rose-500',
                                default => 'text-amber-600 dark:text-amber-400',
                            };
                        @endphp

                        <div class="overflow-hidden rounded-2xl border border-zinc-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                            {{-- Color bar top --}}
                            <div class="h-1 w-full bg-gradient-to-r {{ $cardAccent }}"></div>

                            <div class="p-4">
                                {{-- Product + Status row --}}
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $cardAccent }}">
                                            <svg viewBox="0 0 24 24" fill="none" class="size-4 text-white" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                <path d="M4.75 5.75A1 1 0 0 1 5.75 4.75h12.5a1 1 0 0 1 1 1v12.5a1 1 0 0 1-1 1H5.75a1 1 0 0 1-1-1V5.75Z" stroke="currentColor" stroke-width="1.5"/>
                                                <path d="M8.5 10.5h7M8.5 13.5h4.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-zinc-900 dark:text-white leading-snug">{{ $commission->product?->title ?? '-' }}</div>
                                            <div class="mt-0.5 text-xs text-zinc-400">{{ $commission->created_at?->format('d M Y H:i') }}</div>
                                        </div>
                                    </div>
                                    @include('epi-channel.partials.commission-status-badge', ['status' => $commission->status])
                                </div>

                                {{-- Amounts --}}
                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    <div class="rounded-xl bg-zinc-50 px-3 py-2.5 dark:bg-zinc-800">
                                        <div class="text-xs text-zinc-400 dark:text-zinc-500">Base Amount</div>
                                        <div class="mt-0.5 text-sm font-semibold text-zinc-700 dark:text-zinc-300">
                                            Rp {{ number_format((float) $commission->base_amount, 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <div class="rounded-xl bg-zinc-50 px-3 py-2.5 dark:bg-zinc-800">
                                        <div class="text-xs text-zinc-400 dark:text-zinc-500">Komisi</div>
                                        <div class="mt-0.5 text-sm font-bold {{ $commissionColor }}">
                                            Rp {{ number_format((float) $commission->commission_amount, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Order + Timeline footer --}}
                                <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-100 px-2.5 py-1 text-xs font-mono font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                        <svg viewBox="0 0 24 24" fill="none" class="size-3" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M5.5 7.5H18.5L17 17.5H7L5.5 7.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                            <path d="M5.5 7.5L4.5 4.5H2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        {{ $commission->order?->order_number ?? ('#'.$commission->order_id) }}
                                    </span>
                                    <div class="flex items-center gap-2">
                                        @if ($commission->approved_at)
                                            <span class="flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400">
                                                <svg viewBox="0 0 24 24" fill="none" class="size-3" xmlns="http://www.w3.org/2000/svg"><path d="M5 12L10 17L19 7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                {{ $commission->approved_at->format('d M') }}
                                            </span>
                                        @endif
                                        @if ($commission->paid_at)
                                            <span class="flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400">
                                                <svg viewBox="0 0 24 24" fill="none" class="size-3" xmlns="http://www.w3.org/2000/svg"><rect x="3.75" y="6.75" width="16.5" height="11.5" rx="2.25" stroke="currentColor" stroke-width="1.4"/><path d="M3.75 10H20.25" stroke="currentColor" stroke-width="1.4"/><circle cx="8.5" cy="14" r="1" fill="currentColor"/></svg>
                                                {{ $commission->paid_at->format('d M') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-6">
            {{ $commissions->links() }}
        </div>

    @include('epi-channel.partials.page-shell-end')
</x-layouts::app>
