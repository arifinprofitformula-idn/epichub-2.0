<?php

namespace App\Actions\Oms;

use App\Enums\EpiChannelStatus;
use App\Models\EpiChannel;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Spatie\Permission\Models\Role;

class UpsertOmsAccountAction
{
    /**
     * @return array{user: User, epiChannel: EpiChannel}
     */
    public function execute(
        string $epicCode,
        string $name,
        string $email,
        ?string $phone,
        ?string $storeName,
        ?string $sponsorEpicCode,
        ?string $sponsorName,
        string $plainPassword,
    ): array {
        $epicCode = trim($epicCode);
        $email = trim($email);

        if ($epicCode === '' || $email === '') {
            throw new RuntimeException('Invalid epic_code/email.');
        }

        $existingChannel = EpiChannel::query()->where('epic_code', $epicCode)->first();

        if ($existingChannel) {
            $user = $existingChannel->user()->first();

            if (! $user) {
                throw new RuntimeException('Invalid channel user.');
            }

            if (strtolower($user->email) !== strtolower($email)) {
                throw new RuntimeException('Email sudah terpakai untuk EPIC lain.');
            }

            $user->update([
                'name' => $name,
                'password' => Hash::make($plainPassword),
            ]);

            $existingChannel->update([
                'store_name' => $storeName,
                'sponsor_epic_code' => $sponsorEpicCode,
                'sponsor_name' => $sponsorName,
                'status' => EpiChannelStatus::Active,
                'activated_at' => $existingChannel->activated_at ?? now(),
                'metadata' => array_filter([
                    'phone' => $phone,
                ], fn ($v) => $v !== null && $v !== ''),
            ]);

            $this->ensureRoles($user);

            return [
                'user' => $user->refresh(),
                'epiChannel' => $existingChannel->refresh(),
            ];
        }

        $existingUser = User::query()->where('email', $email)->first();

        if ($existingUser && $existingUser->epiChannel && $existingUser->epiChannel->epic_code !== $epicCode) {
            throw new RuntimeException('Email sudah terpakai untuk EPIC lain.');
        }

        $user = $existingUser ?: User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($plainPassword),
        ]);

        $channel = EpiChannel::query()->create([
            'user_id' => $user->id,
            'epic_code' => $epicCode,
            'store_name' => $storeName,
            'sponsor_epic_code' => $sponsorEpicCode,
            'sponsor_name' => $sponsorName,
            'status' => EpiChannelStatus::Active,
            'source' => 'oms',
            'activated_at' => now(),
            'metadata' => array_filter([
                'phone' => $phone,
            ], fn ($v) => $v !== null && $v !== ''),
        ]);

        $this->ensureRoles($user);

        return [
            'user' => $user->refresh(),
            'epiChannel' => $channel->refresh(),
        ];
    }

    protected function ensureRoles(User $user): void
    {
        foreach (['customer', 'affiliate'] as $roleName) {
            if (Role::query()->where('name', $roleName)->exists()) {
                $user->assignRole($roleName);
            }
        }
    }
}

