<?php

namespace App\Actions\Affiliates;

use App\Models\EpiChannel;
use Illuminate\Http\Request;

class ResolveCurrentReferralAction
{
    public function execute(Request $request): ?EpiChannel
    {
        $epicCode = $this->resolveEpicCode($request);

        if ($epicCode === null) {
            return null;
        }

        return EpiChannel::query()
            ->with('user')
            ->where('epic_code', $epicCode)
            ->active()
            ->first();
    }

    protected function resolveEpicCode(Request $request): ?string
    {
        $queryRef = trim((string) $request->query('ref', ''));

        if ($queryRef !== '') {
            return $queryRef;
        }

        $sessionRef = $request->session()->get('epichub_referral');

        if (is_array($sessionRef)) {
            $epicCode = trim((string) ($sessionRef['epic_code'] ?? ''));

            if ($epicCode !== '') {
                return $epicCode;
            }
        }

        foreach (['epic_ref', 'epichub_ref'] as $cookieKey) {
            $payload = $request->cookie($cookieKey);

            if (! is_string($payload) || trim($payload) === '') {
                continue;
            }

            $decoded = json_decode($payload, true);

            if (! is_array($decoded)) {
                continue;
            }

            $epicCode = trim((string) ($decoded['epic_code'] ?? ''));

            if ($epicCode !== '') {
                return $epicCode;
            }
        }

        return null;
    }
}
