<?php

namespace App\Services\Notifications;

use App\Models\EmailNotificationLog;
use App\Services\Mailketing\MailketingClient;
use App\Services\Settings\AppSettingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Throwable;

class EmailNotificationService
{
    public function __construct(
        protected MailketingClient $mailketing,
        protected AppSettingService $settings,
    ) {}

    /**
     * Kirim email transactional ke user.
     *
     * @param  array{email: string, name?: string}  $recipient
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $metadata
     */
    public function sendTransactionalEmail(
        array $recipient,
        string $subject,
        string $view,
        array $data,
        string $eventType,
        array $metadata = [],
    ): void {
        if (! $this->shouldSend($eventType)) {
            $this->logSkipped($recipient['email'], $subject, $eventType, $metadata);
            return;
        }

        $this->dispatch(
            recipientEmail: $recipient['email'],
            recipientName: $recipient['name'] ?? null,
            subject: $subject,
            view: $view,
            data: $data,
            eventType: $eventType,
            metadata: $metadata,
            notifiable: $metadata['notifiable'] ?? null,
        );
    }

    /**
     * Kirim email notifikasi ke admin.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $metadata
     */
    public function sendAdminNotification(
        string $subject,
        string $view,
        array $data,
        string $eventType,
        array $metadata = [],
    ): void {
        $adminEmail = (string) $this->settings->getMailketing('admin_notification_email', '');

        if (blank($adminEmail)) {
            Log::info("EmailNotificationService: admin_notification_email tidak diset, skip event [{$eventType}]");
            return;
        }

        if (! $this->shouldSend($eventType)) {
            $this->logSkipped($adminEmail, $subject, $eventType, $metadata);
            return;
        }

        $this->dispatch(
            recipientEmail: $adminEmail,
            recipientName: 'Admin EPIC HUB',
            subject: $subject,
            view: $view,
            data: $data,
            eventType: $eventType,
            metadata: $metadata,
            notifiable: $metadata['notifiable'] ?? null,
        );
    }

    public function shouldSend(string $eventType): bool
    {
        $settingKey = $this->toggleKeyForEvent($eventType);

        if ($settingKey === null) {
            return true;
        }

        return (bool) $this->settings->getMailketing($settingKey, true);
    }

    // ── Internals ────────────────────────────────────────────────────────────

    private function dispatch(
        string $recipientEmail,
        ?string $recipientName,
        string $subject,
        string $view,
        array $data,
        string $eventType,
        array $metadata,
        mixed $notifiable,
    ): void {
        $useQueue = (bool) $this->settings->getMailketing('enable_email_queue', false);

        if ($useQueue) {
            dispatch(function () use ($recipientEmail, $recipientName, $subject, $view, $data, $eventType, $metadata, $notifiable) {
                $this->send($recipientEmail, $recipientName, $subject, $view, $data, $eventType, $metadata, $notifiable);
            })->afterCommit();
        } else {
            \Illuminate\Support\Facades\DB::afterCommit(function () use ($recipientEmail, $recipientName, $subject, $view, $data, $eventType, $metadata, $notifiable) {
                $this->send($recipientEmail, $recipientName, $subject, $view, $data, $eventType, $metadata, $notifiable);
            });
        }
    }

    private function send(
        string $recipientEmail,
        ?string $recipientName,
        string $subject,
        string $view,
        array $data,
        string $eventType,
        array $metadata,
        mixed $notifiable,
    ): void {
        try {
            $content = $this->renderEmailView($view, $data);
        } catch (Throwable $e) {
            Log::error("EmailNotificationService: gagal render view [{$view}]", ['error' => $e->getMessage()]);
            return;
        }

        $notifiableType = null;
        $notifiableId   = null;

        if (is_object($notifiable) && method_exists($notifiable, 'getKey')) {
            $notifiableType = get_class($notifiable);
            $notifiableId   = $notifiable->getKey();
        }

        if ($this->mailketing->isEnabled()) {
            $this->sendViaMailketing(
                recipientEmail: $recipientEmail,
                recipientName: $recipientName,
                subject: $subject,
                content: $content,
                eventType: $eventType,
                notifiableType: $notifiableType,
                notifiableId: $notifiableId,
                metadata: $metadata,
            );

            return;
        }

        $this->sendViaFallback(
            recipientEmail: $recipientEmail,
            recipientName: $recipientName,
            subject: $subject,
            content: $content,
            eventType: $eventType,
            notifiableType: $notifiableType,
            notifiableId: $notifiableId,
            metadata: $metadata,
        );
    }

