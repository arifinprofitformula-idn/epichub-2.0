<?php

namespace App\Observers;

use App\Enums\ProductType;
use App\Enums\UserProductStatus;
use App\Models\UserProduct;
use App\Services\Mailketing\MailketingSubscriberService;
use App\Services\Notifications\EmailNotificationService;
use App\Services\Notifications\WhatsAppMessageTemplateService;
use App\Services\Notifications\WhatsAppNotificationService;
use Carbon\Carbon;

class UserProductObserver
{
    public bool $afterCommit = true;

    public function created(UserProduct $userProduct): void
    {
        if ($this->shouldNotify($userProduct, wasActiveBefore: false)) {
            app(EmailNotificationService::class)->sendCourseEnrollmentEmail($userProduct);
            $this->subscribeCourseStudent($userProduct);
        }
    }

    public function updated(UserProduct $userProduct): void
    {
        $wasActiveBefore = $this->wasActiveBefore($userProduct);

        if ($this->shouldNotify($userProduct, $wasActiveBefore)) {
            app(EmailNotificationService::class)->sendCourseEnrollmentEmail($userProduct);
            $this->subscribeCourseStudent($userProduct);
        }
    }

    private function subscribeCourseStudent(UserProduct $userProduct): void
    {
        $userProduct->loadMissing(['user', 'product.course']);

        if (! $userProduct->user || ! $userProduct->product?->course) {
            return;
        }

        try {
            app(WhatsAppNotificationService::class)->sendToUser(
                user: $userProduct->user,
                message: app(WhatsAppMessageTemplateService::class)->render('course_enrolled', [
                    'course_name' => $userProduct->product->course->title,
                    'course_url' => route('my-courses.show', $userProduct),
                ]),
                eventType: 'course_enrolled',
                metadata: ['notifiable' => $userProduct],
            );
        } catch (\Throwable) {
            // Observer ini tidak boleh memblokir flow grant akses.
        }

        app(MailketingSubscriberService::class)->addCourseStudentToList(
            $userProduct->user,
            $userProduct->product->course,
        );
    }

    private function shouldNotify(UserProduct $userProduct, bool $wasActiveBefore): bool
    {
        if (! $this->isCourseProduct($userProduct)) {
            return false;
        }

        if (! $userProduct->isActive()) {
            return false;
        }

        return ! $wasActiveBefore;
    }

    private function isCourseProduct(UserProduct $userProduct): bool
    {
        $userProduct->loadMissing('product.course');

        $type = $userProduct->product?->product_type;

        return ($type instanceof ProductType ? $type : ProductType::tryFrom((string) $type)) === ProductType::Course;
    }

    private function wasActiveBefore(UserProduct $userProduct): bool
    {
        $originalStatus = $userProduct->getOriginal('status');
        $statusValue = $originalStatus instanceof UserProductStatus ? $originalStatus->value : (string) $originalStatus;

        if ($statusValue !== UserProductStatus::Active->value) {
            return false;
        }

        $originalExpiresAt = $userProduct->getOriginal('expires_at');

        if ($originalExpiresAt === null) {
            return true;
        }

        return Carbon::parse($originalExpiresAt)->isFuture();
    }
}
