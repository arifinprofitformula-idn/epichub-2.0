<?php

namespace App\Http\Controllers;

use App\Enums\LessonProgressStatus;
use App\Enums\ProductType;
use App\Models\CourseLesson;
use App\Models\EventRegistration;
use App\Models\LessonProgress;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\UserProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $ownedUserProducts = UserProduct::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->active()
            ->with('product')
            ->get()
            ->keyBy('product_id');

        $ownedProductIds = $ownedUserProducts->keys()->map(fn ($id) => (int) $id)->all();

        $query = Product::query()
            ->published()
            ->visiblePublic()
            ->with([
                'category',
                'course',
                'event' => fn ($eventQuery) => $eventQuery->withCount('activeRegistrations'),
            ])
            ->withCount('bundledProducts')
            ->orderByDesc('is_featured')
            ->orderByDesc('publish_at')
            ->orderBy('sort_order');

        $q = trim((string) $request->string('q'));
        if ($q !== '') {
            $query->where(function (Builder $builder) use ($q): void {
                $builder
                    ->where('title', 'like', "%{$q}%")
                    ->orWhere('short_description', 'like', "%{$q}%")
                    ->orWhere('full_description', 'like', "%{$q}%");
            });
        }

        $category = trim((string) $request->string('category'));
        if ($category !== '') {
            $query->whereHas('category', fn (Builder $builder) => $builder->where('slug', $category));
        }

        $productType = trim((string) $request->string('product_type'));
        if ($productType !== '') {
            $query->where('product_type', $productType);
        }

        $ownership = trim((string) $request->string('ownership', 'all'));
        if ($ownership === 'owned') {
            $ownedProductIds === []
                ? $query->whereRaw('1 = 0')
                : $query->whereIn('id', $ownedProductIds);
        } elseif ($ownership === 'not_owned' && $ownedProductIds !== []) {
            $query->whereNotIn('id', $ownedProductIds);
        }

        $products = $query->paginate(12)->withQueryString();

        $categories = ProductCategory::query()
            ->where('is_active', true)
            ->whereHas('products', fn (Builder $builder) => $builder->published()->visiblePublic())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $productTypes = collect(ProductType::cases())
            ->map(fn (ProductType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ]);

        $eventRegistrationsByProductId = EventRegistration::query()
            ->where('user_id', $user->id)
            ->active()
            ->with('event')
            ->get()
            ->filter(fn (EventRegistration $registration) => $registration->event?->product_id !== null)
            ->keyBy(fn (EventRegistration $registration) => $registration->event->product_id);

        $progressByUserProductId = $this->buildCourseProgressMap(
            userId: $user->id,
            userProducts: $ownedUserProducts
                ->filter(fn (UserProduct $userProduct) => $userProduct->product?->product_type === ProductType::Course)
                ->values(),
        );

        return view('marketplace.index', [
            'products' => $products,
            'categories' => $categories,
            'productTypes' => $productTypes,
            'activeFilters' => [
                'q' => $q,
                'category' => $category,
                'product_type' => $productType,
                'ownership' => in_array($ownership, ['all', 'owned', 'not_owned'], true) ? $ownership : 'all',
            ],
            'ownedUserProducts' => $ownedUserProducts,
            'eventRegistrationsByProductId' => $eventRegistrationsByProductId,
            'progressByUserProductId' => $progressByUserProductId,
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
            : LessonProgress::query()
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
