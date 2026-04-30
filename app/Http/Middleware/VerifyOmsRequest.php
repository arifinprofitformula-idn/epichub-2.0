<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyOmsRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->integrationEnabled()) {
            return response()->json([
                'response_code' => $this->failedCode(),
                'message' => 'Gagal',
                'error' => 'Integrasi OMS sedang nonaktif.',
            ], 403);
        }

        $requestId = trim((string) $request->header('X-OMS-Request-Id', ''));
        $timestamp = trim((string) $request->header('X-OMS-Timestamp', ''));
        $signature = trim((string) $request->header('X-OMS-Signature', ''));

        if ($requestId === '' || $timestamp === '') {
            return response()->json([
                'response_code' => $this->failedCode(),
                'message' => 'Gagal',
                'error' => 'Header OMS wajib tidak lengkap.',
            ], 401);
        }

        if (! ctype_digit($timestamp)) {
            return response()->json([
                'response_code' => $this->failedCode(),
                'message' => 'Gagal',
                'error' => 'Timestamp OMS tidak valid.',
            ], 401);
        }

        $ts = (int) $timestamp;
        $maxSkew = (int) config('epichub.oms.signature_max_skew_seconds', 300);

        if (abs(time() - $ts) > $maxSkew) {
            return response()->json([
                'response_code' => $this->failedCode(),
                'message' => 'Gagal',
                'error' => 'Timestamp OMS di luar toleransi.',
            ], 401);
        }

        $signatureSecret = trim((string) config('epichub.oms.signature_secret', ''));
        $inboundSecret = trim((string) config('epichub.oms.inbound_secret', ''));

        if ($signatureSecret !== '') {
            if ($signature === '') {
                return response()->json([
                    'response_code' => $this->failedCode(),
                    'message' => 'Gagal',
                    'error' => 'Signature OMS wajib dikirim.',
                ], 401);
            }

            $rawBody = $request->getContent();
            $expected = hash_hmac('sha256', $timestamp.$requestId.$rawBody, $signatureSecret);

            if (! hash_equals($expected, $signature)) {
                return response()->json([
                    'response_code' => $this->failedCode(),
                    'message' => 'Gagal',
                    'error' => 'Signature OMS tidak valid.',
                ], 401);
            }
        } elseif ($inboundSecret !== '') {
            if ($request->bearerToken() !== $inboundSecret) {
                return response()->json([
                    'response_code' => $this->failedCode(),
                    'message' => 'Gagal',
                    'error' => 'Bearer token OMS tidak valid.',
                ], 401);
            }
        } else {
            return response()->json([
                'response_code' => $this->failedCode(),
                'message' => 'Gagal',
                'error' => 'Autentikasi OMS belum dikonfigurasi.',
            ], 403);
        }

        $request->attributes->set('oms_request_id', $requestId);
        $request->attributes->set('oms_timestamp', $timestamp);
        $request->attributes->set('oms_auth_mode', $signatureSecret !== '' ? 'signature' : 'bearer');

        return $next($request);
    }

    protected function integrationEnabled(): bool
    {
        return (bool) config('epichub.oms.integration_enabled', config('epichub.oms.enabled', false));
    }

    protected function failedCode(): string
    {
        return (string) config('epichub.oms.response.failed', '99');
    }
}
