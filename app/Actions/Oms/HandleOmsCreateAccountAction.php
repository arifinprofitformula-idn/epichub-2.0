<?php

namespace App\Actions\Oms;

use App\Actions\Affiliates\CreateOrUpdateEpiChannelFromOmsAction;
use App\Enums\OmsIntegrationDirection;
use App\Enums\OmsIntegrationStatus;
use App\Models\OmsIntegrationLog;
use App\Services\Oms\OmsPasswordCipher;
use RuntimeException;

class HandleOmsCreateAccountAction
{
    public function __construct(
        protected OmsPasswordCipher $cipher,
        protected CreateOrUpdateEpiChannelFromOmsAction $createOrUpdateEpiChannel,
        protected LogOmsIntegrationAction $logOmsIntegration,
    ) {
    }

    /**
     * @param  array{
     *      epic_code: string,
     *      name: string,
     *      email: string,
     *      phone?: ?string,
     *      store_name?: ?string,
     *      sponsor_epic_code?: ?string,
     *      sponsor_name?: ?string,
     *      encrypted_password: string
     *  }  $payload
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
            ->where('action', 'create_account')
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
            $plainPassword = $this->cipher->decrypt((string) $payload['encrypted_password']);

            $result = $this->createOrUpdateEpiChannel->execute([
                'epic_code' => (string) $payload['epic_code'],
                'name' => (string) $payload['name'],
                'email' => (string) $payload['email'],
                'phone' => $payload['phone'] ?? null,
                'store_name' => $payload['store_name'] ?? null,
                'sponsor_epic_code' => $payload['sponsor_epic_code'] ?? null,
                'sponsor_name' => $payload['sponsor_name'] ?? null,
            ], $plainPassword);

            $response = [
                'ok' => true,
                'response_code' => (string) config('epichub.oms.response.success', '00'),
                'message' => 'Sukses',
                'data' => [
                    'epic_code' => $result['epi_channel']->epic_code,
                    'email' => $result['user']->email,
                ],
            ];

            $this->logOmsIntegration->execute(
                direction: OmsIntegrationDirection::Inbound,
                action: 'create_account',
                requestId: $requestId,
                epicCode: $result['epi_channel']->epic_code,
                email: $result['user']->email,
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
                action: 'create_account',
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
