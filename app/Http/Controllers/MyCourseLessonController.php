<?php

namespace App\Http\Controllers;

use App\Actions\Access\LogAccessAction;
use App\Actions\Course\DownloadCourseLessonAttachmentAction;
use App\Actions\Course\DownloadLessonAttachmentAction;
use App\Actions\Course\MarkLessonCompletedAction;
use App\Actions\Course\ResolveCourseAccessAction;
use App\Actions\Course\ResolveCourseLessonAccessAction;
use App\Enums\AccessLogAction;
use App\Enums\CourseLessonType;
use App\Enums\LessonProgressStatus;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseLessonAttachment;
use App\Models\LessonProgress;
use App\Models\UserProduct;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MyCourseLessonController extends Controller
{
    public function __construct(
        protected ResolveCourseAccessAction $resolveCourseAccess,
        protected ResolveCourseLessonAccessAction $resolveCourseLessonAccess,
        protected MarkLessonCompletedAction $markLessonCompleted,
        protected DownloadLessonAttachmentAction $downloadLessonAttachment,
        protected DownloadCourseLessonAttachmentAction $downloadCourseLessonAttachment,
        protected LogAccessAction $logAccess,
    ) {}

    public function show(Request $request, UserProduct $userProduct, CourseLesson $courseLesson): View|RedirectResponse
    {
        try {
            $lessonAccess = $this->resolveCourseLessonAccess->execute($request->user(), $userProduct, $courseLesson);
        } catch (RuntimeException $e) {
            $this->logDenied($request, $userProduct, $courseLesson, 'course_access_denied', $e->getMessage());
            abort(404);
        }

        if (! $lessonAccess['can_access']) {
            return $this->redirectLockedLesson($userProduct, $lessonAccess);
        }

        try {
            $resolved = $this->resolveCourseAccess->execute($request->user(), $userProduct);
            $course = $resolved['course'];
        } catch (RuntimeException $e) {
            $this->logDenied($request, $userProduct, $courseLesson, 'course_access_denied', $e->getMessage());
            abort(404);
        }

        $lesson = CourseLesson::query()
            ->where('id', $courseLesson->id)
            ->where('course_id', $course->id)
            ->with([
                'section',
                'attachments' => fn ($q) => $q
                    ->where('is_active', true)
                    ->where('is_downloadable', true)
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->first();

        if (! $lesson) {
            $this->logDenied($request, $userProduct, $courseLesson, 'lesson_not_accessible', null);
            abort(404);
        }

        $progress = LessonProgress::query()->firstOrNew([
            'user_id' => $request->user()->id,
            'course_lesson_id' => $lesson->id,
        ]);

        $progress->fill([
            'course_id' => $course->id,
            'user_product_id' => $userProduct->id,
            'status' => $progress->isCompleted() ? LessonProgressStatus::Completed : LessonProgressStatus::InProgress,
            'completed_at' => $progress->completed_at,
            'last_viewed_at' => now(),
        ]);
        $progress->save();

        $resolved['progressRowsByLessonId']->put($lesson->id, $progress->fresh());
        $resolved['progressByLessonId'][$lesson->id] = $progress->isCompleted()
            ? LessonProgressStatus::Completed
            : LessonProgressStatus::InProgress;

        $this->logAccess->execute(
            action: AccessLogAction::LessonViewed,
            user: $request->user(),
            userProduct: $userProduct,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            metadata: [
                'course_id' => $course->id,
                'course_lesson_id' => $lesson->id,
                'lesson_title' => $lesson->title,
                'lesson_type' => $lesson->lesson_type?->value ?? null,
            ],
        );

        if ($lesson->hasVideo() && $lesson->isYoutubeVideo() && filled($lesson->video_id)) {
            $this->logAccess->execute(
                action: AccessLogAction::LessonVideoViewed,
                user: $request->user(),
                userProduct: $userProduct,
                actor: $request->user(),
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
                metadata: array_filter([
                    'course_lesson_id' => $lesson->id,
                    'user_product_id' => $userProduct->id,
                    'video_provider' => $lesson->video_provider ?? 'youtube',
                    'video_id' => $lesson->video_id,
                ], fn ($v) => $v !== null && $v !== ''),
            );
        }

        $currentLessonIndex = $resolved['lessons']->search(fn (CourseLesson $row) => $row->id === $lesson->id);
        $currentProgressStatus = $resolved['progressByLessonId'][$lesson->id] ?? LessonProgressStatus::InProgress;
        $navigationLessons = $this->navigableLessons($resolved['visibleLessons'], $resolved['lessonAccessByLessonId']);
        $navIndex = $navigationLessons->search(fn (CourseLesson $row) => $row->id === $lesson->id);
        $prevLessonId = $navIndex !== false && $navIndex > 0 ? $navigationLessons->get($navIndex - 1)?->id : null;
        $nextLessonId = $navIndex !== false ? $navigationLessons->get($navIndex + 1)?->id : null;
        $downloadableAttachments = $lesson->attachments
            ->map(fn (CourseLessonAttachment $attachment) => [
                'title' => $attachment->title,
                'description' => $attachment->description,
                'size' => $attachment->size,
                'size_label' => $attachment->size ? number_format($attachment->size / 1024, 1).' KB' : null,
                'url' => route('my-courses.lessons.attachments.download', [$userProduct, $lesson, $attachment]),
                'button_label' => $attachment->display_button_label,
                'badge_label' => $attachment->isExternalUrl() ? 'Link Eksternal' : 'File',
                'open_in_new_tab' => $attachment->isExternalUrl() ? (bool) $attachment->open_in_new_tab : false,
                'is_legacy' => false,
            ])
            ->values();

        if (filled($lesson->attachment_path)) {
            $legacySize = null;

            try {
                if (Storage::disk('local')->exists($lesson->attachment_path)) {
                    $legacySize = Storage::disk('local')->size($lesson->attachment_path);
                }
            } catch (\Throwable) {
                $legacySize = null;
            }

            $downloadableAttachments->prepend([
                'title' => $lesson->short_description ?: 'Attachment lesson',
                'description' => 'File attachment dari lesson ini.',
                'size' => $legacySize,
                'size_label' => $legacySize ? number_format($legacySize / 1024, 1).' KB' : null,
                'url' => route('my-courses.lessons.download', [$userProduct, $lesson]),
                'button_label' => 'Download File',
                'badge_label' => 'File',
                'open_in_new_tab' => false,
                'is_legacy' => true,
            ]);
        }

        return view('my-courses.lesson', [
            'userProduct' => $userProduct,
            'course' => $course,
            'lesson' => $lesson,
            'sections' => $resolved['sections'],
            'lessons' => $resolved['visibleLessons'],
            'progressByLessonId' => $resolved['progressByLessonId'],
            'lessonAccessByLessonId' => $resolved['lessonAccessByLessonId'],
            'progressPercent' => $resolved['progressPercent'],
            'completedLessons' => $resolved['completedLessons'],
            'totalLessons' => $resolved['totalLessons'],
            'prevLessonId' => $prevLessonId,
            'nextLessonId' => $nextLessonId,
            'currentLessonIndex' => $currentLessonIndex === false ? 0 : ($currentLessonIndex + 1),
            'currentProgressStatus' => $currentProgressStatus,
            'downloadableAttachments' => $downloadableAttachments,
        ]);
    }

    public function complete(Request $request, UserProduct $userProduct, CourseLesson $courseLesson): RedirectResponse
    {
        try {
            $lessonAccess = $this->resolveCourseLessonAccess->execute($request->user(), $userProduct, $courseLesson);
        } catch (RuntimeException $e) {
            $this->logDenied($request, $userProduct, $courseLesson, 'lesson_complete_denied', $e->getMessage());
            abort(404);
        }

        if (! $lessonAccess['can_access']) {
            return $this->redirectLockedLesson($userProduct, $lessonAccess);
        }

        try {
            $this->markLessonCompleted->execute(
                user: $request->user(),
                userProduct: $userProduct,
                lesson: $courseLesson,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            $resolved = $this->resolveCourseAccess->execute($request->user(), $userProduct);
        } catch (RuntimeException $e) {
            $this->logDenied($request, $userProduct, $courseLesson, 'lesson_complete_denied', $e->getMessage());
            abort(404);
        }

        $currentIndex = $resolved['lessons']->search(fn (CourseLesson $lesson) => $lesson->id === $courseLesson->id);
        $nextLessons = $currentIndex === false
            ? collect()
            : $resolved['lessons']->slice($currentIndex + 1)->values();

        $nextAccessibleLesson = $nextLessons->first(function (CourseLesson $lesson) use ($resolved): bool {
            return (bool) ($resolved['lessonAccessByLessonId'][$lesson->id]['can_access'] ?? false);
        });

        if ($nextAccessibleLesson) {
            return redirect()->route('my-courses.lessons.show', [$userProduct, $nextAccessibleLesson]);
        }

        $firstBlockedLesson = $nextLessons->first(fn (CourseLesson $lesson): bool => isset($resolved['lessonAccessByLessonId'][$lesson->id]));

        if ($firstBlockedLesson) {
            $blockedAccess = $resolved['lessonAccessByLessonId'][$firstBlockedLesson->id];

            if (($blockedAccess['reason'] ?? null) === 'scheduled') {
                return redirect()
                    ->route('my-courses.show', $userProduct)
                    ->with('status', $blockedAccess['message'] ?? 'Materi berikutnya belum dibuka.')
                    ->with('status_title', 'Materi Dijadwalkan')
                    ->with('status_variant', 'warning');
            }
        }

        return redirect()
            ->route('my-courses.show', $userProduct)
            ->with('status', 'Semua materi di kelas ini sudah selesai.')
            ->with('status_title', 'Progress Tersimpan')
            ->with('status_variant', 'info');
    }

    public function download(Request $request, UserProduct $userProduct, CourseLesson $courseLesson): StreamedResponse
    {
        try {
            return $this->downloadLessonAttachment->execute(
                user: $request->user(),
                userProduct: $userProduct,
                lesson: $courseLesson,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );
        } catch (RuntimeException $e) {
            $this->logDenied($request, $userProduct, $courseLesson, 'lesson_attachment_denied', $e->getMessage());
            abort(404);
        }
    }

    public function openExternal(Request $request, UserProduct $userProduct, CourseLesson $courseLesson): RedirectResponse
    {
        try {
            $lessonAccess = $this->resolveCourseLessonAccess->execute($request->user(), $userProduct, $courseLesson);
        } catch (RuntimeException $e) {
            $this->logDenied($request, $userProduct, $courseLesson, 'course_access_denied', $e->getMessage());
            abort(404);
        }

        if (! $lessonAccess['can_access']) {
            return $this->redirectLockedLesson($userProduct, $lessonAccess);
        }

        $courseLesson->loadMissing('course');
        $course = $courseLesson->course;

        if (! $course instanceof Course) {
            $this->logDenied($request, $userProduct, $courseLesson, 'course_not_found', null);
            abort(404);
        }

        $lesson = CourseLesson::query()
            ->where('id', $courseLesson->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $lesson) {
            $this->logDenied($request, $userProduct, $courseLesson, 'lesson_not_accessible', null);
            abort(404);
        }

        if ($lesson->lesson_type !== CourseLessonType::ExternalLink) {
            $this->logDenied($request, $userProduct, $courseLesson, 'lesson_not_external_link', null);
            abort(404);
        }

        $url = $lesson->external_url;

        if ($url === null || trim($url) === '') {
            $this->logDenied($request, $userProduct, $courseLesson, 'external_url_missing', null);
            abort(404);
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            $this->logDenied($request, $userProduct, $courseLesson, 'external_url_invalid', null);
            abort(404);
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (! in_array($scheme, ['http', 'https'], true)) {
            $this->logDenied($request, $userProduct, $courseLesson, 'external_url_scheme_not_allowed', null);
            abort(404);
        }

        $this->logAccess->execute(
            action: AccessLogAction::LessonViewed,
            user: $request->user(),
            userProduct: $userProduct,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            metadata: [
                'course_id' => $course->id,
                'course_lesson_id' => $lesson->id,
                'lesson_title' => $lesson->title,
                'lesson_type' => $lesson->lesson_type?->value ?? null,
                'delivery_type' => 'external_link',
            ],
        );

        return redirect()->away($url);
    }

    public function downloadAttachment(Request $request, UserProduct $userProduct, CourseLesson $courseLesson, CourseLessonAttachment $attachment): Response|RedirectResponse|StreamedResponse
    {
        try {
            return $this->downloadCourseLessonAttachment->execute(
                user: $request->user(),
                userProduct: $userProduct,
                lesson: $courseLesson,
                attachment: $attachment,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );
        } catch (RuntimeException $e) {
            $this->logDenied($request, $userProduct, $courseLesson, 'lesson_attachment_item_denied', $e->getMessage());
            abort(404);
        }
    }

    /**
     * @param  array{reason: string|null, message?: string|null, available_from: Carbon|null}  $lessonAccess
     */
    protected function redirectLockedLesson(UserProduct $userProduct, array $lessonAccess): RedirectResponse
    {
        return redirect()
            ->route('my-courses.show', $userProduct)
            ->with('status', $lessonAccess['message'] ?? $this->lockedLessonMessage($lessonAccess))
            ->with('status_title', ($lessonAccess['reason'] ?? null) === 'scheduled' ? 'Materi Dijadwalkan' : 'Materi Terkunci')
            ->with('status_variant', 'warning');
    }

    /**
     * @param  array{reason: string|null, available_from: Carbon|null}  $lessonAccess
     */
    protected function lockedLessonMessage(array $lessonAccess): string
    {
        return match ($lessonAccess['reason'] ?? null) {
            'scheduled' => $lessonAccess['available_from']
                ? 'Materi ini dibuka pada '.$lessonAccess['available_from']->timezone('Asia/Jakarta')->format('d M Y H:i').'.'
                : 'Materi ini belum dibuka.',
            'previous_required_lesson_incomplete' => 'Selesaikan materi sebelumnya terlebih dahulu.',
            default => 'Materi ini belum dapat diakses.',
        };
    }

    /**
     * @param  Collection<int, CourseLesson>  $lessons
     * @param  array<int, array<string, mixed>>  $lessonAccessByLessonId
     * @return Collection<int, CourseLesson>
     */
    protected function navigableLessons(Collection $lessons, array $lessonAccessByLessonId): Collection
    {
        return $lessons
            ->filter(fn (CourseLesson $lesson): bool => (bool) ($lessonAccessByLessonId[$lesson->id]['can_access'] ?? false))
            ->values();
    }

    protected function logDenied(Request $request, UserProduct $userProduct, CourseLesson $lesson, string $reason, ?string $message): void
    {
        $this->logAccess->execute(
            action: AccessLogAction::AccessDenied,
            user: $request->user(),
            userProduct: $userProduct,
            actor: $request->user(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            metadata: array_filter([
                'reason' => $reason,
                'message' => $message,
                'course_lesson_id' => $lesson->id,
                'lesson_title' => $lesson->title,
            ], fn ($v) => $v !== null && $v !== ''),
        );
    }
}
