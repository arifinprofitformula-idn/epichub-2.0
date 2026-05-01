<?php

namespace App\Services\Notifications;

use App\Jobs\SendDripSenderWhatsAppJob;
use App\Models\User;
use App\Models\WhatsAppNotificationLog;
use App\Services\DripSender\DripSenderClient;
use App\Services\Settings\AppSettingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    public function __construct(
        protected DripSenderClient $client,
        protected AppSettingService $settings,
    ) {}

    /** @param array<string, mixed> $metadata */
    public function sendToUser(User $user, string $message, string $eventType, array $metadata = []): ?WhatsAppNotificationLog
    {
        return $this->sendToPhone(
            phone: (string) ($user->whatsapp_number ?? ''),
            message: $message,
            eventType: $eventType,
            metadata: array_merge($metadata, [
                'recipient_name' => $user->name,
                'notifiable' => $metadata['notifiable'] ?? $user,
            ]),
        );
    }

    /** @param array<string, mixed> $metadata */
    public function sendToPhone(string $phone, string $message, string $eventType, array $metadata = []): ?WhatsAppNotificationLog
    {
        $normalizedPhone = $this->client->normalizePhone($phone);
        $message = trim($message);
        $toggleKey = $this->toggleKeyForEvent($eventType);
        $recipientName = $metadata['recipient_name'] ?? null;
        $notifiable = $metadata['notifiable'] ?? null;

        if ($toggleKey !== null && ! $this->shouldSend($toggleKey)) {
            return $this->logMessage(
                recipientPhone: $normalizedPhone ?? $phone,
                message: $message,
                eventType: $eventType,
                status: 'skipped',
                recipientName: is_string($recipientName) ? $recipientName : null,
                metadata: $metadata,
                errorMessage: 'toggle_disabled',
                notifiable: $notifiable,
            );
        }

        if (! $this->client->isEnabled()) {
            return $this->logMessage(
                recipientPhone: $normalizedPhone ?? $phone,
                message: $message,
                eventType: $eventType,
                status: 'skipped',
                recipientName: is_string($recipientName) ? $recipientName : null,
                metadata: $metadata,
                errorMessage: 'dripsender_disabled',
                notifiable: $notifiable,
            );
        }

        if ($normalizedPhone === null) {
            return $this->logMessage(
                recipientPhone: $phone,
                message: $message,
                eventType: $eventType,
                status: 'skipped',
                recipientName: is_string($recipientName) ? $recipientName : null,
                metadata: $metadata,
                errorMessage: 'invalid_phone',
                notifiable: $notifiable,
            );
        }

        if ($message === '') {
            return $this->logMessage(
                recipientPhone: $normalizedPhone,
                message: $message,
                eventType: $eventType,
                status: 'failed',
                recipientName: is_string($recipientName) ? $recipientName : null,
                metadata: $metadata,
                errorMessage: 'empty_message',
                notifiable: $notifiable,
            );
        }

        if ($this->hasDeliveredOrQueued($eventType, $normalizedPhone, $notifiable)) {
            return $this->logMessage(
                recipientPhone: $normalizedPhone,
                message: $message,
                eventType: $eventType,
                status: 'skipped',
                recipientName: is_string($recipientName) ? $recipientName : null,
                mediaUrl: $metadata['media_url'] ?? null,
                groupId: $metadata['group_id'] ?? null,
                metadata: $this->stripSensitiveMetadata($metadata),
                errorMessage: 'duplicate_guard',
                notifiable: $notifiable,
            );
        }

        $log = $this->logMessage(
            recipientPhone: $normalizedPhone,
            message: $message,
            eventType: $eventType,
            status: 'pending',
            recipientName: is_string($recipientName) ? $recipientName : null,
            mediaUrl: $metadata['media_url'] ?? null,
            groupId: $metadata['group_id'] ?? null,
            metadata: $this->withRetryPayload($metadata, $normalizedPhone, $message, $eventType),
            notifiable: $notifiable,
        );

        if (! $log) {
            return null;
        }

        $this->dispatchLog($log);

        return $log;
    }

    /** @param array<string, mixed> $metadata */
    public function sendAdminAlert(string $message, string $eventType, array $metadata = []): void
    {
        foreach ($this->adminPhoneNumbers() as $phone) {
            try {
                $this->sendToPhone($phone, $message, $eventType, $metadata);
            } catch (\Throwable $e) {
                Log::warning('WhatsAppNotificationService: gagal kirim admin alert', [
                    'event_type' => $eventType,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function shouldSend(string $settingKey): bool
    {
        return (bool) $this->settings->getDripSender($settingKey, true);
    }

    /** @param array<string, mixed> $metadata */
    public function logMessage(
        string $recipientPhone,
        string $message,
        string $eventType,
        string $status,
        ?string $recipientName = null,
        ?string $mediaUrl = null,
        ?string $groupId = null,
        array $metadata = [],
        ?string $errorMessage = null,
        mixed $notifiable = null,
    ): ?WhatsAppNotificationLog {
        if (! (bool) $this->settings->getDripSender('dripsender_enable_logs', true)) {
            return null;
        }

        $payload = [
            'provider' => 'dripsender',
            'event_type' => $eventType,
            'recipient_phone' => $recipientPhone,
            'recipient_name' => $recipientName,
            'message' => $message,
            'media_url' => $mediaUrl,
            'group_id' => $groupId,
            'status' => $status,
            'error_message' => $errorMessage,
            'metadata' => $this->stripSensitiveMetadata($metadata),
            'sent_at' => $status === 'sent' ? now() : null,
            'failed_at' => $status === 'failed' ? now() : null,
        ];

        if ($notifiable instanceof Model) {
            $payload['notifiable_type'] = $notifiable::class;
            $payload['notifiable_id'] = $notifiable->getKey();
        }

        try {
            return WhatsAppNotificationLog::record($payload);
        } catch (\Throwable $e) {
            Log::warning('WhatsAppNotificationService: gagal tulis log WhatsApp', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public function deliverLog(WhatsAppNotificationLog $log): array
    {
        if (in_array($log->status, ['sent', 'skipped'], true)) {
            return ['success' => true, 'message' => 'Log sudah diproses sebelumnya.', 'status' => $log->status];
        }

        if ($this->hasDeliveredOrQueued($log->event_type, $log->recipient_phone, $log->notifiable, $log->id)) {
            $log->update([
                'status' => 'skipped',
                'error_message' => 'duplicate_guard',
            ]);

            return ['success' => true, 'message' => 'Pengiriman duplikat dilewati oleh idempotency guard.', 'status' => 'skipped'];
        }

        try {
            $metadata = is_array($log->metadata) ? $log->metadata : [];
            $payload = [
                'phone' => $log->recipient_phone,
                'text' => $log->message,
                'media_url' => $log->media_url,
                'group_id' => $log->group_id,
                'footer' => $metadata['footer'] ?? $this->settings->getDripSender('dripsender_default_footer', 'EPIC HUB'),
                'buttons' => $metadata['buttons'] ?? null,
                'isInteractive' => $metadata['isInteractive'] ?? null,
                'send_at' => $metadata['send_at'] ?? null,
            ];

            $result = $log->media_url
                ? $this->client->sendMediaMessage($payload)
                : $this->client->sendMessage($payload);

            $log->update([
                'status' => $result['success'] ? 'sent' : 'failed',
                'http_status' => $result['http_status'],
                'provider_response' => $this->normalizeProviderResponse($result['raw']),
                'error_message' => $result['success'] ? null : $result['message'],
                'sent_at' => $result['success'] ? now() : $log->sent_at,
                'failed_at' => $result['success'] ? null : now(),
            ]);

            return [
                'success' => (bool) $result['success'],
                'message' => (string) $result['message'],
                'status' => $result['success'] ? 'sent' : 'failed',
            ];
        } catch (\Throwable $e) {
            Log::error('WhatsAppNotificationService: unexpected delivery error', [
                'log_id' => $log->id,
                'error' => $e->getMessage(),
            ]);

            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'failed_at' => now(),
            ]);

            return ['success' => false, 'message' => $e->getMessage(), 'status' => 'failed'];
        }
    }

    private function dispatchLog(WhatsAppNotificationLog $log): void
    {
        $callback = function () use ($log): void {
            if ((bool) $this->settings->getDripSender('dripsender_enable_queue', false)) {
                SendDripSenderWhatsAppJob::dispatch($log->id)->afterCommit();

                return;
            }

            $this->deliverLog($log->fresh() ?? $log);
        };

        DB::afterCommit($callback);
    }

    /** @return list<string> */
    private function adminPhoneNumbers(): array
    {
        $raw = (string) $this->settings->getDripSender('dripsender_admin_phone_numbers', '');

        return collect(preg_split('/[\r\n,;]+/', $raw) ?: [])
            ->map(fn (string $phone): ?string => $this->client->normalizePhone($phone))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /** @param array<string, mixed> $metadata
     *  @return array<string, mixed>
     */
    private function withRetryPayload(array $metadata, string $phone, string $message, string $eventType): array
    {
        $metadata['retry_payload'] = [
            'phone' => $phone,
            'message' => $message,
            'event_type' => $eventType,
            'media_url' => $metadata['media_url'] ?? null,
            'group_id' => $metadata['group_id'] ?? null,
            'footer' => $metadata['footer'] ?? $this->settings->getDripSender('dripsender_default_footer', 'EPIC HUB'),
            'buttons' => $metadata['buttons'] ?? null,
            'isInteractive' => $metadata['isInteractive'] ?? null,
            'send_at' => $metadata['send_at'] ?? null,
        ];
        $metadata['max_retry'] = (int) ($metadata['max_retry'] ?? 3);

        return $metadata;
    }

    /** @param array<string, mixed> $metadata
     *  @return array<string, mixed>
     */
    private function stripSensitiveMetadata(array $metadata): array
    {
        unset($metadata['api_key'], $metadata['api-token'], $metadata['api_token']);

        return $metadata;
    }

    private function normalizeProviderResponse(mixed $raw): ?array
    {
        if ($raw === null || $raw === []) {
            return null;
        }

        if (is_array($raw)) {
            return $raw;
        }

        return ['body' => (string) $raw];
    }

    private function hasDeliveredOrQueued(?string $eventType, string $recipientPhone, mixed $notifiable, ?int $ignoreLogId = null): bool
    {
        if ($eventType === '' || $eventType === null) {
            return false;
        }

        if (! $notifiable instanceof Model) {
            return false;
        }

        return WhatsAppNotificationLog::query()
            ->when($ignoreLogId !== null, fn ($query) => $query->whereKeyNot($ignoreLogId))
            ->where('event_type', $eventType)
            ->where('notifiable_type', $notifiable::class)
            ->where('notifiable_id', $notifiable->getKey())
            ->where('recipient_phone', $recipientPhone)
            ->whereIn('status', ['pending', 'sent'])
            ->exists();
    }

    private function toggleKeyForEvent(string $eventType): ?string
    {
        return match ($eventType) {
            'user_registered' => 'whatsapp_notify_user_registered',
            'password_reset_requested' => 'whatsapp_notify_password_reset',
            'order_created' => 'whatsapp_notify_order_created',
            'payment_submitted' => 'whatsapp_notify_payment_submitted',
            'payment_approved' => 'whatsapp_notify_payment_approved',
            'payment_rejected' => 'whatsapp_notify_payment_rejected',
            'access_granted' => 'whatsapp_notify_access_granted',
            'event_registration_confirmed' => 'whatsapp_notify_event_registration',
            'course_enrolled' => 'whatsapp_notify_course_enrollment',
            'affiliate_commission_created' => 'whatsapp_notify_commission_created',
            'commission_payout_paid' => 'whatsapp_notify_payout_paid',
            'admin_order_created' => 'whatsapp_notify_admin_order_created',
            'admin_payment_submitted' => 'whatsapp_notify_admin_payment_submitted',
            'admin_event_registration' => 'whatsapp_notify_admin_event_registration',
            'admin_payout_paid' => 'whatsapp_notify_admin_payout_paid',
            'payment_reminder' => 'enable_whatsapp_payment_reminder',
            'event_reminder_day_before', 'event_reminder_hour_before' => 'enable_whatsapp_event_reminder',
            default => null,
        };
    }
}
