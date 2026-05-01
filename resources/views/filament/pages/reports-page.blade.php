<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ── Filter Periode ─────────────────────────────────────────────── --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-4 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:flex-wrap">

                    {{-- Tipe Periode --}}
                    <div class="min-w-[160px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Periode</label>
                        <select wire:model.live="period"
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="monthly">Bulanan</option>
                            <option value="quarterly">Kuartal</option>
                            <option value="semester">Semester</option>
                            <option value="yearly">Tahunan</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>

                    {{-- Tahun --}}
                    @if(in_array($this->period, ['monthly','quarterly','semester','yearly']))
                    <div class="min-w-[110px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Tahun</label>
                        <select wire:model.live="selectedYear"
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            @foreach($availableYears as $y => $label)
                                <option value="{{ $y }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Bulan --}}
                    @if($this->period === 'monthly')
                    <div class="min-w-[140px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Bulan</label>
                        <select wire:model.live="selectedMonth"
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Kuartal --}}
                    @if($this->period === 'quarterly')
                    <div class="min-w-[120px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Kuartal</label>
                        <select wire:model.live="selectedQuarter"
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="1">Q1 (Jan–Mar)</option>
                            <option value="2">Q2 (Apr–Jun)</option>
                            <option value="3">Q3 (Jul–Sep)</option>
                            <option value="4">Q4 (Okt–Des)</option>
                        </select>
                    </div>
                    @endif

                    {{-- Semester --}}
                    @if($this->period === 'semester')
                    <div class="min-w-[150px]">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Semester</label>
                        <select wire:model.live="selectedSemester"
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="1">Semester 1 (Jan–Jun)</option>
                            <option value="2">Semester 2 (Jul–Des)</option>
                        </select>
                    </div>
                    @endif

                    {{-- Custom Range --}}
                    @if($this->period === 'custom')
                    <div class="flex gap-3 flex-wrap">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Dari Tanggal</label>
                            <input type="date" wire:model.live="customFrom"
                                class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Sampai Tanggal</label>
                            <input type="date" wire:model.live="customTo"
                                class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>
                    @endif

                    {{-- Reset --}}
                    <div class="pt-0 sm:pt-5">
                        <button wire:click="$set('period', 'monthly'); $set('selectedMonth', {{ now()->month }}); $set('selectedYear', {{ now()->year }})"
                            class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600 transition">
                            <x-heroicon-o-arrow-path class="h-4 w-4" />
                            Reset Filter
                        </button>
                    </div>

                    {{-- Label Periode Aktif --}}
                    <div class="ml-auto flex items-center">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-50 px-3 py-1.5 text-sm font-semibold text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">
                            <x-heroicon-o-calendar-days class="h-4 w-4" />
                            {{ $periodLabel }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Summary Cards ───────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Omzet Kotor --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
                <div class="h-1.5 bg-slate-600"></div>
                <div class="p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Omzet Kotor</p>
                            <p class="mt-2 text-2xl font-bold text-slate-800 dark:text-white leading-tight">
                                Rp {{ number_format($omzetKotor, 0, ',', '.') }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Total penjualan paid periode ini</p>
                        </div>
                        <div class="rounded-xl bg-slate-100 p-2.5 dark:bg-slate-800">
                            <x-heroicon-o-banknotes class="h-6 w-6 text-slate-600 dark:text-slate-300" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Laba Bersih Platform --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
                <div class="h-1.5 bg-blue-500"></div>
                <div class="p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Laba Bersih Platform</p>
                            <p class="mt-2 text-2xl font-bold {{ $labaBersih >= 0 ? 'text-blue-700 dark:text-blue-300' : 'text-red-600 dark:text-red-400' }} leading-tight">
                                Rp {{ number_format($labaBersih, 0, ',', '.') }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Omzet − Affiliate − Kontributor</p>
                        </div>
                        <div class="rounded-xl bg-blue-50 p-2.5 dark:bg-blue-900/30">
                            <x-heroicon-o-chart-bar-square class="h-6 w-6 text-blue-500 dark:text-blue-300" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Komisi Affiliate --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
                <div class="h-1.5 bg-amber-500"></div>
                <div class="p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Komisi Affiliate</p>
                            <p class="mt-2 text-2xl font-bold text-amber-700 dark:text-amber-300 leading-tight">
                                Rp {{ number_format($affiliateTotal, 0, ',', '.') }}
                            </p>
                            <div class="mt-2 space-y-0.5">
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                        Paid: <span class="font-medium text-gray-700 dark:text-gray-200">Rp {{ number_format($affiliatePaid, 0, ',', '.') }}</span>
                                    </span>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400 inline-block"></span>
                                        Unpaid: <span class="font-medium text-gray-700 dark:text-gray-200">Rp {{ number_format($affiliateUnpaid, 0, ',', '.') }}</span>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="rounded-xl bg-amber-50 p-2.5 dark:bg-amber-900/30">
                            <x-heroicon-o-users class="h-6 w-6 text-amber-500 dark:text-amber-300" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Komisi Kontributor --}}
            <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
                <div class="h-1.5 bg-violet-500"></div>
                <div class="p-5">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Komisi Kontributor</p>
                            <p class="mt-2 text-2xl font-bold text-violet-700 dark:text-violet-300 leading-tight">
                                Rp {{ number_format($contributorTotal, 0, ',', '.') }}
                            </p>
                            <div class="mt-2 space-y-0.5">
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                        Paid: <span class="font-medium text-gray-700 dark:text-gray-200">Rp {{ number_format($contributorPaid, 0, ',', '.') }}</span>
                                    </span>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="h-1.5 w-1.5 rounded-full bg-violet-400 inline-block"></span>
                                        Unpaid: <span class="font-medium text-gray-700 dark:text-gray-200">Rp {{ number_format($contributorUnpaid, 0, ',', '.') }}</span>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="rounded-xl bg-violet-50 p-2.5 dark:bg-violet-900/30">
                            <x-heroicon-o-academic-cap class="h-6 w-6 text-violet-500 dark:text-violet-300" />
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ── Efektivitas Produk ──────────────────────────────────────────── --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header border-b border-gray-200 dark:border-white/10 px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-slate-100 p-2 dark:bg-slate-800">
                        <x-heroicon-o-shopping-bag class="h-5 w-5 text-slate-600 dark:text-slate-300" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Efektivitas Produk</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Performa penjualan dan komisi per produk</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-white/10 bg-gray-50 dark:bg-gray-800/50">
                            <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Nama Produk</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Sales</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Omzet Kotor</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Affiliate</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Kontributor</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Laba Bersih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        @forelse($efektivitasProduk as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                            <td class="px-5 py-3.5 font-medium text-gray-800 dark:text-gray-100">
                                {{ $row['title'] }}
                            </td>
                            <td class="px-5 py-3.5 text-right text-gray-600 dark:text-gray-300">
                                {{ number_format($row['sales']) }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-medium text-slate-700 dark:text-slate-200">
                                Rp {{ number_format($row['omzet_kotor'], 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-right text-amber-600 dark:text-amber-400">
                                @if($row['affiliate'] > 0)
                                    − Rp {{ number_format($row['affiliate'], 0, ',', '.') }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right text-violet-600 dark:text-violet-400">
                                @if($row['contributor'] > 0)
                                    − Rp {{ number_format($row['contributor'], 0, ',', '.') }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold {{ $row['laba_bersih'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400' }}">
                                Rp {{ number_format($row['laba_bersih'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                Tidak ada data penjualan pada periode ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($efektivitasProduk->isNotEmpty())
                    <tfoot class="border-t-2 border-gray-200 dark:border-white/20 bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <td class="px-5 py-3 font-bold text-gray-800 dark:text-gray-100">Total</td>
                            <td class="px-5 py-3 text-right font-bold text-gray-800 dark:text-gray-100">
                                {{ number_format($efektivitasProduk->sum('sales')) }}
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-slate-700 dark:text-slate-200">
                                Rp {{ number_format($efektivitasProduk->sum('omzet_kotor'), 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-amber-600 dark:text-amber-400">
                                − Rp {{ number_format($efektivitasProduk->sum('affiliate'), 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-violet-600 dark:text-violet-400">
                                − Rp {{ number_format($efektivitasProduk->sum('contributor'), 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3 text-right font-bold {{ $efektivitasProduk->sum('laba_bersih') >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500 dark:text-red-400' }}">
                                Rp {{ number_format($efektivitasProduk->sum('laba_bersih'), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- ── Aktivitas Keuangan Terbaru ──────────────────────────────────── --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header border-b border-gray-200 dark:border-white/10 px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-blue-50 p-2 dark:bg-blue-900/30">
                        <x-heroicon-o-clock class="h-5 w-5 text-blue-500 dark:text-blue-300" />
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Aktivitas Keuangan Terbaru</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">50 transaksi terakhir pada periode terpilih</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-white/10 bg-gray-50 dark:bg-gray-800/50">
                            <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Waktu</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Aktivitas & Ref</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Pihak Terkait</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                        @forelse($aktivitasKeuangan as $act)
                        @php
                            $badgeClasses = match($act['tipe']) {
                                'penjualan' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                                'komisi_affiliate' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                'komisi_kontributor' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300',
                                'payout_affiliate', 'payout_kontributor' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                            };
                            $nominalClass = $act['arah'] === 'masuk'
                                ? 'text-emerald-600 dark:text-emerald-400 font-semibold'
                                : 'text-red-500 dark:text-red-400 font-semibold';
                            $prefix = $act['arah'] === 'masuk' ? '+ Rp' : '− Rp';
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                            <td class="px-5 py-3.5 whitespace-nowrap text-gray-500 dark:text-gray-400 text-xs">
                                {{ $act['waktu']?->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $badgeClasses }}">
                                        {{ $act['label'] }}
                                    </span>
                                    <span class="text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $act['ref'] }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-gray-700 dark:text-gray-200">
                                {{ $act['pihak'] }}
                            </td>
                            <td class="px-5 py-3.5 text-right {{ $nominalClass }} tabular-nums">
                                {{ $prefix }} {{ number_format($act['nominal'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-400 dark:text-gray-500">
                                Tidak ada aktivitas keuangan pada periode ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-filament-panels::page>
