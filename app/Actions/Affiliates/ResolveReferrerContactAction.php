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
        $user->loadMissing('epiChannel');

        $channel = $user->epiChannel;

        if (! $channel) {
            return $this->emptyContact();
        }

        $sponsorEpicCode = $channel->sponsor_epic_code;
        $sponsorName = $channel->sponsor_name;

        if (! filled($sponsorEpicCode)) {
            return [
                ...$this->emptyContact(),
                'sponsor_name' => $sponsorName,
            ];
        }

        $sponsorChannel = EpiChannel::query()
            ->with('user')
            ->where('epic_code', $sponsorEpicCode)
            ->first();

        $sponsorUser = $sponsorChannel?->user;
        $whatsappNumber = $sponsorUser?->whatsapp_number_for_url;
        $resolvedSponsorName = $sponsorUser?->name ?? $sponsorChannel?->sponsor_name ?? $sponsorChannel?->store_name ?? $sponsorName;

        return [
            'sponsor_name' => $resolvedSponsorName,
            'sponsor_epic_code' => $sponsorEpicCode,
            'whatsapp_number' => $whatsappNumber,
            'whatsapp_url' => $whatsappNumber ? $this->buildWhatsappUrl($whatsappNumber, $resolvedSponsorName, $user) : null,
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
