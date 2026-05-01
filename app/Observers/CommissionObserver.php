<?php

namespace App\Observers;

use App\Enums\CommissionStatus;
use App\Models\Commission;
use App\Services\Notifications\EmailNotificationService;

class CommissionObserver
{
    public bool $afterCommit = true;

    public function created(Commission $commission): void
    {
        if ($this->isApproved($commission->status)) {
            app(EmailNotificationService::class)->sendAffiliateCommissionCreatedEmail($commission);
        }
    }

    public function updated(Commission $commission): void
    {
        if (! $commission->wasChanged('status')) {
            return;
        }

        if ($this->isApproved($commission->getOriginal('status'))) {
            return;
        }

        if (! $this->isApproved($commission->status)) {
            return;
        }

        app(EmailNotificationService::class)->sendAffiliateCommissionCreatedEmail($commission);
    }

    private function isApproved(mixed $status): bool
    {
        return ($status instanceof CommissionStatus ? $status : CommissionStatus::tryFrom((string) $status)) === CommissionStatus::Approved;
    }
}
