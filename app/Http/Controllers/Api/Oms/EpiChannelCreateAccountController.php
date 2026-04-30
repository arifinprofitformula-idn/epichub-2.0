<?php

namespace App\Http\Controllers\Api\Oms;

use App\Actions\Oms\HandleOmsCreateAccountAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EpiChannelCreateAccountController
{
    public function __construct(
        protected HandleOmsCreateAccountAction $handleCreateAccount,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
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
}
