<?php

namespace App\Services\Notifications;

use App\Models\EmailNotificationLog;
use App\Services\Mailketing\MailketingClient;
use Illuminate\Support\Facades\Mail;

class EmailRetryService
{
    public function __construct(
        protected MailketingClient $mailketing,
    ) {}

    /**
     * @return array{success: bool, message: string, status: string}
     */
    public function retry(EmailNotificationLog $log): array
    {
        $metadata = is_array($log->metadata) ? $log->metadata : [];
        $retryPayload = is_array($metadata['retry_payload'] ?? null) ? $metadata['retry_payload'] : [];
        $retryCount = (int) ($metadata['retry_count'] ?? 0);
        $maxRetry = max(1, (int) ($metadata['max_retry'] ?? 3));

        if ($retryCount >= $maxRetry) {
            return ['success' => false, 'message' => 'Batas retry tercapai.', 'status' => 'failed'];
        }

        if ($retryPayload === [] || blank($log->recipient_email) || blank($retryPayload['subject'] ?? null) || blank($retryPayload['content'] ?? null)) {
            return ['success' => false, 'message' => 'Retry payload tidak tersedia.', 'status' => 'failed'];
        }

        $provider = (string) ($retryPayload['provider'] ?? $log->provider ?? 'mailketing');

        try {
            $result = $provider === 'laravel'
                ? $this->retryViaLaravel($log, $retryPayload)
                : $this->retryViaMailketing($log, $retryPayload);
        } catch (\Throwable $e) {
            $result = [
                'success' => false,
                'message' => $e->getMessage(),
                'raw' => [],
            ];
        }

        $metadata['retry_count'] = $retryCount + 1;
        $metadata['last_retry_at'] = now()->toISOString();
        $history = is_array($metadata['retry_history'] ?? null) ? $metadata['retry_history'] : [];
        $history[] = [
            'attempt' => $metadata['retry_count'],
            'success' => (bool) $result['success'],
            'message' => (string) ($result['message'] ?? ''),
            'at' => now()->toISOString(),
        ];
        $metadata['retry_history'] = $history;

        $log->update([
            'status' => $result['success'] ? 'sent' : 'failed',
            'provider_response' => $result['raw'] ?? null,
            'error_message' => $result['success'] ? null : ($result['message'] ?? 'Retry gagal'),
            'sent_at' => $result['success'] ? now() : $log->sent_at,
            'failed_at' => $result['success'] ? null : now(),
            'metadata' => $metadata,
        ]);

        return [
            'success' => (bool) $result['success'],
            'message' => (string) ($result['message'] ?? ''),
            'status' => $result['success'] ? 'sent' : 'failed',
        ];
    }

    /**
     * @param  array<string, mixed>  $retryPayload
     * @return array{success: bool, message: string, raw: array}
     */
    private function retryViaMailketing(EmailNotificationLog $log, array $retryPayload): array
    {
        $result = $this->mailketing->sendEmail([
            'recipient' => $log->recipient_email,
            'recipient_name' => $retryPayload['recipient_name'] ?? $log->recipient_name,
            'subject' => (string) $retryPayload['subject'],
            'content' => (string) $retryPayload['content'],
            'event_type' => $retryPayload['event_type'] ?? $log->event_type,
            'notifiable_type' => $retryPayload['notifiable_type'] ?? $log->notifiable_type,
            'notifiable_id' => $retryPayload['notifiable_id'] ?? $log->notifiable_id,
            'log_metadata' => $log->metadata ?? [],
            'suppress_logging' => true,
        ]);

        return [
            'success' => (bool) $result['success'],
            'message' => (string) ($result['message'] ?? ''),
            'raw' => $result['raw'] ?? [],
        ];
    }

    /**
     * @param  array<string, mixed>  $retryPayload
     * @return array{success: bool, message: string, raw: array}
     */
    private function retryViaLaravel(EmailNotificationLog $log, array $retryPayload): array
    {
        Mail::html((string) $retryPayload['content'], function ($message) use ($log, $retryPayload) {
            $message
                ->to($log->recipient_email, $retryPayload['recipient_name'] ?? $log->recipient_name)
                ->subject((string) $retryPayload['subject']);
        });

        return [
            'success' => true,
            'message' => 'Retry berhasil dikirim via Laravel Mail.',
            'raw' => ['provider' => 'laravel'],
        ];
    }
}
