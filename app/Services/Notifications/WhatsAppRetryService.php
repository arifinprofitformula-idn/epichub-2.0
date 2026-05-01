<?php

namespace App\Services\Notifications;

use App\Models\WhatsAppNotificationLog;

class WhatsAppRetryService
{
    public function __construct(
        protected WhatsAppNotificationService $service,
    ) {}

    /** @return array{success: bool, message: string, status: string} */
    public function retry(WhatsAppNotificationLog $log): array
    {
        if ($log->retry_count >= 3) {
            return ['success' => false, 'message' => 'Batas retry tercapai.', 'status' => 'failed'];
        }

        $metadata = is_array($log->metadata) ? $log->metadata : [];
        $payload = is_array($metadata['retry_payload'] ?? null) ? $metadata['retry_payload'] : [];

        if ($payload === []) {
            return ['success' => false, 'message' => 'Retry payload tidak tersedia.', 'status' => 'failed'];
        }

        $log->update([
            'retry_count' => $log->retry_count + 1,
            'status' => 'pending',
            'failed_at' => null,
            'error_message' => null,
        ]);

        $result = $this->service->deliverLog($log->fresh() ?? $log);

        $metadata['last_retry_at'] = now()->toISOString();
        $history = is_array($metadata['retry_history'] ?? null) ? $metadata['retry_history'] : [];
        $history[] = [
            'attempt' => $log->retry_count,
            'success' => (bool) $result['success'],
            'message' => (string) $result['message'],
            'at' => now()->toISOString(),
        ];
        $metadata['retry_history'] = $history;

        $log->refresh()->update(['metadata' => $metadata]);

        return [
            'success' => (bool) $result['success'],
            'message' => (string) $result['message'],
            'status' => (string) $result['status'],
        ];
    }
}
