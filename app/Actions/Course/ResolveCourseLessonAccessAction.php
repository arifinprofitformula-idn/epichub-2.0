<?php

namespace App\Actions\Course;

use App\Actions\Access\CheckProductAccessAction;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\LessonProgress;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Support\Collection;
use RuntimeException;

class ResolveCourseLessonAccessAction
{
    public function __construct(
        protected CheckProductAccessAction $checkProductAccess,
    ) {
    }

    /**
     * @return array{
     *   can_access: bool,
     *   reason: string|null,
     *   reason_label: string|null,
     *   available_from: \Illuminate\Support\Carbon|null,
     *   previous_required_lesson: CourseLesson|null
     * }
     */
    public function execute(User $user, UserProduct $userProduct, CourseLesson $lesson): array
    {
        $validatedUserProduct = $this->checkUserProduct($user, $userProduct);

        $userProduct->loadMissing('product');
        $lesson->loadMissing(['course', 'section']);

        $course = $lesson->course;

        if (! $course) {
            throw new RuntimeException('Course lesson tidak ditemukan.');
        }

        if ((int) $course->product_id !== (int) $validatedUserProduct->product_id) {
            throw new RuntimeException('Lesson tidak berada pada course entitlement ini.');
        }

        $orderedLessons = $this->sortLessons(
            CourseLesson::query()
                ->where('course_id', $course->id)
                ->accessibleToLearner()
                ->with('section')
                ->get(),
        );

        $progressRows = LessonProgress::query()
            ->forUserProduct($user->id, $validatedUserProduct->id)
            ->where('course_id', $course->id)
            ->get()
            ->keyBy('course_lesson_id');

        return $this->resolveWithinCourse(
            user: $user,
            userProduct: $validatedUserProduct,
            lesson: $lesson,
            course: $course,
            orderedLessons: $orderedLessons,
            progressRows: $progressRows,
        );
    }

    /**
     * @param  Collection<int, CourseLesson>  $orderedLessons
     * @param  Collection<int, LessonProgress>  $progressRows
     * @return array{
     *   can_access: bool,
     *   reason: string|null,
     *   reason_label: string|null,
     *   available_from: \Illuminate\Support\Carbon|null,
     *   previous_required_lesson: CourseLesson|null
     * }
     */
    public function resolveWithinCourse(
        User $user,
        UserProduct $userProduct,
        CourseLesson $lesson,
        Course $course,
        Collection $orderedLessons,
        Collection $progressRows,
    ): array {
        $validatedUserProduct = $this->checkUserProduct($user, $userProduct);

        if ((int) $course->product_id !== (int) $validatedUserProduct->product_id) {
            throw new RuntimeException('Course tidak cocok dengan entitlement.');
        }

        if ((int) $lesson->course_id !== (int) $course->id) {
            throw new RuntimeException('Lesson tidak berada pada course yang sesuai.');
        }

        if (! $course->isPublished()) {
            throw new RuntimeException('Course belum dipublikasikan.');
        }

        if (! $lesson->isPublished()) {
            throw new RuntimeException('Lesson belum dipublikasikan.');
        }

        if ($lesson->isScheduled()) {
            return [
                'can_access' => false,
                'reason' => 'scheduled',
                'reason_label' => 'Dijadwalkan',
                'available_from' => $lesson->available_from,
                'previous_required_lesson' => null,
            ];
        }

        if ($course->usesSequentialLessons()) {
            foreach ($orderedLessons as $candidate) {
                if ((int) $candidate->id === (int) $lesson->id) {
                    break;
                }

                if (! $candidate->isRequired()) {
                    continue;
                }

                if (! $this->lessonIsCompleted($progressRows->get($candidate->id))) {
                    return [
                        'can_access' => false,
                        'reason' => 'previous_required_lesson_incomplete',
                        'reason_label' => 'Terkunci',
                        'available_from' => null,
                        'previous_required_lesson' => $candidate,
                    ];
                }
            }
        }

        return [
            'can_access' => true,
            'reason' => null,
            'reason_label' => null,
            'available_from' => null,
            'previous_required_lesson' => null,
        ];
    }

    /**
     * @param  Collection<int, CourseLesson>  $lessons
     * @return Collection<int, CourseLesson>
     */
    public function sortLessons(Collection $lessons): Collection
    {
        $maxSectionOrder = $lessons
            ->filter(fn (CourseLesson $lesson): bool => $lesson->section !== null)
            ->map(fn (CourseLesson $lesson): int => (int) ($lesson->section?->sort_order ?? 0))
            ->max() ?? 0;

        return $lessons
            ->sortBy(fn (CourseLesson $lesson): array => [
                $lesson->section ? (int) ($lesson->section->sort_order ?? 0) : ($maxSectionOrder + 1),
                $lesson->section ? (int) $lesson->section->id : PHP_INT_MAX,
                (int) ($lesson->sort_order ?? 0),
                (int) $lesson->id,
            ])
            ->values();
    }

    protected function checkUserProduct(User $user, UserProduct $userProduct): UserProduct
    {
        $validatedUserProduct = $this->checkProductAccess->execute($user, $userProduct);

        if (! $validatedUserProduct) {
            throw new RuntimeException('Akses course tidak valid.');
        }

        return $validatedUserProduct;
    }

    protected function lessonIsCompleted(mixed $progress): bool
    {
        return $progress instanceof LessonProgress && $progress->isCompleted();
    }
}
