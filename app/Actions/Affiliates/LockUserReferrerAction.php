<?php

namespace App\Actions\Affiliates;

use App\Models\EpiChannel;
use App\Models\User;

class LockUserReferrerAction
{
    public function __construct(
        protected EnsureDefaultEpiChannelAction $ensureDefaultEpiChannel,
    ) {
    }

    public function execute(User $user, ?EpiChannel $epiChannel, ?string $source = null): User
    {
        $user->loadMissing('epiChannel', 'referrerEpiChannel');

        if ($user->referrer_epi_channel_id) {
            return $user->fresh(['referrerEpiChannel.user']);
        }

        $channel = $epiChannel;

        if (! $channel || $this->isSelfReferral($user, $channel)) {
            $channel = $this->ensureDefaultEpiChannel->execute();
            $source = 'default_system';
        }

        $user->forceFill([
            'referrer_epi_channel_id' => $channel->id,
            'referral_locked_at' => now(),
            'referral_source' => $source ?: 'default_system',
        ])->save();

        return $user->fresh(['referrerEpiChannel.user']);
    }

    protected function isSelfReferral(User $user, EpiChannel $channel): bool
    {
        if ((int) $channel->user_id === (int) $user->id) {
            return true;
        }

        return $user->epiChannel?->is($channel) ?? false;
    }
}
