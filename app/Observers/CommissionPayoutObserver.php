<?php

namespace App\Observers;

use App\Enums\PayoutStatus;
use App\Models\CommissionPayout;
use App\Services\Notifications\EmailNotificationService;

class CommissionPayoutObserver
{
    public bool $afterCommit = true;

    public function created(CommissionPayout $payout): void
    {
        if (! $this->isPaid($payout->status)) {
            return;
        }

        $this->notify($payout);
    }

    public function updated(CommissionPayout $payout): void
    {
        if (! $payout->wasChanged('status')) {
            return;
        }

        if ($this->isPaid($payout->getOriginal('status'))) {
            return;
        }

        if (! $this->isPaid($payout->status)) {
            return;
        }

        $this->notify($payout);
    }

    private function notify(CommissionPayout $payout): void
    {
        $service = app(EmailNotificationService::class);

        $service->sendPayoutPaidEmail($payout);
        $service->sendAdminPayoutPaidNotification($payout);
    }

    private function isPaid(mixed $status): bool
    {
        return ($status instanceof PayoutStatus ? $status : PayoutStatus::tryFrom((string) $status)) === PayoutStatus::Paid;
    }
}
