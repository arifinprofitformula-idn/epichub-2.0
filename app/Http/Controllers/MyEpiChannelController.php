<?php

namespace App\Http\Controllers;

use App\Actions\Affiliates\ResolveReferrerContactAction;
use App\Enums\CommissionStatus;
use App\Enums\PayoutStatus;
use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\EpiChannel;
use App\Models\Product;
use App\Models\PromoAsset;
use App\Models\ReferralOrder;
use App\Models\ReferralVisit;
use App\Models\UserProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class MyEpiChannelController extends Controller
{
    public function __construct(
        protected ResolveReferrerContactAction $resolveReferrerContact,
    ) {}

    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing('epiChannel');

        $channel = $user->epiChannel;

        if (! $channel || ! $channel->isActive()) {
            return $this->inactiveView($request);
        }

        $commissionBuckets = Commission::query()
            ->where('epi_channel_id', $channel->id)
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(commission_amount), 0) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy(fn (Commission $row) => (string) $row->status->value);

        $stats = [
            'clicks' => ReferralVisit::query()->where('epi_channel_id', $channel->id)->count(),
            'referral_orders' => ReferralOrder::query()->where('epi_channel_id', $channel->id)->count(),
            'commission_pending_amount' => (string) ($commissionBuckets[CommissionStatus::Pending->value]->amount ?? '0.00'),
            'commission_approved_amount' => (string) ($commissionBuckets[CommissionStatus::Approved->value]->amount ?? '0.00'),
            'commission_paid_amount' => (string) ($commissionBuckets[CommissionStatus::Paid->value]->amount ?? '0.00'),
            'total_payout_paid' => (string) CommissionPayout::query()
                ->where('epi_channel_id', $channel->id)
                ->where('status', PayoutStatus::Paid)
                ->sum('total_amount'),
        ];

        $recentCommissions = Commission::query()
            ->where('epi_channel_id', $channel->id)
            ->with(['product', 'order'])
            ->latest('id')
            ->limit(5)
            ->get();

        $topProductsByClick = ReferralVisit::query()
            ->where('epi_channel_id', $channel->id)
            ->whereNotNull('product_id')
            ->selectRaw('product_id, COUNT(*) as total_clicks')
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_clicks')
            ->limit(5)
            ->get();

        $featuredProducts = $this->affiliateProductsQuery()
            ->limit(6)
            ->get();

        return view('epi-channel.index', [
            'channel' => $channel,
            'stats' => $stats,
            'recentCommissions' => $recentCommissions,
            'topProductsByClick' => $topProductsByClick,
            'featuredProducts' => $featuredProducts,
            'whatsappReminderNeeded' => blank($user->whatsapp_number_for_url),
            'mainReferralLink' => route('referral.redirect', ['epicCode' => $channel->epic_code], absolute: true),
            'referrerContact' => $this->resolveReferrerContact->execute($user),
        ]);
    }

    public function links(Request $request): View|RedirectResponse
    {
        $channel = $this->resolveActiveChannel($request);

        if (! $channel) {
            return redirect()->route('epi-channel.dashboard');
        }

        $products = $this->affiliateProductsQuery()->paginate(12);
        $ownedUserProducts = UserProduct::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('product_id', $products->getCollection()->pluck('id'))
            ->whereNull('revoked_at')
            ->active()
            ->latest('granted_at')
            ->get()
            ->unique('product_id')
            ->keyBy('product_id');

        return view('epi-channel.links', [
            'channel' => $channel,
            'products' => $products,
            'ownedUserProducts' => $ownedUserProducts,
            'mainReferralLink' => route('referral.redirect', ['epicCode' => $channel->epic_code], absolute: true),
        ]);
    }

    public function products(Request $request): View|RedirectResponse
    {
        $channel = $this->resolveActiveChannel($request);

        if (! $channel) {
            return redirect()->route('epi-channel.dashboard');
        }

        $products = $this->affiliateProductsQuery()->paginate(12);

        return view('epi-channel.products', [
            'channel' => $channel,
            'products' => $products,
        ]);
    }

    public function visits(Request $request): View|RedirectResponse
    {
        $channel = $this->resolveActiveChannel($request);

        if (! $channel) {
            return redirect()->route('epi-channel.dashboard');
        }

        $visits = ReferralVisit::query()
            ->where('epi_channel_id', $channel->id)
            ->with('product')
            ->when($request->integer('product_id') > 0, fn ($query) => $query->where('product_id', $request->integer('product_id')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('clicked_at', '>=', (string) $request->string('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('clicked_at', '<=', (string) $request->string('date_to')))
            ->latest('clicked_at')
            ->paginate(15)
            ->withQueryString();

        return view('epi-channel.visits', [
            'channel' => $channel,
            'visits' => $visits,
            'products' => $this->affiliateProductsQuery()->get(['id', 'title']),
            'filters' => $request->only(['product_id', 'date_from', 'date_to']),
        ]);
    }

    public function orders(Request $request): View|RedirectResponse
    {
        $channel = $this->resolveActiveChannel($request);

        if (! $channel) {
            return redirect()->route('epi-channel.dashboard');
        }

        $orders = ReferralOrder::query()
            ->where('epi_channel_id', $channel->id)
            ->with(['buyer', 'order.items'])
            ->latest('attributed_at')
            ->paginate(15);

        return view('epi-channel.orders', [
            'channel' => $channel,
            'orders' => $orders,
        ]);
    }

    public function commissions(Request $request): View|RedirectResponse
    {
        $channel = $this->resolveActiveChannel($request);

        if (! $channel) {
            return redirect()->route('epi-channel.dashboard');
        }

        $activeTab = (string) $request->string('tab', 'summary');

        if (! in_array($activeTab, ['summary', 'v2', 'payout'], true)) {
            $activeTab = 'summary';
        }

        $v2Buckets = Commission::query()
            ->where('epi_channel_id', $channel->id)
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(commission_amount), 0) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy(fn (Commission $row) => (string) $row->status->value);

        $v2Commissions = null;
        $payouts = null;
        $recentLedgerRows = collect();

        if ($activeTab === 'v2') {
            $v2Commissions = Commission::query()
                ->where('epi_channel_id', $channel->id)
                ->with(['product', 'order'])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(15, ['*'], 'v2_page')
                ->withQueryString();
        }

        if ($activeTab === 'payout') {
            $payouts = CommissionPayout::query()
                ->where('epi_channel_id', $channel->id)
                ->withCount('commissions')
                ->latest('id')
                ->paginate(15, ['*'], 'payout_page')
                ->withQueryString();
        }

        if ($activeTab === 'summary') {
            $recentLedgerRows = $this->buildLedgerRows(
                Commission::query()
                    ->where('epi_channel_id', $channel->id)
                    ->with('product')
                    ->latest('created_at')
                    ->limit(6)
                    ->get(),
            )->take(12)->values();
        }

        return view('epi-channel.commissions', [
            'channel' => $channel,
            'activeTab' => $activeTab,
            'v2Commissions' => $v2Commissions,
            'payouts' => $payouts,
            'recentLedgerRows' => $recentLedgerRows,
            'summary' => $this->buildCommissionLedgerSummary($v2Buckets),
        ]);
    }

    public function payouts(Request $request): View|RedirectResponse
    {
        $channel = $this->resolveActiveChannel($request);

        if (! $channel) {
            return redirect()->route('epi-channel.dashboard');
        }

        $payouts = CommissionPayout::query()
            ->where('epi_channel_id', $channel->id)
            ->withCount('commissions')
            ->latest('id')
            ->paginate(15);

        $totals = CommissionPayout::query()
            ->where('epi_channel_id', $channel->id)
            ->selectRaw('status, COALESCE(SUM(total_amount), 0) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy(fn (CommissionPayout $row) => (string) $row->status->value);

        return view('epi-channel.payouts', [
            'channel' => $channel,
            'payouts' => $payouts,
            'summary' => [
                'paid_amount' => (string) ($totals[PayoutStatus::Paid->value]->amount ?? '0.00'),
                'processing_amount' => (string) ($totals[PayoutStatus::Processing->value]->amount ?? '0.00'),
            ],
        ]);
    }

    public function promoAssets(Request $request): View|RedirectResponse
    {
        $channel = $this->resolveActiveChannel($request);

        if (! $channel) {
            return redirect()->route('epi-channel.dashboard');
        }

        $assets = Schema::hasTable('promo_assets')
            ? PromoAsset::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->paginate(12)
            : new LengthAwarePaginator(
                items: [],
                total: 0,
                perPage: 12,
                currentPage: LengthAwarePaginator::resolveCurrentPage(),
                options: ['path' => $request->url(), 'pageName' => 'page'],
            );

        return view('epi-channel.promo-assets', [
            'channel' => $channel,
            'assets' => $assets,
            'hasPromoAssetsTable' => Schema::hasTable('promo_assets'),
        ]);
    }

    public function profile(Request $request): View|RedirectResponse
    {
        $channel = $this->resolveActiveChannel($request);

        if (! $channel) {
            return redirect()->route('epi-channel.dashboard');
        }

        return view('epi-channel.profile', [
            'channel' => $channel,
            'user'    => $request->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $channel = $this->resolveActiveChannel($request);

        if (! $channel) {
            return redirect()->route('epi-channel.dashboard');
        }

        $validated = $request->validate([
            'payout_bank_name' => ['nullable', 'string', 'max:255', 'required_with:payout_bank_account_number,payout_bank_account_holder_name'],
            'payout_bank_account_number' => ['nullable', 'string', 'max:50', 'required_with:payout_bank_name,payout_bank_account_holder_name'],
            'payout_bank_account_holder_name' => ['nullable', 'string', 'max:255', 'required_with:payout_bank_name,payout_bank_account_number'],
        ]);

        $channel->update([
            'payout_bank_name' => blank($validated['payout_bank_name'] ?? null) ? null : trim((string) $validated['payout_bank_name']),
            'payout_bank_account_number' => blank($validated['payout_bank_account_number'] ?? null) ? null : preg_replace('/\s+/', '', (string) $validated['payout_bank_account_number']),
            'payout_bank_account_holder_name' => blank($validated['payout_bank_account_holder_name'] ?? null) ? null : trim((string) $validated['payout_bank_account_holder_name']),
        ]);

        return redirect()
            ->route('epi-channel.profile')
            ->with('epi_channel_profile_notice', 'Data rekening payout berhasil diperbarui.');
    }

    protected function resolveActiveChannel(Request $request): ?EpiChannel
    {
        $request->user()->loadMissing('epiChannel');

        $channel = $request->user()->epiChannel;

        return $channel && $channel->isActive() ? $channel : null;
    }

    protected function inactiveView(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing('epiChannel');

        return view('epi-channel.index', [
            'channel' => $user->epiChannel,
            'stats' => null,
            'recentCommissions' => collect(),
            'topProductsByClick' => collect(),
            'featuredProducts' => collect(),
            'whatsappReminderNeeded' => false,
            'mainReferralLink' => null,
            'referrerContact' => $this->resolveReferrerContact->execute($user),
        ]);
    }

    protected function affiliateProductsQuery()
    {
        return Product::query()
            ->published()
            ->visiblePublic()
            ->where('is_affiliate_enabled', true)
            ->with('category')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('publish_at');
    }

    protected function buildCommissionLedgerSummary(Collection $v2Buckets): array
    {
        $v2Total = (float) $v2Buckets->sum('amount');
        $v2Paid = (float) ($v2Buckets[CommissionStatus::Paid->value]->amount ?? 0);
        $v2Approved = (float) ($v2Buckets[CommissionStatus::Approved->value]->amount ?? 0);
        $v2Pending = (float) ($v2Buckets[CommissionStatus::Pending->value]->amount ?? 0);
        $v2Rejected = (float) ($v2Buckets[CommissionStatus::Rejected->value]->amount ?? 0);

        return [
            'v2_total' => $v2Total,
            'overall_total' => $v2Total,
            'paid_total' => $v2Paid,
            'approved_total' => $v2Approved,
            'unpaid_total' => $v2Total - $v2Paid - $v2Rejected,
            'pending_total' => $v2Pending,
            'v2_pending_total' => $v2Pending,
            'v2_paid_total' => $v2Paid,
            'v2_approved_total' => $v2Approved,
        ];
    }

    protected function buildLedgerRows(Collection $v2Commissions): Collection
    {
        return $v2Commissions->map(function (Commission $commission): array {
            $date = $commission->paid_at ?? $commission->approved_at ?? $commission->created_at;

            return [
                'date' => $date,
                'date_label' => $date?->format('d M Y H:i') ?? '-',
                'source' => 'EPIC HUB 2.0',
                'product' => $commission->product?->title ?? '-',
                'type' => $commission->commission_type?->label() ?? '-',
                'level' => '-',
                'status_label' => $commission->status?->label() ?? (string) $commission->status,
                'status_color' => $commission->status?->getColor() ?? 'gray',
                'amount' => (float) $commission->commission_amount,
            ];
        })->values();
    }
}
