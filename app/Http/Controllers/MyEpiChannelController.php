<?php

namespace App\Http\Controllers;

use App\Enums\CommissionStatus;
use App\Enums\PayoutStatus;
use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\Product;
use App\Models\PromoAsset;
use App\Models\ReferralOrder;
use App\Models\ReferralVisit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyEpiChannelController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing(['epiChannel']);

        $channel = $user->epiChannel;

        if (! $channel || ! $channel->isActive()) {
            return view('epi-channel.index', [
                'channel' => $channel,
                'stats' => null,
                'featuredProducts' => collect(),
                'mainReferralLink' => null,
            ]);
        }

        $clicksCount = ReferralVisit::query()->where('epi_channel_id', $channel->id)->count();
        $referralOrdersCount = ReferralOrder::query()->where('epi_channel_id', $channel->id)->count();

        $commissions = Commission::query()
            ->where('epi_channel_id', $channel->id)
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(commission_amount), 0) as amount')
            ->groupBy('status')
            ->get()
            ->keyBy(fn (Commission $row) => (string) $row->status->value);

        $stats = [
            'clicks' => $clicksCount,
            'referral_orders' => $referralOrdersCount,
            'commission_pending_count' => (int) ($commissions[CommissionStatus::Pending->value]->count ?? 0),
            'commission_pending_amount' => (string) ($commissions[CommissionStatus::Pending->value]->amount ?? '0.00'),
            'commission_approved_count' => (int) ($commissions[CommissionStatus::Approved->value]->count ?? 0),
            'commission_approved_amount' => (string) ($commissions[CommissionStatus::Approved->value]->amount ?? '0.00'),
            'commission_paid_count' => (int) ($commissions[CommissionStatus::Paid->value]->count ?? 0),
            'commission_paid_amount' => (string) ($commissions[CommissionStatus::Paid->value]->amount ?? '0.00'),
        ];

        $featuredProducts = Product::query()
            ->published()
            ->visiblePublic()
            ->where('is_affiliate_enabled', true)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $mainReferralLink = route('catalog.products.index', absolute: true).'?ref='.$channel->epic_code;

        return view('epi-channel.index', [
            'channel' => $channel,
            'stats' => $stats,
            'featuredProducts' => $featuredProducts,
            'mainReferralLink' => $mainReferralLink,
        ]);
    }

    public function links(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing(['epiChannel']);

        $channel = $user->epiChannel;

        $products = Product::query()
            ->published()
            ->visiblePublic()
            ->where('is_affiliate_enabled', true)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->paginate(12);

        return view('epi-channel.links', [
            'channel' => $channel,
            'products' => $products,
        ]);
    }

    public function commissions(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing(['epiChannel']);

        $channel = $user->epiChannel;

        $commissions = Commission::query()
            ->when($channel, fn ($q) => $q->where('epi_channel_id', $channel->id))
            ->with(['product', 'order'])
            ->latest('id')
            ->paginate(15);

        return view('epi-channel.commissions', [
            'channel' => $channel,
            'commissions' => $commissions,
        ]);
    }

    public function payouts(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing(['epiChannel']);

        $channel = $user->epiChannel;

        $payouts = CommissionPayout::query()
            ->when($channel, fn ($q) => $q->where('epi_channel_id', $channel->id))
            ->withCount(['commissions'])
            ->latest('id')
            ->paginate(15);

        $totals = $channel
            ? CommissionPayout::query()
                ->where('epi_channel_id', $channel->id)
                ->selectRaw('status, COALESCE(SUM(total_amount), 0) as amount')
                ->groupBy('status')
                ->get()
                ->keyBy(fn (CommissionPayout $row) => (string) $row->status->value)
            : collect();

        $summary = [
            'paid_amount' => (string) ($totals[PayoutStatus::Paid->value]->amount ?? '0.00'),
        ];

        return view('epi-channel.payouts', [
            'channel' => $channel,
            'payouts' => $payouts,
            'summary' => $summary,
        ]);
    }

    public function promoAssets(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing(['epiChannel']);

        $channel = $user->epiChannel;

        $assets = PromoAsset::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->paginate(12);

        return view('epi-channel.promo-assets', [
            'channel' => $channel,
            'assets' => $assets,
        ]);
    }
}

