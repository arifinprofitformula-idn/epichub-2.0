<?php

namespace App\Notifications;

use App\Services\Notifications\EmailNotificationService;
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
            $service = app(EmailNotificationService::class);

            $service->sendTransactionalEmail(
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
            );
        } catch (\Throwable $e) {
            Log::error('ResetPasswordNotification: gagal kirim via EmailNotificationService', [
                'error' => $e->getMessage(),
            ]);
        }

        // Kembalikan MailMessage kosong — Laravel tetap butuh return value tapi email sudah dikirim via service
        return (new MailMessage)->subject('Reset Password EPIC HUB')->line('');
    }
}
