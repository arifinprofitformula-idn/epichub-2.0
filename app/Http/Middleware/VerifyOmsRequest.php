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
                'code' => (string) config('epichub.oms.response.failed', '99'),
                'message' => 'OMS integration disabled.',
            ], 403);
        }

        $token = (string) $request->header('X-OMS-Token', '');
        $expectedToken = (string) config('epichub.oms.inbound_secret', '');

        if ($expectedToken !== '' && hash_equals($expectedToken, $token)) {
            return $next($request);
        }

        $signature = (string) $request->header('X-OMS-Signature', '');
        $timestamp = (string) $request->header('X-OMS-Timestamp', '');
        $secret = (string) config('epichub.oms.signature_secret', '');

        if ($signature === '' || $timestamp === '' || $secret === '') {
            return response()->json([
                'code' => (string) config('epichub.oms.response.failed', '99'),
                'message' => 'Invalid signature.',
            ], 401);
        }

        if (! ctype_digit($timestamp)) {
            return response()->json([
                'code' => (string) config('epichub.oms.response.failed', '99'),
                'message' => 'Invalid timestamp.',
            ], 401);
        }

        $now = time();
        $ts = (int) $timestamp;
        $maxSkew = (int) config('epichub.oms.signature_max_skew_seconds', 300);

        if (abs($now - $ts) > $maxSkew) {
            return response()->json([
                'code' => (string) config('epichub.oms.response.failed', '99'),
                'message' => 'Signature expired.',
            ], 401);
        }

        $rawBody = $request->getContent();
        $expected = hash_hmac('sha256', $timestamp.'.'.$rawBody, $secret);

        if (! hash_equals($expected, $signature)) {
            return response()->json([
                'code' => (string) config('epichub.oms.response.failed', '99'),
                'message' => 'Invalid signature.',
            ], 401);
        }

        return $next($request);
    }
}

