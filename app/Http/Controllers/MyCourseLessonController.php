<?php

namespace App\Http\Controllers;

use App\Actions\Access\LogAccessAction;
use App\Actions\Course\DownloadLessonAttachmentAction;
use App\Actions\Course\MarkLessonCompletedAction;
use App\Actions\Course\ResolveCourseAccessAction;
use App\Enums\AccessLogAction;
use App\Enums\CourseLessonType;
use App\Enums\LessonProgressStatus;
use App\Models\CourseLesson;
use App\Models\LessonProgress;
use App\Models\UserProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MyCourseLessonController extends Controller
{
    public function __construct(
        protected ResolveCourseAccessAction $resolveCourseAccess,
        protected MarkLessonCompletedAction $markLessonCompleted,
        protected DownloadLessonAttachmentAction $downloadLessonAttachment,
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
            ->where('is_active', true)
            ->where(function (Builder $q): void {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->with(['section'])
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

        return view('my-courses.lesson', [
            'userProduct' => $userProduct,
            'course' => $course,
            'lesson' => $lesson,
            'progressPercent' => $resolved['progressPercent'],
            'completedLessons' => $resolved['completedLessons'],
            'totalLessons' => $resolved['totalLessons'],
            'prevLessonId' => $prevLessonId,
            'nextLessonId' => $nextLessonId,
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
            ->where('is_active', true)
            ->where(function (Builder $q): void {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
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

