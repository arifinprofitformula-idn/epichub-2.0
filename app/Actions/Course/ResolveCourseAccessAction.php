<?php

namespace App\Actions\Course;

use App\Actions\Access\CheckProductAccessAction;
use App\Enums\LessonProgressStatus;
use App\Enums\ProductType;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\LessonProgress;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Support\Collection;
use RuntimeException;

class ResolveCourseAccessAction
{
    public function __construct(
        protected CheckProductAccessAction $checkProductAccess,
        protected ResolveCourseLessonAccessAction $resolveCourseLessonAccess,
    ) {
    }

    /**
     * @return array{
     *   userProduct: UserProduct,
     *   course: Course,
     *   lessons: \Illuminate\Support\Collection<int, CourseLesson>,
     *   visibleLessons: \Illuminate\Support\Collection<int, CourseLesson>,
     *   sections: \Illuminate\Support\Collection<int, \App\Models\CourseSection>,
     *   progressRowsByLessonId: \Illuminate\Support\Collection<int, LessonProgress>,
     *   progressByLessonId: array<int, \App\Enums\LessonProgressStatus>,
     *   lessonAccessByLessonId: array<int, array<string, mixed>>,
     *   totalLessons: int,
     *   completedLessons: int,
     *   progressPercent: int,
     *   continueLesson: CourseLesson|null
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
            ->first();

        if (! $course) {
            throw new RuntimeException('Materi kelas sedang disiapkan.');
        }

        if (! $course->isPublished()) {
            throw new RuntimeException('Kelas belum dipublikasikan.');
        }

        $course->loadMissing([
            'sections' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('id'),
        ]);

        $lessons = $this->resolveCourseLessonAccess->sortLessons(
            CourseLesson::query()
                ->where('course_id', $course->id)
                ->accessibleToLearner()
                ->with('section')
                ->get(),
        );

        $progress = LessonProgress::query()
            ->forUserProduct($user->id, $validatedUserProduct->id)
            ->where('course_id', $course->id)
            ->get()
            ->keyBy('course_lesson_id');

        $progressByLessonId = [];
        $lessonAccessByLessonId = [];

        foreach ($progress as $lessonId => $row) {
            $progressByLessonId[(int) $lessonId] = $row->isCompleted()
                ? LessonProgressStatus::Completed
                : ($row->status ?? LessonProgressStatus::InProgress);
        }

        foreach ($lessons as $lesson) {
            $access = $this->resolveCourseLessonAccess->resolveWithinCourse(
                user: $user,
                userProduct: $validatedUserProduct,
                lesson: $lesson,
                course: $course,
                orderedLessons: $lessons,
                progressRows: $progress,
            );

            $progressStatus = $progressByLessonId[$lesson->id] ?? null;
            $isCompleted = $progressStatus === LessonProgressStatus::Completed;

            $lessonAccessByLessonId[$lesson->id] = [
                ...$access,
                'state' => $isCompleted
                    ? 'completed'
                    : match ($access['reason']) {
                        'scheduled' => 'scheduled',
                        'previous_required_lesson_incomplete' => 'locked',
                        default => 'open',
                    },
                'status_label' => $isCompleted
                    ? 'Selesai'
                    : match ($access['reason']) {
                        'scheduled' => 'Dijadwalkan',
                        'previous_required_lesson_incomplete' => 'Terkunci',
                        default => 'Terbuka',
                    },
                'message' => $this->buildLessonMessage($access),
                'is_visible' => $isCompleted || $access['can_access'] || $course->shouldShowLockedLessons(),
            ];
        }

        $totalLessons = $lessons->count();
        $completedLessons = $progress->filter(fn (LessonProgress $row): bool => $row->isCompleted())->count();
        $progressPercent = $totalLessons === 0 ? 0 : (int) round(($completedLessons / $totalLessons) * 100);
        $visibleLessons = $lessons
            ->filter(fn (CourseLesson $lesson): bool => (bool) ($lessonAccessByLessonId[$lesson->id]['is_visible'] ?? false))
            ->values();
        $continueLesson = $visibleLessons->first(function (CourseLesson $lesson) use ($progressByLessonId, $lessonAccessByLessonId): bool {
            return ($progressByLessonId[$lesson->id] ?? null) !== LessonProgressStatus::Completed
                && (bool) ($lessonAccessByLessonId[$lesson->id]['can_access'] ?? false);
        });

        return [
            'userProduct' => $validatedUserProduct,
            'course' => $course,
            'lessons' => $lessons,
            'visibleLessons' => $visibleLessons,
            'sections' => $course->sections,
            'progressRowsByLessonId' => $progress,
            'progressByLessonId' => $progressByLessonId,
            'lessonAccessByLessonId' => $lessonAccessByLessonId,
            'totalLessons' => $totalLessons,
            'completedLessons' => $completedLessons,
            'progressPercent' => $progressPercent,
            'continueLesson' => $continueLesson,
        ];
    }

    /**
     * @param  array{reason: string|null, available_from: \Illuminate\Support\Carbon|null}  $access
     */
    protected function buildLessonMessage(array $access): ?string
    {
        return match ($access['reason']) {
            'scheduled' => $access['available_from']
                ? 'Materi ini dibuka pada '.$access['available_from']->timezone('Asia/Jakarta')->format('d M Y H:i').'.'
                : 'Materi ini dijadwalkan untuk dibuka nanti.',
            'previous_required_lesson_incomplete' => 'Selesaikan materi sebelumnya terlebih dahulu.',
            default => null,
        };
    }
}

