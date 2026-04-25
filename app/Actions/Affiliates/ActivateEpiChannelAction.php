<?php

namespace App\Actions\Affiliates;

use App\Enums\EpiChannelStatus;
use App\Models\EpiChannel;
use App\Models\User;

class ActivateEpiChannelAction
{
    public function execute(EpiChannel $channel, User $actor): EpiChannel
    {
        $channel->update([
            'status' => EpiChannelStatus::Active,
            'activated_at' => $channel->activated_at ?? now(),
            'suspended_at' => null,
            'metadata' => array_merge($channel->metadata ?? [], [
                'last_activated_by' => $actor->id,
                'last_activated_at' => now()->toISOString(),
            ]),
        ]);

        return $channel->refresh();
    }
}

