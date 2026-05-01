<?php

namespace App\Observers;

use App\Enums\ProductType;
use App\Enums\UserProductStatus;
use App\Models\UserProduct;
use App\Services\Notifications\EmailNotificationService;
use Carbon\Carbon;

class UserProductObserver
{
    public bool $afterCommit = true;

    public function created(UserProduct $userProduct): void
    {
        if ($this->shouldNotify($userProduct, wasActiveBefore: false)) {
            app(EmailNotificationService::class)->sendCourseEnrollmentEmail($userProduct);
        }
    }

    public function updated(UserProduct $userProduct): void
    {
        $wasActiveBefore = $this->wasActiveBefore($userProduct);

        if ($this->shouldNotify($userProduct, $wasActiveBefore)) {
            app(EmailNotificationService::class)->sendCourseEnrollmentEmail($userProduct);
        }
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
