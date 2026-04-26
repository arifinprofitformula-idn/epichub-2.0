<?php

namespace App\Actions\Affiliates;

use App\Enums\EpiChannelStatus;
use App\Models\EpiChannel;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EnsureDefaultEpiChannelAction
{
    public function execute(): EpiChannel
    {
        $epicCode = (string) config('epichub.default_referrer_epic_code', 'EPIC-HOUSE');

        $channel = EpiChannel::query()
            ->where('epic_code', $epicCode)
            ->first();

        if ($channel) {
            $channel->forceFill([
                'store_name' => 'EPIC Hub Official',
                'status' => EpiChannelStatus::Active,
                'source' => 'system',
                'activated_at' => $channel->activated_at ?? now(),
                'metadata' => array_merge($channel->metadata ?? [], ['is_house_channel' => true]),
            ])->save();

            return $channel->fresh();
        }

        $owner = $this->resolveOwner();

        return EpiChannel::query()->create([
            'user_id' => $owner->id,
            'epic_code' => $epicCode,
            'store_name' => 'EPIC Hub Official',
            'status' => EpiChannelStatus::Active,
            'source' => 'system',
            'activated_at' => now(),
            'metadata' => [
                'is_house_channel' => true,
            ],
        ]);
    }

    protected function resolveOwner(): User
    {
        $owner = User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['super_admin', 'admin']))
            ->orderBy('id')
            ->first();

        if ($owner) {
            return $owner;
        }

        $configuredEmail = (string) config('epichub.default_referrer_owner_email', 'system-referrer@epichub.local');

        return User::query()->firstOrCreate(
            ['email' => $configuredEmail],
            [
                'name' => 'EPIC Hub System',
                'password' => Hash::make(Str::random(32)),
            ],
        );
    }
}
