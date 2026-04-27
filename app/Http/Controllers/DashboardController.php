<?php

namespace App\Http\Controllers;

use App\Enums\LessonProgressStatus;
use App\Enums\ProductType;
use App\Models\CourseLesson;
use App\Models\EventRegistration;
use App\Models\LessonProgress;
use App\Models\Product;
use App\Models\UserProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController
{
    public function __invoke(Request $request): View
    {
        $request->user()->loadMissing(['epiChannel']);

        $activeUserProductsCount = UserProduct::query()
            ->where('user_id', $request->user()->id)
            ->active()
            ->count();

        $activeCoursesCount = UserProduct::query()
            ->where('user_id', $request->user()->id)
            ->active()
            ->whereHas('product', fn ($q) => $q->where('product_type', ProductType::Course->value))
            ->count();

        $activeCourseUserProducts = UserProduct::query()
            ->where('user_id', $request->user()->id)
            ->active()
            ->whereHas('product', fn ($q) => $q->where('product_type', ProductType::Course->value))
            ->with(['product', 'product.course'])
            ->latest('granted_at')
            ->get();

        $progressByUserProductId = $this->buildCourseProgressMap(
            userId: $request->user()->id,
            userProducts: $activeCourseUserProducts,
        );

        $catalogCourses = Product::query()
            ->published()
            ->visiblePublic()
            ->where('product_type', ProductType::Course)
            ->whereHas('course', fn (Builder $query) => $query->published())
            ->whereDoesntHave('userProducts', fn (Builder $query) => $query->where('user_id', $request->user()->id))
            ->with(['category', 'course'])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('publish_at')
            ->limit(6)
            ->get()
            ->map(fn (Product $product): array => [
                'product' => $product,
            ]);

        $activeEventsCount = EventRegistration::query()
            ->where('user_id', $request->user()->id)
            ->active()
            ->count();

        $channel = $request->user()->epiChannel;
        $epiChannelStatus = $channel
            ? match ($channel->status->value) {
                'active' => 'Aktif',
                'prospect' => 'Prospect',
                'qualified' => 'Qualified',
                'suspended' => 'Suspended',
                'inactive' => 'Tidak aktif',
                default => 'Belum aktif',
            }
            : 'Belum aktif';
        $epiChannelDescription = $channel && $channel->isActive()
            ? 'Dashboard penghasilan tersedia'
            : 'Aktivasi melalui OMS/Admin';

        return view('dashboard', [
            'activeUserProductsCount' => $activeUserProductsCount,
            'activeCoursesCount' => $activeCoursesCount,
            'activeEventsCount' => $activeEventsCount,
            'activeCourseUserProducts' => $activeCourseUserProducts,
            'progressByUserProductId' => $progressByUserProductId,
            'catalogCourses' => $catalogCourses,
            'epiChannel' => $channel,
            'epiChannelStatus' => $epiChannelStatus,
            'epiChannelDescription' => $epiChannelDescription,
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
                ->accessibleToLearner()
                ->selectRaw('course_id, count(*) as total')
                ->groupBy('course_id')
                ->pluck('total', 'course_id');

        $completedByCourseId = empty($courseIds)
            ? collect()
            : LessonProgress::query()
                ->where('user_id', $userId)
                ->whereIn('course_id', $courseIds)
                ->completed()
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

