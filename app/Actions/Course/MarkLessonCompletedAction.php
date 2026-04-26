<?php

namespace App\Actions\Course;

use App\Actions\Access\LogAccessAction;
use App\Enums\AccessLogAction;
use App\Enums\LessonProgressStatus;
use App\Models\CourseLesson;
use App\Models\LessonProgress;
use App\Models\User;
use App\Models\UserProduct;
use RuntimeException;

class MarkLessonCompletedAction
{
    public function __construct(
        protected ResolveCourseAccessAction $resolveCourseAccess,
        protected ResolveCourseLessonAccessAction $resolveCourseLessonAccess,
        protected LogAccessAction $logAccess,
    ) {
    }

    public function execute(User $user, UserProduct $userProduct, CourseLesson $lesson, ?string $ipAddress = null, ?string $userAgent = null): LessonProgress
    {
        $resolved = $this->resolveCourseAccess->execute($user, $userProduct);
        $course = $resolved['course'];

        if (! $lesson) {
            throw new RuntimeException('Lesson tidak ditemukan.');
        }

        $access = $this->resolveCourseLessonAccess->resolveWithinCourse(
            user: $user,
            userProduct: $userProduct,
            lesson: $lesson,
            course: $course,
            orderedLessons: $resolved['lessons'],
            progressRows: $resolved['progressRowsByLessonId'],
        );

        if (! $access['can_access']) {
            throw new RuntimeException('Lesson belum dapat diakses.');
        }

        $now = now();

        $progress = LessonProgress::query()->firstOrNew([
            'user_id' => $user->id,
            'course_lesson_id' => $lesson->id,
        ]);

        $wasCompleted = $progress->exists && $progress->isCompleted();

        $progress->fill([
            'course_id' => $course->id,
            'user_product_id' => $userProduct->id,
            'status' => LessonProgressStatus::Completed,
            'completed_at' => $progress->completed_at ?? $now,
            'last_viewed_at' => $now,
        ]);
        $progress->save();

        if (! $wasCompleted) {
            $this->logAccess->execute(
                action: AccessLogAction::LessonCompleted,
                user: $user,
                userProduct: $userProduct,
                actor: $user,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
                metadata: [
                    'course_id' => $course->id,
                    'course_lesson_id' => $lesson->id,
                    'lesson_title' => $lesson->title,
                ],
            );
        }

        return $progress->refresh();
    }
}

