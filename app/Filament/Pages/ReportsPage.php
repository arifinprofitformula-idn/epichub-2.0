<?php

namespace App\Filament\Pages;

use App\Enums\CommissionStatus;
use App\Enums\ContributorCommissionStatus;
use App\Enums\OrderStatus;
use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\ContributorCommission;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use UnitEnum;
use App\Filament\Navigation\AdminNavigationGroup;

class ReportsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Laporan';

    protected static string|UnitEnum|null $navigationGroup = AdminNavigationGroup::Administration;

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.reports-page';

    // Filter state
    public string $period = 'monthly';
    public ?string $customFrom = null;
    public ?string $customTo = null;
    public ?int $selectedYear = null;
    public ?int $selectedMonth = null;
    public ?int $selectedQuarter = null;
    public ?int $selectedSemester = null;

    public function mount(): void
    {
        $now = now()->setTimezone(config('app.timezone', 'Asia/Jakarta'));
        $this->selectedYear = $now->year;
        $this->selectedMonth = $now->month;
        $this->selectedQuarter = (int) ceil($now->month / 3);
        $this->selectedSemester = $now->month <= 6 ? 1 : 2;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'finance']) ?? false;
    }

    public function getHeading(): string
    {
        return 'Laporan Penjualan & Komisi';
    }

    public function getSubheading(): ?string
    {
        return 'Analisis keuangan dan aliran kas platform periode terpilih.';
    }

    // ─── Date Range Resolution ───────────────────────────────────────────────

    public function getDateRange(): array
    {
        $tz = config('app.timezone', 'Asia/Jakarta');
        $year = $this->selectedYear ?? now()->year;

        return match ($this->period) {
            'monthly' => [
                Carbon::create($year, $this->selectedMonth ?? now()->month, 1, 0, 0, 0, $tz)->startOfMonth(),
                Carbon::create($year, $this->selectedMonth ?? now()->month, 1, 0, 0, 0, $tz)->endOfMonth(),
            ],
            'quarterly' => [
                Carbon::create($year, (($this->selectedQuarter ?? 1) - 1) * 3 + 1, 1, 0, 0, 0, $tz)->startOfMonth(),
                Carbon::create($year, (($this->selectedQuarter ?? 1) - 1) * 3 + 3, 1, 0, 0, 0, $tz)->endOfMonth(),
            ],
            'semester' => [
                Carbon::create($year, ($this->selectedSemester ?? 1) === 1 ? 1 : 7, 1, 0, 0, 0, $tz)->startOfMonth(),
                Carbon::create($year, ($this->selectedSemester ?? 1) === 1 ? 6 : 12, 1, 0, 0, 0, $tz)->endOfMonth(),
            ],
            'yearly' => [
                Carbon::create($year, 1, 1, 0, 0, 0, $tz)->startOfYear(),
                Carbon::create($year, 12, 31, 0, 0, 0, $tz)->endOfYear(),
            ],
            'custom' => [
                $this->customFrom ? Carbon::parse($this->customFrom, $tz)->startOfDay() : now($tz)->startOfMonth(),
                $this->customTo ? Carbon::parse($this->customTo, $tz)->endOfDay() : now($tz)->endOfDay(),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    public function getPeriodLabel(): string
    {
        [$from, $to] = $this->getDateRange();
        $tz = config('app.timezone', 'Asia/Jakarta');

        return match ($this->period) {
            'monthly' => $from->locale('id')->translatedFormat('F Y'),
            'quarterly' => 'Q' . ($this->selectedQuarter ?? 1) . ' ' . ($this->selectedYear ?? now()->year),
            'semester' => 'Semester ' . ($this->selectedSemester ?? 1) . ' ' . ($this->selectedYear ?? now()->year),
            'yearly' => (string) ($this->selectedYear ?? now()->year),
            'custom' => $from->format('d/m/Y') . ' – ' . $to->format('d/m/Y'),
            default => '',
        };
    }

    // ─── Summary Calculations ────────────────────────────────────────────────

    public function getOmzetKotor(): float
    {
        [$from, $to] = $this->getDateRange();

        return (float) Order::query()
            ->where('status', OrderStatus::Paid)
            ->whereBetween('paid_at', [$from, $to])
            ->sum('total_amount');
    }

    public function getKomisiAffiliate(): array
    {
        [$from, $to] = $this->getDateRange();

        $eligibleStatuses = [CommissionStatus::Approved->value, CommissionStatus::Paid->value];

        $baseQuery = Commission::query()
            ->whereIn('status', $eligibleStatuses)
            ->whereHas('order', fn (Builder $q) => $q
                ->where('status', OrderStatus::Paid)
                ->whereBetween('paid_at', [$from, $to])
            );

        $total = (float) (clone $baseQuery)->sum('commission_amount');
        $paid = (float) (clone $baseQuery)->where('status', CommissionStatus::Paid->value)->sum('commission_amount');
        $unpaid = (float) (clone $baseQuery)->where('status', CommissionStatus::Approved->value)->sum('commission_amount');

        return ['total' => $total, 'paid' => $paid, 'unpaid' => $unpaid];
    }

    public function getKomisiKontributor(): array
    {
        [$from, $to] = $this->getDateRange();

        $eligibleStatuses = [ContributorCommissionStatus::Approved->value, ContributorCommissionStatus::Paid->value];

        $baseQuery = ContributorCommission::query()
            ->whereIn('status', $eligibleStatuses)
            ->whereHas('order', fn (Builder $q) => $q
                ->where('status', OrderStatus::Paid)
                ->whereBetween('paid_at', [$from, $to])
            );

        $total = (float) (clone $baseQuery)->sum('commission_amount');
        $paid = (float) (clone $baseQuery)->where('status', ContributorCommissionStatus::Paid->value)->sum('commission_amount');
        $unpaid = (float) (clone $baseQuery)->where('status', ContributorCommissionStatus::Approved->value)->sum('commission_amount');

        return ['total' => $total, 'paid' => $paid, 'unpaid' => $unpaid];
    }

    public function getLabaBersih(): float
    {
        $omzet = $this->getOmzetKotor();
        $affiliate = $this->getKomisiAffiliate()['total'];
        $contributor = $this->getKomisiKontributor()['total'];

        return $omzet - $affiliate - $contributor;
    }

    // ─── Efektivitas Produk ──────────────────────────────────────────────────

    public function getEfektivitasProduk(): Collection
    {
        [$from, $to] = $this->getDateRange();

        $affiliateEligibleStatuses = [CommissionStatus::Approved->value, CommissionStatus::Paid->value];
        $contributorEligibleStatuses = [ContributorCommissionStatus::Approved->value, ContributorCommissionStatus::Paid->value];

        $products = Product::query()
            ->select('products.id', 'products.title')
            ->addSelect(DB::raw('COUNT(DISTINCT order_items.id) as sales_count'))
            ->addSelect(DB::raw('COALESCE(SUM(order_items.subtotal_amount), 0) as omzet_kotor'))
            ->join('order_items', 'order_items.product_id', '=', 'products.id')
            ->join('orders', function ($join) use ($from, $to): void {
                $join->on('orders.id', '=', 'order_items.order_id')
                    ->where('orders.status', OrderStatus::Paid->value)
                    ->whereBetween('orders.paid_at', [$from, $to]);
            })
            ->groupBy('products.id', 'products.title')
            ->orderByDesc('omzet_kotor')
            ->limit(20)
            ->get();

        // Enrich dengan data komisi
        $productIds = $products->pluck('id')->all();

        $affiliateByProduct = Commission::query()
            ->select('product_id', DB::raw('COALESCE(SUM(commission_amount), 0) as total'))
            ->whereIn('status', $affiliateEligibleStatuses)
            ->whereIn('product_id', $productIds)
            ->whereHas('order', fn (Builder $q) => $q
                ->where('status', OrderStatus::Paid->value)
                ->whereBetween('paid_at', [$from, $to])
            )
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $contributorByProduct = ContributorCommission::query()
            ->select('product_id', DB::raw('COALESCE(SUM(commission_amount), 0) as total'))
            ->whereIn('status', $contributorEligibleStatuses)
            ->whereIn('product_id', $productIds)
            ->whereHas('order', fn (Builder $q) => $q
                ->where('status', OrderStatus::Paid->value)
                ->whereBetween('paid_at', [$from, $to])
            )
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        return $products->map(function ($product) use ($affiliateByProduct, $contributorByProduct) {
            $omzet = (float) $product->omzet_kotor;
            $affiliate = (float) ($affiliateByProduct[$product->id] ?? 0);
            $contributor = (float) ($contributorByProduct[$product->id] ?? 0);

            return [
                'title' => $product->title,
                'sales' => (int) $product->sales_count,
                'omzet_kotor' => $omzet,
                'affiliate' => $affiliate,
                'contributor' => $contributor,
                'laba_bersih' => $omzet - $affiliate - $contributor,
            ];
        });
    }

    // ─── Aktivitas Keuangan Terbaru ──────────────────────────────────────────

    public function getAktivitasKeuangan(): Collection
    {
        [$from, $to] = $this->getDateRange();

        $activities = collect();

        // 1. Penjualan paid
        $orders = Order::query()
            ->with('user')
            ->where('status', OrderStatus::Paid)
            ->whereBetween('paid_at', [$from, $to])
            ->orderByDesc('paid_at')
            ->limit(30)
            ->get();

        foreach ($orders as $order) {
            $activities->push([
                'waktu' => $order->paid_at,
                'tipe' => 'penjualan',
                'label' => 'Penjualan',
                'ref' => $order->order_number ?? '#' . $order->id,
                'pihak' => $order->user?->name ?? 'Guest',
                'nominal' => (float) $order->total_amount,
                'arah' => 'masuk',
                'warna' => 'success',
            ]);
        }

        // 2. Komisi affiliate approved/paid
        $affiliates = Commission::query()
            ->with(['epiChannel.user', 'order'])
            ->whereIn('status', [CommissionStatus::Approved->value, CommissionStatus::Paid->value])
            ->whereHas('order', fn (Builder $q) => $q
                ->where('status', OrderStatus::Paid->value)
                ->whereBetween('paid_at', [$from, $to])
            )
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        foreach ($affiliates as $comm) {
            $activities->push([
                'waktu' => $comm->created_at,
                'tipe' => 'komisi_affiliate',
                'label' => 'Komisi Affiliate',
                'ref' => $comm->order?->order_number ?? '#' . $comm->order_id,
                'pihak' => $comm->epiChannel?->user?->name ?? $comm->epiChannel?->epic_code ?? '-',
                'nominal' => (float) $comm->commission_amount,
                'arah' => 'keluar',
                'warna' => 'warning',
            ]);
        }

        // 3. Komisi kontributor approved/paid
        $contributors = ContributorCommission::query()
            ->with(['contributor', 'order'])
            ->whereIn('status', [ContributorCommissionStatus::Approved->value, ContributorCommissionStatus::Paid->value])
            ->whereHas('order', fn (Builder $q) => $q
                ->where('status', OrderStatus::Paid->value)
                ->whereBetween('paid_at', [$from, $to])
            )
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        foreach ($contributors as $comm) {
            $activities->push([
                'waktu' => $comm->created_at,
                'tipe' => 'komisi_kontributor',
                'label' => 'Komisi Kontributor',
                'ref' => $comm->order?->order_number ?? '#' . $comm->order_id,
                'pihak' => $comm->contributor?->name ?? '-',
                'nominal' => (float) $comm->commission_amount,
                'arah' => 'keluar',
                'warna' => 'purple',
            ]);
        }

        // 4. Payout affiliate paid
        $payouts = CommissionPayout::query()
            ->with(['epiChannel.user'])
            ->where('status', \App\Enums\PayoutStatus::Paid)
            ->whereBetween('paid_at', [$from, $to])
            ->orderByDesc('paid_at')
            ->limit(20)
            ->get();

        foreach ($payouts as $payout) {
            $activities->push([
                'waktu' => $payout->paid_at,
                'tipe' => 'payout_affiliate',
                'label' => 'Payout Affiliate',
                'ref' => $payout->payout_number ?? '#' . $payout->id,
                'pihak' => $payout->epiChannel?->user?->name ?? $payout->epiChannel?->epic_code ?? '-',
                'nominal' => (float) $payout->total_amount,
                'arah' => 'keluar',
                'warna' => 'danger',
            ]);
        }

        return $activities
            ->sortByDesc('waktu')
            ->values()
            ->take(50);
    }

    // ─── Available Years ─────────────────────────────────────────────────────

    public function getAvailableYears(): array
    {
        $current = now()->year;
        $years = [];
        for ($y = $current; $y >= $current - 4; $y--) {
            $years[$y] = (string) $y;
        }

        return $years;
    }

    // ─── View Data ───────────────────────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $omzet = $this->getOmzetKotor();
        $affiliate = $this->getKomisiAffiliate();
        $contributor = $this->getKomisiKontributor();
        $laba = $this->getLabaBersih();

        return [
            'periodLabel' => $this->getPeriodLabel(),
            'omzetKotor' => $omzet,
            'labaBersih' => $laba,
            'affiliateTotal' => $affiliate['total'],
            'affiliatePaid' => $affiliate['paid'],
            'affiliateUnpaid' => $affiliate['unpaid'],
            'contributorTotal' => $contributor['total'],
            'contributorPaid' => $contributor['paid'],
            'contributorUnpaid' => $contributor['unpaid'],
            'efektivitasProduk' => $this->getEfektivitasProduk(),
            'aktivitasKeuangan' => $this->getAktivitasKeuangan(),
            'availableYears' => $this->getAvailableYears(),
            'months' => [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ],
        ];
    }
}
