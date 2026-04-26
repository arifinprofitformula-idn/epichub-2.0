<?php

namespace App\Actions\Affiliates;

use App\Models\EpiChannel;
use App\Models\User;
use Illuminate\Http\Request;

class ResolveReferralForUserAction
{
    public function __construct(
        protected EnsureDefaultEpiChannelAction $ensureDefaultEpiChannel,
    ) {
    }

    /**
     * @return array{epiChannel: EpiChannel, source: string}
     */
    public function execute(?User $user = null, ?Request $request = null): array
    {
        $request ??= request();

        if ($user?->referrer_epi_channel_id) {
            $lockedChannel = $user->referrerEpiChannel()->with('user')->first();

            if ($lockedChannel) {
                return [
                    'epiChannel' => $lockedChannel,
                    'source' => (string) ($user->referral_source ?: 'locked'),
                ];
            }
        }

        $candidate = $this->resolveCandidateChannel($request);

        if ($candidate && ! $this->isSelfReferral($user, $candidate)) {
            return [
                'epiChannel' => $candidate,
                'source' => $request->query->has('ref') ? 'link' : 'cookie',
            ];
        }

        return [
            'epiChannel' => $this->ensureDefaultEpiChannel->execute()->loadMissing('user'),
            'source' => 'default_system',
        ];
    }

    protected function resolveCandidateChannel(?Request $request): ?EpiChannel
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

    protected function resolveEpicCode(?Request $request): ?string
    {
        if (! $request) {
            return null;
        }

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

    protected function isSelfReferral(?User $user, EpiChannel $channel): bool
    {
        if (! $user) {
            return false;
        }

        if ((int) $channel->user_id === (int) $user->id) {
            return true;
        }

        $user->loadMissing('epiChannel');

        return $user->epiChannel?->is($channel) ?? false;
    }
}
