<?php

namespace App\Actions\Course;

use App\Actions\Access\CheckProductAccessAction;
use App\Enums\ProductType;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\LessonProgress;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

class ResolveCourseAccessAction
{
    public function __construct(
        protected CheckProductAccessAction $checkProductAccess,
    ) {
    }

    /**
     * @return array{
     *   userProduct: UserProduct,
     *   course: Course,
     *   lessons: \Illuminate\Support\Collection<int, CourseLesson>,
     *   sections: \Illuminate\Support\Collection<int, \App\Models\CourseSection>,
     *   progressByLessonId: array<int, \App\Enums\LessonProgressStatus>,
     *   totalLessons: int,
     *   completedLessons: int,
     *   progressPercent: int
     * }
     */
    public function execute(User $user, UserProduct $userProduct): array
    {
        $validatedUserProduct = $this->checkProductAccess->execute($user, $userProduct);

        if (! $validatedUserProduct) {
            throw new RuntimeException('Akses tidak valid.');
        }

        $userProduct->loadMissing(['product']);

        $product = $userProduct->product;

        if (! $product) {
            throw new RuntimeException('Produk tidak ditemukan.');
        }

        $productType = $product->product_type instanceof ProductType
            ? $product->product_type->value
            : (string) $product->product_type;

        if ($productType !== ProductType::Course->value) {
            throw new RuntimeException('Produk bukan course.');
        }

        $course = Course::query()
            ->where('product_id', $product->id)
            ->published()
            ->first();

        if (! $course) {
            throw new RuntimeException('Materi kelas sedang disiapkan.');
        }

        $course->loadMissing([
            'sections' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
        ]);

        $lessons = CourseLesson::query()
            ->where('course_id', $course->id)
            ->where('is_active', true)
            ->where(function (Builder $q): void {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->orderBy('sort_order')
            ->get();

        $progress = LessonProgress::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->get()
            ->keyBy('course_lesson_id');

        $progressByLessonId = [];

        foreach ($progress as $lessonId => $row) {
            $progressByLessonId[(int) $lessonId] = $row->status;
        }

        $totalLessons = $lessons->count();
        $completedLessons = $progress->where('status', \App\Enums\LessonProgressStatus::Completed)->count();
        $progressPercent = $totalLessons === 0 ? 0 : (int) round(($completedLessons / $totalLessons) * 100);

        return [
            'userProduct' => $userProduct,
            'course' => $course,
            'lessons' => $lessons,
            'sections' => $course->sections,
            'progressByLessonId' => $progressByLessonId,
            'totalLessons' => $totalLessons,
            'completedLessons' => $completedLessons,
            'progressPercent' => $progressPercent,
        ];
    }
}

