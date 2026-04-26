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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
            'referrerContact' => null,
        ]);
    }

    public function links(Request $request): View|RedirectResponse
    {
        $channel = $this->resolveActiveChannel($request);

        if (! $channel) {
            return redirect()->route('epi-channel.dashboard');
        }

        $products = $this->affiliateProductsQuery()->paginate(12);

        return view('epi-channel.links', [
            'channel' => $channel,
            'products' => $products,
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

        $commissions = Commission::query()
            ->where('epi_channel_id', $channel->id)
            ->with(['product', 'order'])
            ->latest('id')
            ->paginate(15);

        $summary = Commission::query()
            ->where('epi_channel_id', $channel->id)
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(commission_amount), 0) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy(fn (Commission $row) => (string) $row->status->value);

        return view('epi-channel.commissions', [
            'channel' => $channel,
            'commissions' => $commissions,
            'summary' => [
                'pending_amount' => (string) ($summary[CommissionStatus::Pending->value]->amount ?? '0.00'),
                'approved_amount' => (string) ($summary[CommissionStatus::Approved->value]->amount ?? '0.00'),
                'paid_amount' => (string) ($summary[CommissionStatus::Paid->value]->amount ?? '0.00'),
                'rejected_amount' => (string) ($summary[CommissionStatus::Rejected->value]->amount ?? '0.00'),
            ],
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
        ]);
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
}
