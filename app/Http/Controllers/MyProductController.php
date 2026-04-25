<?php

namespace App\Http\Controllers;

use App\Actions\Access\LogAccessAction;
use App\Actions\Access\ResolveProductDeliveryAction;
use App\Enums\AccessLogAction;
use App\Enums\LessonProgressStatus;
use App\Enums\ProductType;
use App\Models\CourseLesson;
use App\Models\EventRegistration;
use App\Models\UserProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MyProductController extends Controller
{
    public function __construct(
        protected ResolveProductDeliveryAction $resolveProductDelivery,
        protected LogAccessAction $logAccess,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $activeQuery = $this->baseUserProductsQuery($user->id)
            ->active();

        $activeOwnedProducts = $activeQuery
            ->get();

        $groupedOwnedProducts = $activeOwnedProducts
            ->groupBy(fn (UserProduct $userProduct) => $userProduct->product?->product_type?->value ?? 'unknown');

        $filters = [
            'q' => trim((string) $request->string('q')),
            'product_type' => trim((string) $request->string('product_type')),
            'status' => trim((string) $request->string('status', 'active')),
        ];

        $status = in_array($filters['status'], ['active', 'revoked', 'expired', 'all'], true)
            ? $filters['status']
            : 'active';

        $query = $this->baseUserProductsQuery($user->id);

        match ($status) {
            'revoked' => $query->revoked(),
            'expired' => $query->expired(),
            'all' => $query,
            default => $query->active(),
        };

        if ($filters['q'] !== '') {
            $query->whereHas('product', function (Builder $builder) use ($filters): void {
                $builder->where(function (Builder $searchQuery) use ($filters): void {
                    $searchQuery
                        ->where('title', 'like', "%{$filters['q']}%")
                        ->orWhere('short_description', 'like', "%{$filters['q']}%")
                        ->orWhere('full_description', 'like', "%{$filters['q']}%");
                });
            });
        }

        if (ProductType::tryFrom($filters['product_type']) instanceof ProductType) {
            $query->whereHas('product', fn (Builder $builder) => $builder->where('product_type', $filters['product_type']));
        } else {
            $filters['product_type'] = '';
        }

        $userProducts = $query
            ->latest('granted_at')
            ->paginate(12)
            ->withQueryString();

        $courseProgressByUserProductId = $this->buildCourseProgressMap(
            userId: $user->id,
            userProducts: $userProducts->getCollection(),
        );

        $eventRegistrationsByUserProductId = EventRegistration::query()
            ->where('user_id', $user->id)
            ->whereIn('user_product_id', $userProducts->getCollection()->pluck('id'))
            ->with('event')
            ->latest('registered_at')
            ->get()
            ->unique('user_product_id')
            ->keyBy('user_product_id');

        return view('my-products.index', [
            'userProducts' => $userProducts,
            'activeFilters' => [
                'q' => $filters['q'],
                'product_type' => $filters['product_type'],
                'status' => $status,
            ],
            'productTypeOptions' => collect(ProductType::cases())->map(fn (ProductType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ]),
            'statusOptions' => [
                ['value' => 'active', 'label' => 'Aktif'],
                ['value' => 'revoked', 'label' => 'Dicabut'],
                ['value' => 'expired', 'label' => 'Kedaluwarsa'],
                ['value' => 'all', 'label' => 'Semua status'],
            ],
            'groupedOwnedProducts' => $groupedOwnedProducts,
            'summary' => [
                'total_products' => $activeOwnedProducts->count(),
                'digital_products' => $activeOwnedProducts
                    ->filter(fn (UserProduct $userProduct) => in_array($userProduct->product?->product_type?->value, [
                        ProductType::Ebook->value,
                        ProductType::DigitalFile->value,
                        ProductType::Bundle->value,
                        ProductType::Membership->value,
                    ], true))
                    ->count(),
                'active_courses' => $activeOwnedProducts
                    ->filter(fn (UserProduct $userProduct) => $userProduct->product?->product_type === ProductType::Course)
                    ->count(),
                'registered_events' => EventRegistration::query()
                    ->where('user_id', $user->id)
                    ->active()
                    ->count(),
            ],
            'courseProgressByUserProductId' => $courseProgressByUserProductId,
            'eventRegistrationsByUserProductId' => $eventRegistrationsByUserProductId,
        ]);
    }

    public function show(Request $request, UserProduct $userProduct): View
    {
        if (Gate::denies('view', $userProduct)) {
            $this->logAccess->execute(
                action: AccessLogAction::AccessDenied,
                user: $request->user(),
                userProduct: $userProduct,
                actor: $request->user(),
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
                metadata: [
                    'reason' => 'policy_denied',
                ],
            );

            abort(403);
        }

        $delivery = $this->resolveProductDelivery->execute($userProduct);

        $type = $delivery['type'];

        $logAction = $type === ProductType::Bundle->value
            ? AccessLogAction::BundleAccessed
            : AccessLogAction::AccessViewed;

        $this->logAccess->execute(
            action: $logAction,
            user: $request->user(),
            userProduct: $userProduct,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return view('my-products.show', [
            'userProduct' => $userProduct,
            'delivery' => $delivery,
        ]);
    }

    protected function baseUserProductsQuery(int $userId): Builder
    {
        return UserProduct::query()
            ->where('user_id', $userId)
            ->with([
                'order',
                'sourceProduct',
                'product' => function ($query): void {
                    $query
                        ->withCount('bundledProducts')
                        ->with([
                            'category',
                            'course',
                            'event',
                            'files' => fn ($fileQuery) => $fileQuery
                                ->where('is_active', true)
                                ->orderBy('sort_order')
                                ->orderBy('id'),
                            'bundledProducts' => fn ($bundleQuery) => $bundleQuery
                                ->select('products.id')
                                ->orderBy('product_bundles.sort_order'),
                        ]);
                },
            ]);
    }

    /**
     * @param  Collection<int, UserProduct>  $userProducts
     * @return array<int, array{total:int, completed:int, percent:int}>
     */
    protected function buildCourseProgressMap(int $userId, Collection $userProducts): array
    {
        $courseIds = $userProducts
            ->map(fn (UserProduct $userProduct) => $userProduct->product?->course?->id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $lessonTotalsByCourseId = empty($courseIds)
            ? collect()
            : CourseLesson::query()
                ->whereIn('course_id', $courseIds)
                ->where('is_active', true)
                ->where(function (Builder $query): void {
                    $query->whereNull('published_at')->orWhere('published_at', '<=', now());
                })
                ->selectRaw('course_id, count(*) as total')
                ->groupBy('course_id')
                ->pluck('total', 'course_id');

        $completedByCourseId = empty($courseIds)
            ? collect()
            : \App\Models\LessonProgress::query()
                ->where('user_id', $userId)
                ->whereIn('course_id', $courseIds)
                ->where('status', LessonProgressStatus::Completed)
                ->selectRaw('course_id, count(*) as completed')
                ->groupBy('course_id')
                ->pluck('completed', 'course_id');

        $progressByUserProductId = [];

        foreach ($userProducts as $userProduct) {
            $courseId = $userProduct->product?->course?->id;

            if (! $courseId) {
                $progressByUserProductId[$userProduct->id] = [
                    'total' => 0,
                    'completed' => 0,
                    'percent' => 0,
                ];

                continue;
            }

            $total = (int) ($lessonTotalsByCourseId[$courseId] ?? 0);
            $completed = (int) ($completedByCourseId[$courseId] ?? 0);
            $percent = $total === 0 ? 0 : (int) round(($completed / $total) * 100);

            $progressByUserProductId[$userProduct->id] = [
                'total' => $total,
                'completed' => $completed,
                'percent' => $percent,
            ];
        }

        return $progressByUserProductId;
    }
}

