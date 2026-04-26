<?php

namespace App\Http\Controllers;

use App\Actions\Access\LogAccessAction;
use App\Actions\Course\ResolveCourseAccessAction;
use App\Enums\AccessLogAction;
use App\Enums\LessonProgressStatus;
use App\Enums\ProductType;
use App\Models\CourseLesson;
use App\Models\LessonProgress;
use App\Models\UserProduct;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class MyCourseController extends Controller
{
    public function __construct(
        protected ResolveCourseAccessAction $resolveCourseAccess,
        protected LogAccessAction $logAccess,
    ) {
    }

    public function index(Request $request): View
    {
        $userProducts = UserProduct::query()
            ->where('user_id', $request->user()->id)
            ->active()
            ->whereHas('product', fn ($q) => $q->where('product_type', ProductType::Course->value))
            ->with(['product', 'product.course'])
            ->latest('granted_at')
            ->paginate(12);

        $courseIds = $userProducts
            ->getCollection()
            ->map(fn (UserProduct $up) => $up->product?->course?->id)
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
                ->where('user_id', $request->user()->id)
                ->whereIn('course_id', $courseIds)
                ->completed()
                ->selectRaw('course_id, count(*) as completed')
                ->groupBy('course_id')
                ->pluck('completed', 'course_id');

        $progressByUserProductId = [];

        foreach ($userProducts->getCollection() as $up) {
            $courseId = $up->product?->course?->id;

            if (! $courseId) {
                $progressByUserProductId[$up->id] = [
                    'total' => 0,
                    'completed' => 0,
                    'percent' => 0,
                ];
                continue;
            }

            $total = (int) ($lessonTotalsByCourseId[$courseId] ?? 0);
            $completed = (int) ($completedByCourseId[$courseId] ?? 0);
            $percent = $total === 0 ? 0 : (int) round(($completed / $total) * 100);

            $progressByUserProductId[$up->id] = [
                'total' => $total,
                'completed' => $completed,
                'percent' => $percent,
            ];
        }

        return view('my-courses.index', [
            'userProducts' => $userProducts,
            'progressByUserProductId' => $progressByUserProductId,
        ]);
    }

    public function show(Request $request, UserProduct $userProduct): View
    {
        try {
            $resolved = $this->resolveCourseAccess->execute($request->user(), $userProduct);
        } catch (RuntimeException $e) {
            $this->logAccess->execute(
                action: AccessLogAction::AccessDenied,
                user: $request->user(),
                userProduct: $userProduct,
                actor: $request->user(),
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
                metadata: [
                    'reason' => 'course_access_denied',
                    'message' => $e->getMessage(),
                ],
            );

            abort(404);
        }

        $this->logAccess->execute(
            action: AccessLogAction::CourseAccessed,
            user: $request->user(),
            userProduct: $userProduct,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            metadata: [
                'course_id' => $resolved['course']->id,
            ],
        );

        return view('my-courses.show', $resolved);
    }
}

