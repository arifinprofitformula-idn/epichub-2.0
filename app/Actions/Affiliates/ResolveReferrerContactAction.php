<?php

namespace App\Actions\Affiliates;

use App\Models\EpiChannel;
use App\Models\User;

class ResolveReferrerContactAction
{
    /**
     * @return array{
     *     sponsor_name: ?string,
     *     sponsor_epic_code: ?string,
     *     whatsapp_number: ?string,
     *     whatsapp_url: ?string,
     *     has_contact: bool
     * }
     */
    public function execute(User $user): array
    {
        $user->loadMissing('epiChannel', 'referrerEpiChannel.user');

        $ownChannel = $user->epiChannel;
        $referrerChannel = null;

        // Priority 1 — sponsor recorded on the user's own EpiChannel profile.
        // This is the sponsor the member should contact for channel onboarding.
        if (filled($ownChannel?->sponsor_epic_code)) {
            $referrerChannel = EpiChannel::query()
                ->with('user')
                ->where('epic_code', $ownChannel->sponsor_epic_code)
                ->first();
        }

        // Priority 2 — referral locked on the user account (set during registration).
        if (! $referrerChannel) {
            $referrerChannel = $user->referrerEpiChannel;
        }

        if (! $referrerChannel) {
            return [
                ...$this->emptyContact(),
                'sponsor_name' => $ownChannel?->sponsor_name,
                'sponsor_epic_code' => $ownChannel?->sponsor_epic_code,
            ];
        }

        $referrerChannel->loadMissing('user');

        $referrerUser = $referrerChannel->user;
        $whatsappNumber = $referrerUser?->whatsapp_number_for_url;
        $resolvedName = $referrerUser?->name
            ?? $referrerChannel->store_name
            ?? $referrerChannel->sponsor_name;

        return [
            'sponsor_name'      => $resolvedName,
            'sponsor_epic_code' => $referrerChannel->epic_code,
            'whatsapp_number'   => $whatsappNumber,
            'whatsapp_url'      => $whatsappNumber
                ? $this->buildWhatsappUrl($whatsappNumber, $resolvedName, $user)
                : null,
            'has_contact' => filled($whatsappNumber),
        ];
    }

    protected function buildWhatsappUrl(string $normalizedNumber, ?string $sponsorName, User $user): string
    {
        $message = "Assalamu'alaikum, saya {$user->name} dengan email {$user->email} ingin menanyakan aktivasi EPI Channel saya di EPIC Hub. Mohon arahannya.";

        return 'https://wa.me/'.$normalizedNumber.'?text='.rawurlencode($message);
    }

    /**
     * @return array{
     *     sponsor_name: null,
     *     sponsor_epic_code: null,
     *     whatsapp_number: null,
     *     whatsapp_url: null,
     *     has_contact: false
     * }
     */
    protected function emptyContact(): array
    {
        return [
            'sponsor_name' => null,
            'sponsor_epic_code' => null,
            'whatsapp_number' => null,
            'whatsapp_url' => null,
            'has_contact' => false,
        ];
    }
}
