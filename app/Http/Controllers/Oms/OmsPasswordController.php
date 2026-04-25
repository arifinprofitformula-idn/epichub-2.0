<?php

namespace App\Http\Controllers\Oms;

use App\Actions\Oms\LogOmsIntegrationAction;
use App\Actions\Oms\UpdatePasswordFromOmsAction;
use App\Enums\OmsIntegrationDirection;
use App\Enums\OmsIntegrationStatus;
use App\Models\OmsIntegrationLog;
use App\Services\Oms\OmsPasswordCrypto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class OmsPasswordController
{
    public function __construct(
        protected OmsPasswordCrypto $crypto,
        protected UpdatePasswordFromOmsAction $updatePassword,
        protected LogOmsIntegrationAction $logOms,
    ) {
    }

    public function change(Request $request): JsonResponse
    {
        $data = $request->validate([
            'request_id' => ['nullable', 'string', 'max:120'],
            'epic_code' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password_encrypted' => ['required', 'string'],
        ]);

        $requestId = $data['request_id'] ?: (string) Str::uuid();

        $existingLog = OmsIntegrationLog::query()->where('request_id', $requestId)->first();

        if ($existingLog && $existingLog->status === OmsIntegrationStatus::Success) {
            return response()->json([
                'code' => (string) config('epichub.oms.response.success', '00'),
                'message' => 'OK',
                'request_id' => $requestId,
            ]);
        }

        try {
            $plain = $this->crypto->decrypt((string) $data['password_encrypted']);

            $this->updatePassword->execute(
                epicCode: (string) $data['epic_code'],
                email: (string) $data['email'],
                plainPassword: $plain,
            );

            $response = [
                'code' => (string) config('epichub.oms.response.success', '00'),
                'message' => 'OK',
                'request_id' => $requestId,
            ];

            $this->logOms->execute(
                direction: OmsIntegrationDirection::Inbound,
                action: 'change_password',
                requestId: $requestId,
                epicCode: (string) $data['epic_code'],
                email: (string) $data['email'],
                status: OmsIntegrationStatus::Success,
                responseCode: (string) config('epichub.oms.response.success', '00'),
                httpStatus: 200,
                requestPayload: $data,
                responsePayload: $response,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return response()->json($response);
        } catch (RuntimeException $e) {
            $response = [
                'code' => (string) config('epichub.oms.response.failed', '99'),
                'message' => $e->getMessage(),
                'request_id' => $requestId,
            ];

            $this->logOms->execute(
                direction: OmsIntegrationDirection::Inbound,
                action: 'change_password',
                requestId: $requestId,
                epicCode: $data['epic_code'] ?? null,
                email: $data['email'] ?? null,
                status: OmsIntegrationStatus::Failed,
                responseCode: (string) config('epichub.oms.response.failed', '99'),
                httpStatus: 422,
                requestPayload: $data,
                responsePayload: $response,
                errorMessage: $e->getMessage(),
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return response()->json($response, 422);
        }
    }
}

