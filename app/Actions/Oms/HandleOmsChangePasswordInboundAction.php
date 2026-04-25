<?php

namespace App\Actions\Oms;

use App\Enums\OmsIntegrationDirection;
use App\Enums\OmsIntegrationStatus;
use App\Models\EpiChannel;
use App\Models\OmsIntegrationLog;
use App\Services\Oms\OmsPasswordCipher;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class HandleOmsChangePasswordInboundAction
{
    public function __construct(
        protected OmsPasswordCipher $cipher,
        protected LogOmsIntegrationAction $logOmsIntegration,
    ) {
    }

    /**
     * @param  array{epic_code: string, email: string, encrypted_password: string}  $payload
     * @return array{
     *      ok: bool,
     *      response_code: string,
     *      message: string,
     *      data?: array{epic_code: string, email: string},
     *      error?: string
     *  }
     */
    public function execute(array $payload, string $requestId, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        $existingSuccess = OmsIntegrationLog::query()
            ->where('direction', OmsIntegrationDirection::Inbound->value)
            ->where('action', 'change_password')
            ->where('request_id', $requestId)
            ->where('status', OmsIntegrationStatus::Success->value)
            ->first();

        if ($existingSuccess) {
            return [
                'ok' => true,
                'response_code' => (string) config('epichub.oms.response.success', '00'),
                'message' => 'Sukses',
                'data' => [
                    'epic_code' => (string) ($existingSuccess->epic_code ?: ($payload['epic_code'] ?? '')),
                    'email' => (string) ($existingSuccess->email ?: ($payload['email'] ?? '')),
                ],
            ];
        }

        try {
            $epicCode = strtoupper(trim((string) $payload['epic_code']));
            $email = strtolower(trim((string) $payload['email']));

            $channel = EpiChannel::query()
                ->whereRaw('UPPER(epic_code) = ?', [$epicCode])
                ->with('user')
                ->first();

            if (! $channel || ! $channel->user || strtolower((string) $channel->user->email) !== $email) {
                throw new RuntimeException('Kode EPIC dan email tidak valid.');
            }

            $plainPassword = $this->cipher->decrypt((string) $payload['encrypted_password']);

            $channel->user->update([
                'password' => Hash::make($plainPassword),
            ]);

            $response = [
                'ok' => true,
                'response_code' => (string) config('epichub.oms.response.success', '00'),
                'message' => 'Sukses',
                'data' => [
                    'epic_code' => $channel->epic_code,
                    'email' => $channel->user->email,
                ],
            ];

            $this->logOmsIntegration->execute(
                direction: OmsIntegrationDirection::Inbound,
                action: 'change_password',
                requestId: $requestId,
                epicCode: $channel->epic_code,
                email: $channel->user->email,
                status: OmsIntegrationStatus::Success,
                responseCode: $response['response_code'],
                httpStatus: 200,
                requestPayload: $payload,
                responsePayload: $response,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            );

            return $response;
        } catch (RuntimeException $e) {
            $response = [
                'ok' => false,
                'response_code' => (string) config('epichub.oms.response.failed', '99'),
                'message' => 'Gagal',
                'error' => $e->getMessage(),
            ];

            $this->logOmsIntegration->execute(
                direction: OmsIntegrationDirection::Inbound,
                action: 'change_password',
                requestId: $requestId,
                epicCode: $payload['epic_code'] ?? null,
                email: $payload['email'] ?? null,
                status: OmsIntegrationStatus::Failed,
                responseCode: $response['response_code'],
                httpStatus: 200,
                requestPayload: $payload,
                responsePayload: $response,
                errorMessage: $e->getMessage(),
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            );

            return $response;
        }
    }
}
