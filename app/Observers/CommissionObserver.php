<?php

namespace App\Observers;

use App\Enums\CommissionStatus;
use App\Models\Commission;
use App\Services\Notifications\EmailNotificationService;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\NotificationPayloadBuilder;

class CommissionObserver
{
    public bool $afterCommit = true;

    public function created(Commission $commission): void
    {
        if ($this->isApproved($commission->status)) {
            $this->notifyCommissionCreated($commission);
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

        $this->notifyCommissionCreated($commission);
    }

    private function isApproved(mixed $status): bool
    {
        return ($status instanceof CommissionStatus ? $status : CommissionStatus::tryFrom((string) $status)) === CommissionStatus::Approved;
    }

    private function notifyCommissionCreated(Commission $commission): void
    {
        $commission->loadMissing(['epiChannel.user', 'product']);

        $user = $commission->epiChannel?->user;
        $product = $commission->product;

        if (! $user || ! $product) {
            return;
        }

        $dispatcher = app(NotificationDispatcher::class);
        $payload = app(NotificationPayloadBuilder::class)->forCommission($commission);

        $dispatcher->notifySponsorEmail(
            eventKey: 'affiliate_commission_created',
            user: $user,
            payload: $payload,
            notifiable: $commission,
            fallback: fn () => app(EmailNotificationService::class)->sendAffiliateCommissionCreatedEmail($commission),
        );

        $dispatcher->notifySponsorWhatsApp(
            eventKey: 'affiliate_commission_created',
            user: $user,
            payload: $payload,
            notifiable: $commission,
            legacyData: [
                'product_name' => $product->title ?? $product->name,
                'commission_amount' => 'Rp '.number_format((float) $commission->commission_amount, 0, ',', '.'),
                'status' => ($commission->status instanceof CommissionStatus ? $commission->status->label() : (string) $commission->status),
            ],
        );
    }
}
