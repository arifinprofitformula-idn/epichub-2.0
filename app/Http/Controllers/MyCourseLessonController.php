<?php

namespace App\Http\Controllers;

use App\Actions\Access\LogAccessAction;
use App\Actions\Course\DownloadCourseLessonAttachmentAction;
use App\Actions\Course\DownloadLessonAttachmentAction;
use App\Actions\Course\MarkLessonCompletedAction;
use App\Actions\Course\ResolveCourseAccessAction;
use App\Enums\AccessLogAction;
use App\Enums\CourseLessonType;
use App\Enums\LessonProgressStatus;
use App\Models\CourseLesson;
use App\Models\CourseLessonAttachment;
use App\Models\LessonProgress;
use App\Models\UserProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MyCourseLessonController extends Controller
{
    public function __construct(
        protected ResolveCourseAccessAction $resolveCourseAccess,
        protected MarkLessonCompletedAction $markLessonCompleted,
        protected DownloadLessonAttachmentAction $downloadLessonAttachment,
        protected DownloadCourseLessonAttachmentAction $downloadCourseLessonAttachment,
        protected LogAccessAction $logAccess,
    ) {
    }

    public function show(Request $request, UserProduct $userProduct, CourseLesson $courseLesson): \Illuminate\View\View
    {
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
            ->accessibleToLearner()
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

        LessonProgress::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'course_lesson_id' => $lesson->id,
            ],
            [
                'course_id' => $course->id,
                'user_product_id' => $userProduct->id,
                'status' => LessonProgressStatus::InProgress,
                'last_viewed_at' => now(),
            ],
        );

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

        $nextLessonId = $resolved['lessons']->firstWhere('sort_order', '>', $lesson->sort_order)?->id;
        $prevLessonId = $resolved['lessons']->where('sort_order', '<', $lesson->sort_order)->sortByDesc('sort_order')->first()?->id;
        $currentLessonIndex = $resolved['lessons']->search(fn (CourseLesson $row) => $row->id === $lesson->id);
        $currentProgressStatus = $resolved['progressByLessonId'][$lesson->id] ?? LessonProgressStatus::InProgress;
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
                if (\Illuminate\Support\Facades\Storage::disk('local')->exists($lesson->attachment_path)) {
                    $legacySize = \Illuminate\Support\Facades\Storage::disk('local')->size($lesson->attachment_path);
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
            'lessons' => $resolved['lessons'],
            'progressByLessonId' => $resolved['progressByLessonId'],
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
            $this->markLessonCompleted->execute(
                user: $request->user(),
                userProduct: $userProduct,
                lesson: $courseLesson,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );
        } catch (RuntimeException $e) {
            $this->logDenied($request, $userProduct, $courseLesson, 'lesson_complete_denied', $e->getMessage());
            abort(404);
        }

        return redirect()->back();
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
            $resolved = $this->resolveCourseAccess->execute($request->user(), $userProduct);
            $course = $resolved['course'];
        } catch (RuntimeException $e) {
            $this->logDenied($request, $userProduct, $courseLesson, 'course_access_denied', $e->getMessage());
            abort(404);
        }

        $lesson = CourseLesson::query()
            ->where('id', $courseLesson->id)
            ->where('course_id', $course->id)
            ->accessibleToLearner()
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