    private function sendViaMailketing(
        string $recipientEmail,
        ?string $recipientName,
        string $subject,
        string $content,
        string $eventType,
        ?string $notifiableType,
        mixed $notifiableId,
        array $metadata,
    ): void {
        try {
            $this->mailketing->sendEmail([
                'recipient'       => $recipientEmail,
                'recipient_name'  => $recipientName,
                'subject'         => $subject,
                'content'         => $content,
                'event_type'      => $eventType,
                'notifiable_type' => $notifiableType,
                'notifiable_id'   => $notifiableId,
            ]);
        } catch (Throwable $e) {
            Log::error("EmailNotificationService: Mailketing gagal [{$eventType}]", [
                'recipient' => $recipientEmail,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    private function sendViaFallback(
        string $recipientEmail,
        ?string $recipientName,
        string $subject,
        string $content,
        string $eventType,
        ?string $notifiableType,
        mixed $notifiableId,
        array $metadata,
    ): void {
        $mailerDriver = config('mail.default', '');

        if (blank($mailerDriver) || $mailerDriver === 'array' || $mailerDriver === 'log') {
            $this->logSkipped($recipientEmail, $subject, $eventType, $metadata, 'fallback_not_ready');
            return;
        }

        $logEntry = $this->logEmail(
            recipientEmail: $recipientEmail,
            recipientName: $recipientName,
            subject: $subject,
            eventType: $eventType,
            status: 'pending',
            notifiableType: $notifiableType,
            notifiableId: $notifiableId,
            metadata: $metadata,
            provider: 'laravel',
        );

        try {
            Mail::html($content, function ($message) use ($recipientEmail, $recipientName, $subject) {
                $message->to($recipientEmail, $recipientName)->subject($subject);
            });

            $logEntry?->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (Throwable $e) {
            Log::error("EmailNotificationService: fallback mail gagal [{$eventType}]", [
                'recipient' => $recipientEmail,
                'error'     => $e->getMessage(),
            ]);

            $logEntry?->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'failed_at'     => now(),
            ]);
        }
    }

    public function renderEmailView(string $view, array $data): string
    {
        return View::make($view, $data)->render();
    }

    private function logEmail(
        string $recipientEmail,
        ?string $recipientName,
        string $subject,
        string $eventType,
        string $status,
        ?string $notifiableType = null,
        mixed $notifiableId = null,
        array $metadata = [],
        string $provider = 'mailketing',
    ): ?EmailNotificationLog {
        if (! (bool) $this->settings->getMailketing('enable_email_logs', true)) {
            return null;
        }

        try {
            return EmailNotificationLog::record([
                'provider'        => $provider,
                'event_type'      => $eventType,
                'notifiable_type' => $notifiableType,
                'notifiable_id'   => $notifiableId,
                'recipient_email' => $recipientEmail,
                'recipient_name'  => $recipientName,
                'subject'         => $subject,
                'status'          => $status,
                'metadata'        => $metadata,
            ]);
        } catch (Throwable $e) {
            Log::warning('EmailNotificationService: gagal tulis log email', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function logSkipped(
        string $recipientEmail,
        string $subject,
        string $eventType,
        array $metadata = [],
        string $reason = 'toggle_disabled',
    ): void {
        if (! (bool) $this->settings->getMailketing('enable_email_logs', true)) {
            return;
        }

        try {
            EmailNotificationLog::record([
                'event_type'      => $eventType,
                'recipient_email' => $recipientEmail,
                'subject'         => $subject,
                'status'          => 'skipped',
                'error_message'   => $reason,
                'metadata'        => $metadata,
            ]);
        } catch (Throwable $e) {
            Log::warning('EmailNotificationService: gagal tulis log skipped', ['error' => $e->getMessage()]);
        }
    }

    private function toggleKeyForEvent(string $eventType): ?string
    {
        return match ($eventType) {
            'user_registered'          => 'notify_user_registered',
            'password_reset_requested' => 'notify_password_reset',
            'order_created'            => 'notify_order_created',
            'payment_submitted'        => 'notify_payment_submitted',
            'admin_payment_submitted'  => 'notify_admin_payment_submitted',
            'payment_approved'         => 'notify_payment_approved',
            'payment_rejected'         => 'notify_payment_rejected',
            'access_granted'           => 'notify_access_granted',
            'admin_order_created'      => 'notify_admin_order_created',
            default                    => null,
        };
    }
}
