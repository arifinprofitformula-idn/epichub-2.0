<?php

namespace App\Actions\Oms;

use App\Actions\Affiliates\CreateOrUpdateEpiChannelFromOmsAction;
use App\Enums\OmsIntegrationDirection;
use App\Enums\OmsIntegrationStatus;
use App\Models\OmsIntegrationLog;
use App\Services\Oms\OmsPasswordCipher;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

class HandleOmsCreateAccountAction
{
    public function __construct(
        protected NormalizeOmsCreateAccountPayloadAction $normalizePayload,
        protected OmsPasswordCipher $cipher,
        protected CreateOrUpdateEpiChannelFromOmsAction $createOrUpdateEpiChannel,
        protected LogOmsIntegrationAction $logOmsIntegration,
    ) {
    }

    /**
     * @param  array<string, mixed>  $rawPayload
     * @return array{
     *      ok: bool,
     *      response_code: string,
     *      message: string,
     *      http_status: int,
     *      data?: array{epic_code: string, email: string},
     *      error?: string
     *  }
     */
    public function execute(array $rawPayload, string $requestId, ?string $ipAddress = null, ?string $userAgent = null): array
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
                'response_code' => $this->successCode(),
                'message' => 'Sukses',
                'http_status' => 200,
                'data' => [
                    'epic_code' => (string) ($existingSuccess->epic_code ?: data_get($rawPayload, 'kode_epic', data_get($rawPayload, 'kode_new_epic', ''))),
                    'email' => (string) ($existingSuccess->email ?: data_get($rawPayload, 'email_epic', data_get($rawPayload, 'email_addr_new_epic', ''))),
                ],
            ];
        }

        $payload = $this->normalizePayload->execute($rawPayload);
        $validator = Validator::make($payload, [
            'epic_code' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'store_name' => ['nullable', 'string', 'max:255'],
            'sponsor_epic_code' => ['nullable', 'string', 'max:100'],
            'sponsor_name' => ['nullable', 'string', 'max:255'],
            'encrypted_password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            $response = $this->businessFailure('Payload OMS tidak valid.');

            $this->logOmsIntegration->execute(
                direction: OmsIntegrationDirection::Inbound,
                action: 'create_account',
                requestId: $requestId,
                epicCode: $payload['epic_code'] ?? null,
                email: $payload['email'] ?? null,
                status: OmsIntegrationStatus::Failed,
                responseCode: $response['response_code'],
                httpStatus: $response['http_status'],
                requestPayload: $rawPayload,
                responsePayload: $response,
                errorMessage: 'Payload OMS tidak valid.',
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            );

            return $response;
        }

        try {
            $plainPassword = $this->cipher->decrypt((string) $payload['encrypted_password']);
        } catch (RuntimeException) {
            $response = $this->businessFailure('Password tidak valid atau tidak dapat diproses.');

            $this->logOmsIntegration->execute(
                direction: OmsIntegrationDirection::Inbound,
                action: 'create_account',
                requestId: $requestId,
                epicCode: $payload['epic_code'] ?? null,
                email: $payload['email'] ?? null,
                status: OmsIntegrationStatus::Failed,
                responseCode: $response['response_code'],
                httpStatus: $response['http_status'],
                requestPayload: $rawPayload,
                responsePayload: $response,
                errorMessage: 'Password tidak valid atau tidak dapat diproses.',
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            );

            return $response;
        }

        try {
            $result = $this->createOrUpdateEpiChannel->execute([
                'epic_code' => (string) $payload['epic_code'],
                'name' => (string) $payload['name'],
                'email' => (string) $payload['email'],
                'whatsapp_number' => $payload['whatsapp_number'] ?? null,
                'store_name' => $payload['store_name'] ?? null,
                'sponsor_epic_code' => $payload['sponsor_epic_code'] ?? null,
                'sponsor_name' => $payload['sponsor_name'] ?? null,
            ], $plainPassword);

            $response = [
                'ok' => true,
                'response_code' => $this->successCode(),
                'message' => 'Sukses',
                'http_status' => 200,
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
                requestPayload: $rawPayload,
                responsePayload: $response,
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            );

            return $response;
        } catch (RuntimeException $e) {
            $response = $this->businessFailure($e->getMessage());

            $this->logOmsIntegration->execute(
                direction: OmsIntegrationDirection::Inbound,
                action: 'create_account',
                requestId: $requestId,
                epicCode: $payload['epic_code'] ?? null,
                email: $payload['email'] ?? null,
                status: OmsIntegrationStatus::Failed,
                responseCode: $response['response_code'],
                httpStatus: $response['http_status'],
                requestPayload: $rawPayload,
                responsePayload: $response,
                errorMessage: $e->getMessage(),
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            );

            return $response;
        } catch (Throwable $e) {
            $response = $this->businessFailure('Permintaan OMS gagal diproses.');

            $this->logOmsIntegration->execute(
                direction: OmsIntegrationDirection::Inbound,
                action: 'create_account',
                requestId: $requestId,
                epicCode: $payload['epic_code'] ?? null,
                email: $payload['email'] ?? null,
                status: OmsIntegrationStatus::Failed,
                responseCode: $response['response_code'],
                httpStatus: $response['http_status'],
                requestPayload: $rawPayload,
                responsePayload: $response,
                errorMessage: 'Permintaan OMS gagal diproses.',
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            );

            return $response;
        }
    }

    /**
     * @return array{ok: false, response_code: string, message: string, http_status: int, error: string}
     */
    protected function businessFailure(string $error): array
    {
        return [
            'ok' => false,
            'response_code' => $this->failedCode(),
            'message' => 'Gagal',
            'http_status' => 200,
            'error' => $error,
        ];
    }

    protected function successCode(): string
    {
        return (string) config('epichub.oms.response.success', '00');
    }

    protected function failedCode(): string
    {
        return (string) config('epichub.oms.response.failed', '99');
    }
}
