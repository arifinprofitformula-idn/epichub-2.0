<?php

namespace App\Observers;

use App\Enums\CommissionStatus;
use App\Models\Commission;
use App\Services\Notifications\EmailNotificationService;
use App\Services\Notifications\WhatsAppMessageTemplateService;
use App\Services\Notifications\WhatsAppNotificationService;

class CommissionObserver
{
    public bool $afterCommit = true;

    public function created(Commission $commission): void
    {
        if ($this->isApproved($commission->status)) {
            app(EmailNotificationService::class)->sendAffiliateCommissionCreatedEmail($commission);
            $this->sendAffiliateCommissionCreatedWhatsApp($commission);
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
        $this->sendAffiliateCommissionCreatedWhatsApp($commission);
    }

    private function isApproved(mixed $status): bool
    {
        return ($status instanceof CommissionStatus ? $status : CommissionStatus::tryFrom((string) $status)) === CommissionStatus::Approved;
    }

    public function sendAffiliateCommissionCreatedWhatsApp(Commission $commission): void
    {
        $commission->loadMissing(['epiChannel.user', 'product']);

        $user = $commission->epiChannel?->user;
        $product = $commission->product;

        if (! $user || ! $product) {
            return;
        }

        app(WhatsAppNotificationService::class)->sendToUser(
            user: $user,
            message: app(WhatsAppMessageTemplateService::class)->render('affiliate_commission_created', [
                'product_name' => $product->title ?? $product->name,
                'commission_amount' => 'Rp '.number_format((float) $commission->commission_amount, 0, ',', '.'),
                'status' => ($commission->status instanceof CommissionStatus ? $commission->status->label() : (string) $commission->status),
            ]),
            eventType: 'affiliate_commission_created',
            metadata: ['notifiable' => $commission],
        );
    }
}
