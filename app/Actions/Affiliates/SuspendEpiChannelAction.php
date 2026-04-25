<?php

namespace App\Actions\Affiliates;

use App\Enums\EpiChannelStatus;
use App\Models\EpiChannel;
use App\Models\User;

class SuspendEpiChannelAction
{
    public function execute(EpiChannel $channel, User $actor): EpiChannel
    {
        $channel->update([
            'status' => EpiChannelStatus::Suspended,
            'suspended_at' => now(),
            'metadata' => array_merge($channel->metadata ?? [], [
                'last_suspended_by' => $actor->id,
                'last_suspended_at' => now()->toISOString(),
            ]),
        ]);

        return $channel->refresh();
    }
}

