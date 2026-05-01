<?php

namespace App\Observers;

use App\Enums\PayoutStatus;
use App\Models\CommissionPayout;
use App\Services\Notifications\EmailNotificationService;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\NotificationPayloadBuilder;

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
        $dispatcher = app(NotificationDispatcher::class);

        $dispatcher->notifySponsorEmail(
            eventKey: 'commission_payout_paid',
            user: $payout->epiChannel?->user,
            payload: app(NotificationPayloadBuilder::class)->forPayout($payout),
            notifiable: $payout,
            fallback: fn () => $service->sendPayoutPaidEmail($payout),
        );

        $dispatcher->notifyAdminEmail(
            eventKey: 'admin_payout_paid',
            payload: app(NotificationPayloadBuilder::class)->forAdminPayout($payout),
            notifiable: $payout,
            fallback: fn () => $service->sendAdminPayoutPaidNotification($payout),
        );

        $payout->loadMissing(['epiChannel.user']);

        $user = $payout->epiChannel?->user;

        if ($user) {
            $dispatcher->notifySponsorWhatsApp(
                eventKey: 'commission_payout_paid',
                user: $user,
                payload: app(NotificationPayloadBuilder::class)->forPayout($payout),
                notifiable: $payout,
                legacyData: [
                    'amount' => 'Rp '.number_format((float) $payout->total_amount, 0, ',', '.'),
                    'paid_at' => $payout->paid_at?->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y H:i') ?? '-',
                ],
            );
        }

        $dispatcher->notifyAdminWhatsApp(
            eventKey: 'admin_payout_paid',
            payload: app(NotificationPayloadBuilder::class)->forAdminPayout($payout),
            notifiable: $payout,
            legacyData: [
                'member_name' => $user?->name ?? 'Member EPI Channel',
                'amount' => 'Rp '.number_format((float) $payout->total_amount, 0, ',', '.'),
                'paid_at' => $payout->paid_at?->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y H:i') ?? '-',
            ],
        );
    }

    private function isPaid(mixed $status): bool
    {
        return ($status instanceof PayoutStatus ? $status : PayoutStatus::tryFrom((string) $status)) === PayoutStatus::Paid;
    }
}
