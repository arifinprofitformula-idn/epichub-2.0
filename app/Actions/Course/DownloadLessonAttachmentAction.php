<?php

namespace App\Actions\Course;

use App\Actions\Access\LogAccessAction;
use App\Enums\AccessLogAction;
use App\Enums\CourseLessonType;
use App\Models\CourseLesson;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadLessonAttachmentAction
{
    public function __construct(
        protected ResolveCourseAccessAction $resolveCourseAccess,
        protected LogAccessAction $logAccess,
    ) {
    }

    public function execute(
        User $user,
        UserProduct $userProduct,
        CourseLesson $lesson,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): StreamedResponse {
        $resolved = $this->resolveCourseAccess->execute($user, $userProduct);
        $course = $resolved['course'];

        $lesson = CourseLesson::query()
            ->where('id', $lesson->id)
            ->where('course_id', $course->id)
            ->where('is_active', true)
            ->where(function (Builder $q): void {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->first();

        if (! $lesson) {
            throw new RuntimeException('Lesson tidak ditemukan.');
        }

        if ($lesson->lesson_type !== CourseLessonType::FileAttachment) {
            throw new RuntimeException('Lesson tidak memiliki attachment.');
        }

        $path = $lesson->attachment_path;

        if ($path === null || trim($path) === '') {
            throw new RuntimeException('Attachment tidak tersedia.');
        }

        if (! Storage::disk('local')->exists($path)) {
            throw new RuntimeException('File attachment tidak ditemukan.');
        }

        $this->logAccess->execute(
            action: AccessLogAction::LessonAttachmentDownloaded,
            user: $user,
            userProduct: $userProduct,
            actor: $user,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            metadata: [
                'course_id' => $course->id,
                'course_lesson_id' => $lesson->id,
                'lesson_title' => $lesson->title,
                'attachment_path' => $path,
                'disk' => 'local',
            ],
        );

        return Storage::disk('local')->download($path);
    }
}

