<?php

namespace App\Http\Controllers;

use App\Actions\Affiliates\ResolveReferrerContactAction;
use App\Enums\AffiliateClientFollowUpStatus;
use App\Models\AffiliateClientNote;
use App\Models\Commission;
use App\Models\EpiChannel;
use App\Models\Order;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardClientController extends Controller
{
    public function __construct(
        protected ResolveReferrerContactAction $resolveReferrerContact,
    ) {}

    public function index(Request $request): View
    {
        $channel = $this->resolveActiveChannel($request);

        if (! $channel) {
            return $this->renderUnavailableState($request);
        }

        $filters = $this->filtersFromRequest($request);
        $notesEnabled = Schema::hasTable('affiliate_client_notes');

        $clientsQuery = $this->baseClientsQuery($channel->id);
        $this->applyClientFilters($clientsQuery, $filters, $channel->id);

        $clients = $this->applyClientSorting($clientsQuery, $filters['sort'], $channel->id)
            ->paginate(12)
            ->withQueryString();

        $this->hydrateClientRows(
            clients: $clients,
            epiChannelId: $channel->id,
            notesEnabled: $notesEnabled,
        );

        return view('dashboard.clients.index', [
            'channel' => $channel,
            'clients' => $clients,
            'summary' => $this->buildSummary($channel->id),
            'filters' => $filters,
            'statusOptions' => $this->statusOptions(),
            'sortOptions' => $this->sortOptions(),
            'followUpStatusOptions' => AffiliateClientFollowUpStatus::options(),
            'notesEnabled' => $notesEnabled,
            'referrerContact' => $this->resolveReferrerContact->execute($request->user()),
        ]);
    }

    public function show(Request $request, User $client): View
    {
        $channel = $this->resolveActiveChannel($request);

        if (! $channel) {
            return $this->renderUnavailableState($request);
        }

        abort_unless($this->clientBelongsToChannel($client->id, $channel->id), 404);

        $notesEnabled = Schema::hasTable('affiliate_client_notes');
        $client = $this->baseClientsQuery($channel->id)->whereKey($client->id)->firstOrFail();
        $client->loadMissing('referrerEpiChannel');

        $latestOrder = Order::query()
            ->where('user_id', $client->id)
            ->paid()
            ->attributedToEpiChannel($channel->id)
            ->with(['items.product'])
            ->latest('paid_at')
            ->latest('id')
            ->first();

        $latestNote = $notesEnabled
            ? AffiliateClientNote::query()
                ->where('epi_channel_id', $channel->id)
                ->where('client_user_id', $client->id)
                ->with('creator')
                ->latest('id')
                ->first()
            : null;

        $client->setAttribute('latest_order', $latestOrder);
        $client->setAttribute('latest_note', $latestNote);
        $client->setAttribute('latest_product_label', $this->latestProductLabel($latestOrder));
        $client->setAttribute('status_badges', $this->statusBadges(
            paidOrdersCount: (int) $client->paid_orders_count,
            activeProductsCount: (int) $client->active_user_products_count,
        ));
        $client->setAttribute('whatsapp_url', $client->whatsapp_number_for_url
            ? 'https://wa.me/'.$client->whatsapp_number_for_url
            : null);

        $orderHistory = Order::query()
            ->where('user_id', $client->id)
            ->paid()
            ->attributedToEpiChannel($channel->id)
            ->with(['items.product', 'payments'])
            ->latest('paid_at')
            ->latest('id')
            ->get();

        $userProducts = UserProduct::query()
            ->where('user_id', $client->id)
            ->with(['product', 'order'])
            ->latest('granted_at')
            ->latest('id')
            ->get();

        $commissions = Commission::query()
            ->where('epi_channel_id', $channel->id)
            ->where('buyer_user_id', $client->id)
            ->with(['product', 'order'])
            ->latest('created_at')
            ->latest('id')
            ->get();

        $notes = $notesEnabled
            ? AffiliateClientNote::query()
                ->where('epi_channel_id', $channel->id)
                ->where('client_user_id', $client->id)
                ->with('creator')
                ->latest('created_at')
                ->latest('id')
                ->get()
            : collect();

        return view('dashboard.clients.show', [
            'channel' => $channel,
            'client' => $client,
            'orderHistory' => $orderHistory,
            'userProducts' => $userProducts,
            'commissions' => $commissions,
            'notes' => $notes,
            'notesEnabled' => $notesEnabled,
            'followUpStatusOptions' => AffiliateClientFollowUpStatus::options(),
        ]);
    }

    public function storeNote(Request $request, User $client): RedirectResponse
    {
        $channel = $this->resolveActiveChannel($request);
        abort_unless($channel, 403);
        abort_unless($this->clientBelongsToChannel($client->id, $channel->id), 404);

        if (! Schema::hasTable('affiliate_client_notes')) {
            return redirect()
                ->back()
                ->with('client_notice', 'Tabel catatan follow-up belum tersedia. Jalankan migrate terlebih dahulu.');
        }

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:5000'],
            'follow_up_status' => ['nullable', Rule::in(array_keys(AffiliateClientFollowUpStatus::options()))],
            'next_follow_up_at' => ['nullable', 'date'],
        ]);

        AffiliateClientNote::query()->create([
            'epi_channel_id' => $channel->id,
            'client_user_id' => $client->id,
            'note' => $validated['note'],
            'follow_up_status' => $validated['follow_up_status'] ?: null,
            'next_follow_up_at' => $validated['next_follow_up_at'] ?: null,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('dashboard.clients.show', $client)
            ->with('client_notice', 'Catatan follow-up berhasil ditambahkan.');
    }

    public function markFollowUp(Request $request, User $client): RedirectResponse
    {
        $channel = $this->resolveActiveChannel($request);
        abort_unless($channel, 403);
        abort_unless($this->clientBelongsToChannel($client->id, $channel->id), 404);

        if (! Schema::hasTable('affiliate_client_notes')) {
            return redirect()
                ->back()
                ->with('client_notice', 'Tabel catatan follow-up belum tersedia. Jalankan migrate terlebih dahulu.');
        }

        AffiliateClientNote::query()->create([
            'epi_channel_id' => $channel->id,
            'client_user_id' => $client->id,
            'note' => 'Follow-up ditandai dari dashboard klien.',
            'follow_up_status' => AffiliateClientFollowUpStatus::Contacted,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->back()
            ->with('client_notice', 'Klien ditandai sudah di-follow up.');
    }

    protected function resolveActiveChannel(Request $request): ?EpiChannel
    {
        $request->user()->loadMissing('epiChannel');

        $channel = $request->user()->epiChannel;

        return $channel && $channel->isActive() ? $channel : null;
    }

    protected function renderUnavailableState(Request $request): View
    {
        $request->user()->loadMissing('epiChannel');

        return view('dashboard.clients.index', [
            'channel' => $request->user()->epiChannel,
            'clients' => new LengthAwarePaginator([], 0, 12, 1, [
                'path' => $request->url(),
                'pageName' => 'page',
            ]),
            'summary' => [
                'total_clients' => 0,
                'prospects' => 0,
                'buyers' => 0,
                'repeat_buyers' => 0,
                'referral_revenue' => 0,
            ],
            'filters' => $this->filtersFromRequest($request),
            'statusOptions' => $this->statusOptions(),
            'sortOptions' => $this->sortOptions(),
            'followUpStatusOptions' => AffiliateClientFollowUpStatus::options(),
            'notesEnabled' => Schema::hasTable('affiliate_client_notes'),
            'referrerContact' => $this->resolveReferrerContact->execute($request->user()),
        ]);
    }

    protected function baseClientsQuery(int $epiChannelId): Builder
    {
        return User::query()
            ->select('users.*')
            ->where(function (Builder $query) use ($epiChannelId): void {
                $query
                    ->where('referrer_epi_channel_id', $epiChannelId)
                    ->orWhereHas('referralOrders', fn (Builder $referralOrderQuery) => $referralOrderQuery->where('epi_channel_id', $epiChannelId));
            })
            ->selectSub($this->paidOrdersCountSubquery($epiChannelId), 'paid_orders_count')
            ->selectSub($this->paidOrdersSumSubquery($epiChannelId), 'paid_orders_total_amount')
            ->selectSub($this->lastPaidOrderAtSubquery($epiChannelId), 'last_paid_order_at')
            ->selectSub($this->activeUserProductsCountSubquery(), 'active_user_products_count');
    }

    protected function applyClientFilters(Builder $query, array $filters, int $epiChannelId): void
    {
        if (filled($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function (Builder $searchQuery) use ($search): void {
                $searchQuery
                    ->where('users.name', 'like', '%'.$search.'%')
                    ->orWhere('users.email', 'like', '%'.$search.'%')
                    ->orWhere('users.whatsapp_number', 'like', '%'.$search.'%');
            });
        }

        if (filled($filters['status'])) {
            match ($filters['status']) {
                'prospect' => $query->whereDoesntHave('orders', fn (Builder $orderQuery) => $this->applyPaidOrdersScope($orderQuery, $epiChannelId)),
                'sudah_beli' => $query->whereHas('orders', fn (Builder $orderQuery) => $this->applyPaidOrdersScope($orderQuery, $epiChannelId)),
                'repeat_buyer' => $query->whereIn('users.id', $this->repeatBuyerIdsSubquery($epiChannelId)),
                'aktif' => $query->whereHas('userProducts', fn (Builder $userProductQuery) => $userProductQuery->active()),
                default => null,
            };
        }
    }

    protected function applyClientSorting(Builder $query, string $sort, int $epiChannelId): Builder
    {
        return match ($sort) {
            'latest_order' => $query
                ->orderByDesc('last_paid_order_at')
                ->orderByDesc('users.created_at'),
            'highest_revenue' => $query
                ->orderByDesc('paid_orders_total_amount')
                ->orderByDesc('last_paid_order_at')
                ->orderByDesc('users.created_at'),
            'not_purchased_first' => $query
                ->orderByRaw('CASE WHEN COALESCE(paid_orders_count, 0) = 0 THEN 0 ELSE 1 END ASC')
                ->orderByDesc('users.created_at'),
            'name_asc' => $query->orderBy('users.name'),
            default => $query->orderByDesc('users.created_at'),
        };
    }

    protected function hydrateClientRows(LengthAwarePaginator $clients, int $epiChannelId, bool $notesEnabled): void
    {
        $clientIds = $clients->getCollection()
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        if ($clientIds === []) {
            return;
        }

        $latestOrders = Order::query()
            ->whereIn('user_id', $clientIds)
            ->paid()
            ->attributedToEpiChannel($epiChannelId)
            ->with(['items.product'])
            ->latest('paid_at')
            ->latest('id')
            ->get()
            ->groupBy('user_id')
            ->map(fn (Collection $orders) => $orders->first());

        $latestNotes = $notesEnabled
            ? AffiliateClientNote::query()
                ->where('epi_channel_id', $epiChannelId)
                ->whereIn('client_user_id', $clientIds)
                ->with('creator')
                ->latest('id')
                ->get()
                ->groupBy('client_user_id')
                ->map(fn (Collection $notes) => $notes->first())
            : collect();

        $clients->setCollection(
            $clients->getCollection()->map(function (User $client) use ($latestOrders, $latestNotes): User {
                $latestOrder = $latestOrders->get($client->id);
                $latestNote = $latestNotes->get($client->id);

                $client->setAttribute('latest_order', $latestOrder);
                $client->setAttribute('latest_note', $latestNote);
                $client->setAttribute('latest_product_label', $this->latestProductLabel($latestOrder));
                $client->setAttribute('status_badges', $this->statusBadges(
                    paidOrdersCount: (int) $client->paid_orders_count,
                    activeProductsCount: (int) $client->active_user_products_count,
                ));
                $client->setAttribute('whatsapp_url', $client->whatsapp_number_for_url
                    ? 'https://wa.me/'.$client->whatsapp_number_for_url
                    : null);

                return $client;
            }),
        );
    }

    protected function buildSummary(int $epiChannelId): array
    {
        $clientQuery = $this->baseClientsQuery($epiChannelId);

        return [
            'total_clients' => (clone $clientQuery)->count(),
            'prospects' => (clone $clientQuery)
                ->whereDoesntHave('orders', fn (Builder $orderQuery) => $this->applyPaidOrdersScope($orderQuery, $epiChannelId))
                ->count(),
            'buyers' => (clone $clientQuery)
                ->whereHas('orders', fn (Builder $orderQuery) => $this->applyPaidOrdersScope($orderQuery, $epiChannelId))
                ->count(),
            'repeat_buyers' => (clone $clientQuery)
                ->whereIn('users.id', $this->repeatBuyerIdsSubquery($epiChannelId))
                ->count(),
            'referral_revenue' => (float) Order::query()
                ->paid()
                ->attributedToEpiChannel($epiChannelId)
                ->sum('total_amount'),
        ];
    }

    protected function paidOrdersCountSubquery(int $epiChannelId): Builder
    {
        return Order::query()
            ->selectRaw('COUNT(*)')
            ->whereColumn('orders.user_id', 'users.id')
            ->paid()
            ->attributedToEpiChannel($epiChannelId);
    }

    protected function paidOrdersSumSubquery(int $epiChannelId): Builder
    {
        return Order::query()
            ->selectRaw('COALESCE(SUM(total_amount), 0)')
            ->whereColumn('orders.user_id', 'users.id')
            ->paid()
            ->attributedToEpiChannel($epiChannelId);
    }

    protected function lastPaidOrderAtSubquery(int $epiChannelId): Builder
    {
        return Order::query()
            ->selectRaw('MAX(COALESCE(paid_at, created_at))')
            ->whereColumn('orders.user_id', 'users.id')
            ->paid()
            ->attributedToEpiChannel($epiChannelId);
    }

    protected function activeUserProductsCountSubquery(): Builder
    {
        return UserProduct::query()
            ->selectRaw('COUNT(*)')
            ->whereColumn('user_products.user_id', 'users.id')
            ->active();
    }

    protected function repeatBuyerIdsSubquery(int $epiChannelId): Builder
    {
        return Order::query()
            ->select('user_id')
            ->paid()
            ->attributedToEpiChannel($epiChannelId)
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1');
    }

    protected function clientBelongsToChannel(int $clientUserId, int $epiChannelId): bool
    {
        return $this->baseClientsQuery($epiChannelId)
            ->whereKey($clientUserId)
            ->exists();
    }

    protected function applyPaidOrdersScope(Builder $orderQuery, int $epiChannelId): void
    {
        $orderQuery->paid()->attributedToEpiChannel($epiChannelId);
    }

    protected function latestProductLabel(?Order $order): ?string
    {
        if (! $order) {
            return null;
        }

        $items = $order->items;
        $firstTitle = $items->first()?->product?->title
            ?? $items->first()?->product_title;

        if (! $firstTitle) {
            return null;
        }

        $extraCount = max(0, $items->count() - 1);

        return $extraCount > 0
            ? $firstTitle.' +'.$extraCount.' produk'
            : $firstTitle;
    }

    /**
     * @return array<int, array{label: string, variant: string}>
     */
    protected function statusBadges(int $paidOrdersCount, int $activeProductsCount): array
    {
        $badges = [];

        if ($paidOrdersCount <= 0) {
            $badges[] = ['label' => 'Prospek', 'variant' => 'warning'];
        } elseif ($paidOrdersCount > 1) {
            $badges[] = ['label' => 'Repeat Buyer', 'variant' => 'info'];
        } else {
            $badges[] = ['label' => 'Sudah Beli', 'variant' => 'success'];
        }

        if ($activeProductsCount > 0) {
            $badges[] = ['label' => 'Aktif', 'variant' => 'success'];
        }

        return $badges;
    }

    protected function filtersFromRequest(Request $request): array
    {
        return [
            'search' => (string) $request->string('search'),
            'status' => (string) $request->string('status', 'all'),
            'sort' => (string) $request->string('sort', 'latest_registered'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function statusOptions(): array
    {
        return [
            'all' => 'Semua Status',
            'prospect' => 'Prospek',
            'sudah_beli' => 'Sudah Beli',
            'repeat_buyer' => 'Repeat Buyer',
            'aktif' => 'Aktif',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function sortOptions(): array
    {
        return [
            'latest_registered' => 'Terbaru daftar',
            'latest_order' => 'Terbaru order',
            'highest_revenue' => 'Omzet terbesar',
            'not_purchased_first' => 'Belum beli dulu',
            'name_asc' => 'Nama A-Z',
        ];
    }
}
