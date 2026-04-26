<?php

namespace App\Actions\Course;

use App\Actions\Access\LogAccessAction;
use App\Enums\AccessLogAction;
use App\Models\CourseLesson;
use App\Models\CourseLessonAttachment;
use App\Models\User;
use App\Models\UserProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DownloadCourseLessonAttachmentAction
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
        CourseLessonAttachment $attachment,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Response|RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse {
        $resolved = $this->resolveCourseAccess->execute($user, $userProduct);
        $course = $resolved['course'];

        $lesson = CourseLesson::query()
            ->where('id', $lesson->id)
            ->where('course_id', $course->id)
            ->accessibleToLearner()
            ->first();

        if (! $lesson) {
            throw new RuntimeException('Lesson tidak ditemukan.');
        }

        $attachment = CourseLessonAttachment::query()
            ->where('id', $attachment->id)
            ->where('course_lesson_id', $lesson->id)
            ->where('is_active', true)
            ->where('is_downloadable', true)
            ->first();

        if (! $attachment) {
            throw new RuntimeException('Resource materi tidak ditemukan.');
        }

        if ($attachment->isExternalUrl()) {
            $url = trim((string) ($attachment->external_url ?? ''));

            if (! $this->isSafeExternalUrl($url)) {
                throw new RuntimeException('Link resource tidak valid.');
            }

            $this->logAccess->execute(
                action: AccessLogAction::LessonAttachmentExternalOpened,
                user: $user,
                userProduct: $userProduct,
                actor: $user,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
                metadata: [
                    'course_id' => $course->id,
                    'course_lesson_id' => $lesson->id,
                    'course_lesson_attachment_id' => $attachment->id,
                    'attachment_title' => $attachment->title,
                    'source_type' => $attachment->source_type,
                ],
            );

            return redirect()->away($url);
        }

        if (! $attachment->isUpload()) {
            throw new RuntimeException('Tipe resource materi tidak valid.');
        }

        $disk = $attachment->disk ?: 'local';
        $path = $attachment->file_path;

        if ($path === null || trim($path) === '') {
            throw new RuntimeException('Path file materi tidak tersedia.');
        }

        if (! Storage::disk($disk)->exists($path)) {
            throw new RuntimeException('File materi tidak ditemukan.');
        }

        $filename = $this->downloadFilename($attachment, $path);

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
                'course_lesson_attachment_id' => $attachment->id,
                'attachment_title' => $attachment->title,
                'source_type' => $attachment->source_type,
            ],
        );

        return Storage::disk($disk)->download($path, $filename);
    }

    protected function isSafeExternalUrl(string $url): bool
    {
        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true);
    }

    protected function downloadFilename(CourseLessonAttachment $attachment, string $path): string
    {
        $originalName = trim((string) ($attachment->original_name ?? ''));

        if ($originalName !== '') {
            return $originalName;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $base = Str::slug((string) ($attachment->title ?: 'materi'));

        return $extension !== '' ? $base.'.'.$extension : $base;
    }
}
