<?php

namespace App\Notifications;

use App\Services\Notifications\EmailNotificationService;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\NotificationPayloadBuilder;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class ResetPasswordNotification extends BaseResetPassword
{
    use Queueable;

    public function toMail(mixed $notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);

        try {
            $dispatcher = app(NotificationDispatcher::class);
            $payload = app(NotificationPayloadBuilder::class)->forPasswordReset($notifiable, $url);

            $dispatcher->notifyMemberEmail(
                eventKey: 'password_reset_requested',
                user: $notifiable,
                payload: $payload,
                notifiable: $notifiable,
                fallback: fn () => app(EmailNotificationService::class)->sendTransactionalEmail(
                    recipient: ['email' => $notifiable->email, 'name' => $notifiable->name ?? null],
                    subject: 'Reset Password EPIC HUB',
                    view: 'emails.auth.password-reset',
                    data: [
                        'userName'       => $notifiable->name ?? $notifiable->email,
                        'resetUrl'       => $url,
                        'expiryMinutes'  => config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
                    ],
                    eventType: 'password_reset_requested',
                    metadata: ['notifiable' => $notifiable],
                ),
            );
        } catch (\Throwable $e) {
            Log::error('ResetPasswordNotification: gagal kirim via EmailNotificationService', [
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $dispatcher = app(NotificationDispatcher::class);
            $payload = app(NotificationPayloadBuilder::class)->forPasswordReset($notifiable);

            $dispatcher->notifyMemberWhatsApp(
                eventKey: 'password_reset_requested',
                user: $notifiable,
                payload: $payload,
                notifiable: $notifiable,
                legacyData: [
                    'name' => $notifiable->name ?? $notifiable->email,
                ],
            );
        } catch (\Throwable $e) {
            Log::error('ResetPasswordNotification: gagal kirim WhatsApp reset password', [
                'error' => $e->getMessage(),
            ]);
        }

        // Kembalikan MailMessage kosong — Laravel tetap butuh return value tapi email sudah dikirim via service
        return (new MailMessage)->subject('Reset Password EPIC HUB')->line('');
    }
}
