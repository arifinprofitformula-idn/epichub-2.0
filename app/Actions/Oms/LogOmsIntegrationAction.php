<?php

namespace App\Actions\Oms;

use App\Enums\OmsIntegrationDirection;
use App\Enums\OmsIntegrationStatus;
use App\Models\OmsIntegrationLog;

class LogOmsIntegrationAction
{
    public function execute(
        OmsIntegrationDirection $direction,
        string $action,
        ?string $requestId,
        ?string $epicCode,
        ?string $email,
        OmsIntegrationStatus $status,
        ?string $responseCode = null,
        ?int $httpStatus = null,
        ?array $requestPayload = null,
        ?array $responsePayload = null,
        ?string $errorMessage = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        OmsIntegrationLog::query()->create([
            'direction' => $direction,
            'action' => $action,
            'request_id' => $requestId,
            'epic_code' => $epicCode,
            'email' => $email,
            'status' => $status,
            'response_code' => $responseCode,
            'http_status' => $httpStatus,
            'request_payload' => $requestPayload ? $this->sanitize($requestPayload) : null,
            'response_payload' => $responsePayload ? $this->sanitize($responsePayload) : null,
            'error_message' => $errorMessage,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'processed_at' => now(),
        ]);
    }

    protected function sanitize(array $payload): array
    {
        $clean = $payload;

        foreach (['password', 'password_encrypted', 'encrypted_password', 'new_password'] as $key) {
            if (array_key_exists($key, $clean)) {
                $clean[$key] = '***';
            }
        }

        return $clean;
    }
}

