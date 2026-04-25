<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyOmsRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('epichub.oms.enabled', false)) {
            return response()->json([
                'response_code' => (string) config('epichub.oms.response.failed', '99'),
                'message' => 'Gagal',
                'error' => 'OMS integration disabled.',
            ], 403);
        }

        $requestId = trim((string) $request->header('X-OMS-Request-Id', ''));
        $timestamp = trim((string) $request->header('X-OMS-Timestamp', ''));
        $signature = trim((string) $request->header('X-OMS-Signature', ''));
        $secret = (string) config('epichub.oms.signature_secret', '');

        if ($requestId === '' || $timestamp === '' || $signature === '' || $secret === '') {
            return response()->json([
                'response_code' => (string) config('epichub.oms.response.failed', '99'),
                'message' => 'Gagal',
                'error' => 'Invalid OMS signature headers.',
            ], 401);
        }

        if (! ctype_digit($timestamp)) {
            return response()->json([
                'response_code' => (string) config('epichub.oms.response.failed', '99'),
                'message' => 'Gagal',
                'error' => 'Invalid timestamp.',
            ], 401);
        }

        $ts = (int) $timestamp;
        $maxSkew = (int) config('epichub.oms.signature_max_skew_seconds', 300);

        if (abs(time() - $ts) > $maxSkew) {
            return response()->json([
                'response_code' => (string) config('epichub.oms.response.failed', '99'),
                'message' => 'Gagal',
                'error' => 'Signature expired.',
            ], 401);
        }

        $rawBody = $request->getContent();
        $expected = hash_hmac('sha256', $timestamp.$requestId.$rawBody, $secret);

        if (! hash_equals($expected, $signature)) {
            return response()->json([
                'response_code' => (string) config('epichub.oms.response.failed', '99'),
                'message' => 'Gagal',
                'error' => 'Invalid signature.',
            ], 401);
        }

        $request->attributes->set('oms_request_id', $requestId);
        $request->attributes->set('oms_timestamp', $timestamp);

        return $next($request);
    }
}

