<?php

namespace App\Observers;

use App\Enums\PayoutStatus;
use App\Models\CommissionPayout;
use App\Services\Notifications\EmailNotificationService;
use App\Services\Notifications\WhatsAppMessageTemplateService;
use App\Services\Notifications\WhatsAppNotificationService;

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

        $payout->loadMissing(['epiChannel.user']);

        $user = $payout->epiChannel?->user;

        if ($user) {
            app(WhatsAppNotificationService::class)->sendToUser(
                user: $user,
                message: app(WhatsAppMessageTemplateService::class)->render('commission_payout_paid', [
                    'amount' => 'Rp '.number_format((float) $payout->total_amount, 0, ',', '.'),
                    'paid_at' => $payout->paid_at?->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y H:i') ?? '-',
                ]),
                eventType: 'commission_payout_paid',
                metadata: ['notifiable' => $payout],
            );
        }

        app(WhatsAppNotificationService::class)->sendAdminAlert(
            message: app(WhatsAppMessageTemplateService::class)->render('admin_payout_paid', [
                'member_name' => $user?->name ?? 'Member EPI Channel',
                'amount' => 'Rp '.number_format((float) $payout->total_amount, 0, ',', '.'),
                'paid_at' => $payout->paid_at?->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y H:i') ?? '-',
            ]),
            eventType: 'admin_payout_paid',
            metadata: ['notifiable' => $payout],
        );
    }

    private function isPaid(mixed $status): bool
    {
        return ($status instanceof PayoutStatus ? $status : PayoutStatus::tryFrom((string) $status)) === PayoutStatus::Paid;
    }
}
