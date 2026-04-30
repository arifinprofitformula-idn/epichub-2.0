<?php

namespace App\Http\Controllers\Oms;

use App\Actions\Oms\HandleOmsChangePasswordInboundAction;
use App\Actions\Oms\HandleOmsCreateAccountAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OmsEpiChannelController
{
    public function __construct(
        protected HandleOmsCreateAccountAction $handleCreateAccount,
        protected HandleOmsChangePasswordInboundAction $handleChangePassword,
    ) {
    }

    public function createAccount(Request $request): JsonResponse
    {
        $result = $this->handleCreateAccount->execute(
            rawPayload: $request->all(),
            requestId: (string) $request->attributes->get('oms_request_id', ''),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        $payload = [
            'response_code' => $result['response_code'],
            'message' => $result['message'],
        ];

        if (isset($result['data'])) {
            $payload['data'] = $result['data'];
        }

        if (isset($result['error'])) {
            $payload['error'] = $result['error'];
        }

        return response()->json($payload, $result['http_status'] ?? 200);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $normalized = $this->normalizeChangePasswordPayload($request);

        $validator = Validator::make($normalized, [
            'epic_code' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'encrypted_password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response_code' => (string) config('epichub.oms.response.failed', '99'),
                'message' => 'Gagal',
                'error' => 'Payload OMS tidak valid.',
            ]);
        }

        $requestId = (string) $request->attributes->get('oms_request_id', '');

        $result = $this->handleChangePassword->execute(
            payload: $validator->validated(),
            requestId: $requestId,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        if ($result['ok']) {
            return response()->json([
                'response_code' => $result['response_code'],
                'message' => $result['message'],
                'data' => $result['data'],
            ]);
        }

        return response()->json([
            'response_code' => $result['response_code'],
            'message' => $result['message'],
            'error' => $result['error'] ?? 'Terjadi kesalahan.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizeCreateAccountPayload(Request $request): array
    {
        return [
            'epic_code' => $this->firstFilled($request, ['kode_epic', 'kode_new_epic', 'epic_code']),
            'name' => $this->firstFilled($request, ['nama_epic', 'nama_new_epic', 'name']),
            'email' => $this->firstFilled($request, ['email_epic', 'email_addr_new_epic', 'email']),
            'phone' => $this->firstFilled($request, ['no_tlp_epic', 'no_tlp_new_epic', 'phone']),
            'store_name' => $this->firstFilled($request, ['nama_epi_store', 'nama_epi_store_new_epic', 'store_name']),
            'sponsor_epic_code' => $this->firstFilled($request, ['sponsor_epic_code', 'kode_epic_sponsor']),
            'sponsor_name' => $this->firstFilled($request, ['sponsor_name', 'nama_epic_sponsor']),
            'encrypted_password' => $this->firstFilled($request, ['encrypted_password', 'password_terenkripsi', 'password_encrypted']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function normalizeChangePasswordPayload(Request $request): array
    {
        return [
            'epic_code' => $this->firstFilled($request, ['kode_epic', 'epic_code']),
            'email' => $this->firstFilled($request, ['email_epic', 'email']),
            'encrypted_password' => $this->firstFilled($request, ['encrypted_password', 'password_terenkripsi', 'password_encrypted']),
        ];
    }

    protected function firstFilled(Request $request, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $request->input($key);

            if ($value === null) {
                continue;
            }

            $value = trim((string) $value);

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}
