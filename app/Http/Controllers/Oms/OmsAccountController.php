<?php

namespace App\Http\Controllers\Oms;

use App\Actions\Oms\LogOmsIntegrationAction;
use App\Actions\Oms\UpsertOmsAccountAction;
use App\Enums\OmsIntegrationDirection;
use App\Enums\OmsIntegrationStatus;
use App\Models\OmsIntegrationLog;
use App\Services\Oms\OmsPasswordCrypto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class OmsAccountController
{
    public function __construct(
        protected OmsPasswordCrypto $crypto,
        protected UpsertOmsAccountAction $upsertAccount,
        protected LogOmsIntegrationAction $logOms,
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'request_id' => ['nullable', 'string', 'max:120'],
            'epic_code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'store_name' => ['nullable', 'string', 'max:255'],
            'sponsor_epic_code' => ['nullable', 'string', 'max:50'],
            'sponsor_name' => ['nullable', 'string', 'max:255'],
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

            $result = $this->upsertAccount->execute(
                epicCode: (string) $data['epic_code'],
                name: (string) $data['name'],
                email: (string) $data['email'],
                phone: $data['phone'] ?? null,
                storeName: $data['store_name'] ?? null,
                sponsorEpicCode: $data['sponsor_epic_code'] ?? null,
                sponsorName: $data['sponsor_name'] ?? null,
                plainPassword: $plain,
            );

            $response = [
                'code' => (string) config('epichub.oms.response.success', '00'),
                'message' => 'OK',
                'request_id' => $requestId,
            ];

            $this->logOms->execute(
                direction: OmsIntegrationDirection::Inbound,
                action: 'create_account',
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
                action: 'create_account',
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

