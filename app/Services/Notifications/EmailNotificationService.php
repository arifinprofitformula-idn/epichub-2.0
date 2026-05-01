<?php

namespace App\Services\Notifications;

use App\Enums\CommissionStatus;
use App\Models\Commission;
use App\Models\CommissionPayout;
use App\Models\EmailNotificationLog;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\UserProduct;
use App\Services\Mailketing\MailketingClient;
use App\Services\Settings\AppSettingService;
use Carbon\CarbonInterface;
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
        $adminRecipients = $this->adminRecipients();

        if ($adminRecipients === []) {
            Log::info("EmailNotificationService: admin_notification_email tidak diset, skip event [{$eventType}]");
            return;
        }

        if (! $this->shouldSend($eventType)) {
            foreach ($adminRecipients as $adminEmail) {
                $this->logSkipped($adminEmail, $subject, $eventType, $metadata);
            }

            return;
        }

        foreach ($adminRecipients as $adminEmail) {
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
    }

    public function shouldSend(string $eventType): bool
    {
        $settingKey = $this->toggleKeyForEvent($eventType);

        if ($settingKey === null) {
            return true;
        }

        return (bool) $this->settings->getMailketing($settingKey, true);
    }

    public function sendEventRegistrationConfirmed(EventRegistration $registration): void
    {
        $registration->loadMissing(['event.product', 'user', 'userProduct']);

        $event = $registration->event;
        $user = $registration->user;

        if (! $event || ! $user || blank($user->email)) {
            return;
        }

        $this->sendTransactionalEmail(
            recipient: ['email' => $user->email, 'name' => $user->name],
            subject: 'Registrasi Event Berhasil',
            view: 'emails.events.registration-confirmed',
            data: [
                'userName' => $user->name,
                'eventName' => $event->title,
                'eventSchedule' => $this->formatEventSchedule($event),
                'eventLocation' => $this->eventLocationLabel($event),
                'accessGuidance' => $this->eventAccessGuidance($event),
                'myEventUrl' => route('my-events.show', $registration),
                'myEventsUrl' => route('my-events.index'),
            ],
            eventType: 'event_registration_confirmed',
            metadata: ['notifiable' => $registration],
        );
    }

    public function sendAdminEventRegistrationNotification(EventRegistration $registration): void
    {
        $registration->loadMissing(['event.product', 'user', 'userProduct.order']);

        $event = $registration->event;
        $user = $registration->user;

        if (! $event || ! $user) {
            return;
        }

        $sourceLabel = match (true) {
            $registration->order_id !== null => 'Order paid / pembelian event',
            $registration->user_product_id !== null => 'Grant akses produk',
            default => 'Registrasi admin / manual',
        };

        $this->sendAdminNotification(
            subject: 'Pendaftaran Event Baru',
            view: 'emails.events.admin-new-registration',
            data: [
                'participantName' => $user->name,
                'participantEmail' => $user->email,
                'eventName' => $event->title,
                'eventSchedule' => $this->formatEventSchedule($event),
                'eventLocation' => $this->eventLocationLabel($event),
                'sourceLabel' => $sourceLabel,
                'registeredAt' => $this->formatDateTime($registration->registered_at, config('app.timezone', 'Asia/Jakarta')),
                'adminEventRegistrationUrl' => url('/admin/event-registrations/'.$registration->id.'/edit'),
            ],
            eventType: 'admin_event_registration',
            metadata: ['notifiable' => $registration],
        );
    }

    public function sendCourseEnrollmentEmail(UserProduct $userProduct): void
    {
        $userProduct->loadMissing(['user', 'product.course']);

        $user = $userProduct->user;
        $product = $userProduct->product;
        $course = $product?->course;

        if (! $user || blank($user->email) || ! $product || ! $course) {
            return;
        }

        $this->sendTransactionalEmail(
            recipient: ['email' => $user->email, 'name' => $user->name],
            subject: 'Anda Terdaftar di Kelas Baru',
            view: 'emails.courses.enrolled',
            data: [
                'userName' => $user->name,
                'courseName' => $course->title,
                'courseDescription' => $course->short_description,
                'courseUrl' => route('my-courses.show', $userProduct),
                'myCoursesUrl' => route('my-courses.index'),
            ],
            eventType: 'course_enrolled',
            metadata: ['notifiable' => $userProduct],
        );
    }

    public function sendAffiliateCommissionCreatedEmail(Commission $commission): void
    {
        $commission->loadMissing(['epiChannel.user', 'product']);

        $channelUser = $commission->epiChannel?->user;
        $product = $commission->product;

        if (! $channelUser || blank($channelUser->email) || ! $product) {
            return;
        }

        $status = $commission->status instanceof CommissionStatus
            ? $commission->status->label()
            : (string) $commission->status;

        $this->sendTransactionalEmail(
            recipient: ['email' => $channelUser->email, 'name' => $channelUser->name],
            subject: 'Komisi Affiliate Baru Masuk',
            view: 'emails.affiliate.commission-created',
            data: [
                'userName' => $channelUser->name,
                'productName' => $product->title,
                'commissionAmount' => $this->formatCurrency((float) $commission->commission_amount),
                'commissionStatus' => $status,
                'commissionUrl' => route('epi-channel.commissions'),
            ],
            eventType: 'affiliate_commission_created',
            metadata: ['notifiable' => $commission],
        );
    }

    public function sendPayoutPaidEmail(CommissionPayout $payout): void
    {
        $payout->loadMissing(['epiChannel.user']);

        $channel = $payout->epiChannel;
        $channelUser = $channel?->user;

        if (! $channel || ! $channelUser || blank($channelUser->email)) {
            return;
        }

        $bankSnapshot = (array) data_get($payout->metadata, 'bank_account_snapshot', []);
        $maskedDestination = $this->maskedPayoutDestination(
            bankName: (string) ($bankSnapshot['bank_name'] ?? $channel->payout_bank_name ?? ''),
            accountNumber: (string) ($bankSnapshot['bank_account_number'] ?? $channel->payout_bank_account_number ?? ''),
            accountHolder: (string) ($bankSnapshot['bank_account_holder_name'] ?? $channel->payout_bank_account_holder_name ?? ''),
        );

        $this->sendTransactionalEmail(
            recipient: ['email' => $channelUser->email, 'name' => $channelUser->name],
            subject: 'Payout Komisi Telah Diproses',
            view: 'emails.payouts.paid',
            data: [
                'userName' => $channelUser->name,
                'payoutNumber' => $payout->payout_number,
                'payoutAmount' => $this->formatCurrency((float) $payout->total_amount),
                'paidAt' => $this->formatDateTime($payout->paid_at, config('app.timezone', 'Asia/Jakarta')),
                'paymentDestination' => $maskedDestination,
                'payoutUrl' => route('epi-channel.payouts'),
            ],
            eventType: 'commission_payout_paid',
            metadata: ['notifiable' => $payout],
        );
    }

    public function sendAdminPayoutPaidNotification(CommissionPayout $payout): void
    {
        $payout->loadMissing(['epiChannel.user']);

        $channel = $payout->epiChannel;
        $channelUser = $channel?->user;

        if (! $channel) {
            return;
        }

        $this->sendAdminNotification(
            subject: 'Payout Komisi Diproses',
            view: 'emails.admin.payout-paid',
            data: [
                'memberName' => $channelUser?->name ?? 'Member EPI Channel',
                'memberEmail' => $channelUser?->email ?? '-',
                'epicCode' => $channel->epic_code,
                'payoutNumber' => $payout->payout_number,
                'payoutAmount' => $this->formatCurrency((float) $payout->total_amount),
                'paidAt' => $this->formatDateTime($payout->paid_at, config('app.timezone', 'Asia/Jakarta')),
                'adminPayoutUrl' => url('/admin/commission-payouts'),
            ],
            eventType: 'admin_payout_paid',
            metadata: ['notifiable' => $payout],
        );
    }

    // ── Rendered content methods (dari template DB) ──────────────────────────

    /**
     * Kirim email transactional dengan konten HTML yang sudah dirender.
     * Digunakan oleh NotificationDispatcher untuk template dari database.
     *
     * @param  array{email: string, name?: string}  $recipient
     * @param  array<string, mixed>  $metadata
     */
    public function sendRenderedTransactionalEmail(
        array $recipient,
        string $subject,
        string $htmlContent,
        string $eventType,
        array $metadata = [],
    ): void {
        if (! $this->shouldSend($eventType)) {
            $this->logSkipped($recipient['email'], $subject, $eventType, $metadata);
            return;
        }

        $this->dispatchRendered(
            recipientEmail: $recipient['email'],
            recipientName: $recipient['name'] ?? null,
            subject: $subject,
            htmlContent: $htmlContent,
            eventType: $eventType,
            metadata: $metadata,
            notifiable: $metadata['notifiable'] ?? null,
        );
    }

    /**
     * Kirim email ke admin dengan konten HTML yang sudah dirender.
     * Digunakan oleh NotificationDispatcher untuk template dari database.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function sendRenderedAdminNotification(
        string $subject,
        string $htmlContent,
        string $eventType,
        array $metadata = [],
    ): void {
        $adminRecipients = $this->adminRecipients();

        if ($adminRecipients === []) {
            Log::info("EmailNotificationService: admin_notification_email tidak diset, skip event [{$eventType}]");
            return;
        }

        if (! $this->shouldSend($eventType)) {
            foreach ($adminRecipients as $adminEmail) {
                $this->logSkipped($adminEmail, $subject, $eventType, $metadata);
            }

            return;
        }

        foreach ($adminRecipients as $adminEmail) {
            $this->dispatchRendered(
                recipientEmail: $adminEmail,
                recipientName: 'Admin EPIC HUB',
                subject: $subject,
                htmlContent: $htmlContent,
                eventType: $eventType,
                metadata: $metadata,
                notifiable: $metadata['notifiable'] ?? null,
            );
        }
    }

    // ── Internals ────────────────────────────────────────────────────────────

    private function dispatchRendered(
        string $recipientEmail,
        ?string $recipientName,
        string $subject,
        string $htmlContent,
        string $eventType,
        array $metadata,
        mixed $notifiable,
    ): void {
        $useQueue = (bool) $this->settings->getMailketing('enable_email_queue', false);

        if ($useQueue) {
            dispatch(function () use ($recipientEmail, $recipientName, $subject, $htmlContent, $eventType, $metadata, $notifiable) {
                $this->sendRendered($recipientEmail, $recipientName, $subject, $htmlContent, $eventType, $metadata, $notifiable);
            })->afterCommit();
        } else {
            \Illuminate\Support\Facades\DB::afterCommit(function () use ($recipientEmail, $recipientName, $subject, $htmlContent, $eventType, $metadata, $notifiable) {
                $this->sendRendered($recipientEmail, $recipientName, $subject, $htmlContent, $eventType, $metadata, $notifiable);
            });
        }
    }

    private function sendRendered(
        string $recipientEmail,
        ?string $recipientName,
        string $subject,
        string $htmlContent,
        string $eventType,
        array $metadata,
        mixed $notifiable,
    ): void {
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
                content: $htmlContent,
                eventType: $eventType,
                notifiableType: $notifiableType,
                notifiableId: $notifiableId,
                metadata: $this->metadataWithRetryPayload(
                    metadata: $metadata,
                    recipientName: $recipientName,
                    subject: $subject,
                    content: $htmlContent,
                    eventType: $eventType,
                    notifiableType: $notifiableType,
                    notifiableId: $notifiableId,
                    provider: 'mailketing',
                ),
            );

            return;
        }

        $this->sendViaFallback(
            recipientEmail: $recipientEmail,
            recipientName: $recipientName,
            subject: $subject,
            content: $htmlContent,
            eventType: $eventType,
            notifiableType: $notifiableType,
            notifiableId: $notifiableId,
            metadata: $this->metadataWithRetryPayload(
                metadata: $metadata,
                recipientName: $recipientName,
                subject: $subject,
                content: $htmlContent,
                eventType: $eventType,
                notifiableType: $notifiableType,
                notifiableId: $notifiableId,
                provider: 'laravel',
            ),
        );
    }

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
                metadata: $this->metadataWithRetryPayload(
                    metadata: $metadata,
                    recipientName: $recipientName,
                    subject: $subject,
                    content: $content,
                    eventType: $eventType,
                    notifiableType: $notifiableType,
                    notifiableId: $notifiableId,
                    provider: 'mailketing',
                ),
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
            metadata: $this->metadataWithRetryPayload(
                metadata: $metadata,
                recipientName: $recipientName,
                subject: $subject,
                content: $content,
                eventType: $eventType,
                notifiableType: $notifiableType,
                notifiableId: $notifiableId,
                provider: 'laravel',
            ),
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
                'log_metadata'    => $metadata,
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
            'event_registration_confirmed' => 'notify_event_registration',
            'admin_event_registration' => 'notify_admin_event_registration',
            'course_enrolled'          => 'notify_course_enrollment',
            'affiliate_commission_created' => 'notify_commission_created',
            'commission_payout_paid'   => 'notify_payout_paid',
            'admin_payout_paid'        => 'notify_admin_payout_paid',
            'admin_commission_payout_paid' => 'notify_admin_payout_paid',
            'payment_reminder'         => 'enable_payment_reminder',
            'event_reminder_day_before' => 'enable_event_reminder',
            'event_reminder_hour_before' => 'enable_event_reminder',
            default                    => null,
        };
    }

    private function metadataWithRetryPayload(
        array $metadata,
        ?string $recipientName,
        string $subject,
        string $content,
        string $eventType,
        ?string $notifiableType,
        mixed $notifiableId,
        string $provider,
    ): array {
        $metadata['retry_payload'] = [
            'recipient_name' => $recipientName,
            'subject' => $subject,
            'content' => $content,
            'event_type' => $eventType,
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiableId,
            'provider' => $provider,
        ];
        $metadata['retry_count'] = (int) ($metadata['retry_count'] ?? 0);
        $metadata['max_retry'] = (int) ($metadata['max_retry'] ?? 3);

        return $metadata;
    }

    /**
     * @return list<string>
     */
    private function adminRecipients(): array
    {
        $raw = (string) $this->settings->getMailketing('admin_notification_email', '');

        return collect(preg_split('/[\s,;]+/', $raw) ?: [])
            ->map(fn (string $email): string => trim($email))
            ->filter(fn (string $email): bool => $email !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function formatEventSchedule(Event $event): string
    {
        if (! $event->starts_at) {
            return 'Jadwal akan diinformasikan segera';
        }

        $timezone = $event->timezone ?: config('app.timezone', 'Asia/Jakarta');
        $startsAt = $event->starts_at->timezone($timezone);
        $schedule = $startsAt->translatedFormat('d M Y, H:i');

        if ($event->ends_at) {
            $schedule .= ' - '.$event->ends_at->timezone($timezone)->translatedFormat('H:i');
        }

        return trim($schedule.' '.$timezone);
    }

    private function eventLocationLabel(Event $event): string
    {
        $metadataKeys = ['location', 'location_label', 'venue', 'venue_name'];

        foreach ($metadataKeys as $key) {
            $value = trim((string) data_get($event->metadata, $key, ''));

            if ($value !== '') {
                return $value;
            }
        }

        if (filled($event->zoom_url)) {
            return 'Online';
        }

        return 'Detail lokasi akan diinformasikan di halaman event';
    }

    /**
     * @return list<string>
     */
    private function eventAccessGuidance(Event $event): array
    {
        $guidance = [];

        if (filled($event->zoom_url)) {
            $guidance[] = 'Akses Zoom mengikuti jadwal dan aturan event. Gunakan tombol di halaman Event Saya saat akses sudah dibuka.';
        }

        if (filled($event->replay_url)) {
            $guidance[] = 'Replay akan muncul di halaman Event Saya setelah tersedia sesuai pengaturan event.';
        }

        if ($guidance === []) {
            $guidance[] = 'Detail akses event akan diinformasikan melalui halaman Event Saya.';
        }

        return $guidance;
    }

    private function formatDateTime(?CarbonInterface $dateTime, string $timezone): string
    {
        if (! $dateTime) {
            return '-';
        }

        return $dateTime->timezone($timezone)->translatedFormat('d M Y, H:i').' '.$timezone;
    }

    private function formatCurrency(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    private function maskedPayoutDestination(string $bankName, string $accountNumber, string $accountHolder): string
    {
        $cleanNumber = preg_replace('/\s+/', '', $accountNumber) ?? '';

        if (strlen($cleanNumber) > 4) {
            $cleanNumber = str_repeat('*', max(strlen($cleanNumber) - 4, 0)).substr($cleanNumber, -4);
        }

        return collect([$bankName, $cleanNumber, $accountHolder])
            ->filter(fn (string $value): bool => trim($value) !== '')
            ->implode(' / ');
    }
}
