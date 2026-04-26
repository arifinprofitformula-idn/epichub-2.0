<?php

namespace App\Actions\Affiliates;

use Illuminate\Http\Request;

class ResolveCurrentReferralAction
{
    public function __construct(
        protected ResolveReferralForUserAction $resolveReferralForUser,
    ) {
    }

    /**
     * @return array{channel: \App\Models\EpiChannel, source: string, is_locked: bool}
     */
    public function execute(Request $request): array
    {
        $user = $request->user();
        $resolved = $this->resolveReferralForUser->execute($user, $request);

        return [
            'channel' => $resolved['epiChannel'],
            'source' => $resolved['source'],
            'is_locked' => (bool) ($user?->referrer_epi_channel_id),
        ];
    }
}
