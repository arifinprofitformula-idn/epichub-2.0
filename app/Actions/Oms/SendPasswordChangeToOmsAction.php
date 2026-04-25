<?php

namespace App\Actions\Oms;

use App\Enums\OmsIntegrationDirection;
use App\Enums\OmsIntegrationStatus;
use App\Models\User;
use App\Services\Oms\OmsPasswordCipher;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SendPasswordChangeToOmsAction
{
    public function __construct(
        protected OmsPasswordCipher $cipher,
        protected LogOmsIntegrationAction $logOms,
    ) {
    }

    public function execute(User $user, string $plainPassword): bool
    {
        if (! config('epichub.oms.enabled', false)) {
            return false;
        }

        $url = (string) config('epichub.oms.outbound_change_password_url', '');

        if ($url === '') {
            return false;
        }

        $user->loadMissing(['epiChannel']);

        $epicCode = $user->epiChannel?->epic_code;

        if (! $epicCode) {
            return false;
        }

        $requestId = (string) Str::uuid();
        $timestamp = (string) time();

        $payload = [
            'kode_epic' => $epicCode,
            'email_epic' => $user->email,
            'encrypted_password' => $this->cipher->encrypt($plainPassword),
        ];

        $secret = (string) config('epichub.oms.signature_secret', '');
        $rawBody = json_encode($payload);

        if ($rawBody === false) {
            return false;
        }

        $signature = $secret !== '' ? hash_hmac('sha256', $timestamp.$requestId.$rawBody, $secret) : '';

        try {
            $response = Http::timeout((int) config('epichub.oms.outbound_timeout', 10))
                ->withHeaders([
                    'X-OMS-Request-Id' => $requestId,
                    'X-OMS-Timestamp' => $timestamp,
                    'X-OMS-Signature' => $signature,
                    'Accept' => 'application/json',
                ])
                ->post($url, $payload);

            $json = $response->json() ?: [];
            $code = (string) data_get($json, 'response_code', '');

            $success = $response->successful() && $code === (string) config('epichub.oms.response.success', '00');

            $this->logOms->execute(
                direction: OmsIntegrationDirection::Outbound,
                action: 'outbound_change_password',
                requestId: $requestId,
                epicCode: $epicCode,
                email: $user->email,
                status: $success ? OmsIntegrationStatus::Success : OmsIntegrationStatus::Failed,
                responseCode: $code !== '' ? $code : null,
                httpStatus: $response->status(),
                requestPayload: $payload,
                responsePayload: is_array($json) ? $json : null,
                errorMessage: $success ? null : (string) data_get($json, 'error', data_get($json, 'message', 'OMS failed')),
            );

            return $success;
        } catch (\Throwable $e) {
            $this->logOms->execute(
                direction: OmsIntegrationDirection::Outbound,
                action: 'outbound_change_password',
                requestId: $requestId,
                epicCode: $epicCode,
                email: $user->email,
                status: OmsIntegrationStatus::Failed,
                responseCode: (string) config('epichub.oms.response.failed', '99'),
                httpStatus: null,
                requestPayload: $payload,
                responsePayload: null,
                errorMessage: $e->getMessage(),
            );

            return false;
        }
    }
}

