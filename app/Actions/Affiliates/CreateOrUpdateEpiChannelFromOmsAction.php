<?php

namespace App\Actions\Affiliates;

use App\Enums\EpiChannelStatus;
use App\Models\EpiChannel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Spatie\Permission\Models\Role;

class CreateOrUpdateEpiChannelFromOmsAction
{
    /**
     * @param  array{
     *     epic_code: string,
     *     name: string,
     *     email: string,
     *     phone?: ?string,
     *     store_name?: ?string,
     *     sponsor_epic_code?: ?string,
     *     sponsor_name?: ?string
     * }  $payload
     * @return array{user: User, epi_channel: EpiChannel}
     */
    public function execute(array $payload, string $plainPassword): array
    {
        $epicCode = strtoupper(trim((string) $payload['epic_code']));
        $email = strtolower(trim((string) $payload['email']));
        $name = trim((string) $payload['name']);
        $phone = $this->nullableString($payload['phone'] ?? null);
        $storeName = $this->nullableString($payload['store_name'] ?? null);
        $sponsorEpicCode = $this->nullableString($payload['sponsor_epic_code'] ?? null);
        $sponsorName = $this->nullableString($payload['sponsor_name'] ?? null);

        if ($epicCode === '' || $email === '' || $name === '') {
            throw new RuntimeException('Data OMS tidak valid.');
        }

        return DB::transaction(function () use ($epicCode, $email, $name, $phone, $storeName, $sponsorEpicCode, $sponsorName, $plainPassword): array {
            $channel = EpiChannel::query()
                ->where('epic_code', $epicCode)
                ->lockForUpdate()
                ->first();

            if ($channel) {
                $user = $channel->user()->lockForUpdate()->first();

                if (! $user || strtolower((string) $user->email) !== $email) {
                    throw new RuntimeException('Email EPIC tidak cocok dengan kode EPIC.');
                }

                $user->update($this->buildUserData($user, $name, $email, $phone, false, $plainPassword));

                $channel->update([
                    'store_name' => $storeName,
                    'sponsor_epic_code' => $sponsorEpicCode,
                    'sponsor_name' => $sponsorName,
                    'status' => EpiChannelStatus::Active,
                    'source' => $channel->source ?: 'oms',
                    'activated_at' => $channel->activated_at ?? now(),
                    'suspended_at' => null,
                    'metadata' => $this->mergeMetadata($channel->metadata, $phone),
                ]);

                $this->ensureRoles($user);

                return [
                    'user' => $user->refresh(),
                    'epi_channel' => $channel->refresh(),
                ];
            }

            $existingUser = User::query()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->lockForUpdate()
                ->first();

            if ($existingUser?->epiChannel && strtoupper((string) $existingUser->epiChannel->epic_code) !== $epicCode) {
                throw new RuntimeException('Email sudah dipakai untuk kode EPIC lain.');
            }

            $user = $existingUser ?? new User();
            $user->fill($this->buildUserData($user, $name, $email, $phone, true, $plainPassword));
            $user->save();

            $channel = $existingUser?->epiChannel;

            if (! $channel) {
                $channel = EpiChannel::query()->create([
                    'user_id' => $user->id,
                    'epic_code' => $epicCode,
                    'store_name' => $storeName,
                    'sponsor_epic_code' => $sponsorEpicCode,
                    'sponsor_name' => $sponsorName,
                    'status' => EpiChannelStatus::Active,
                    'source' => 'oms',
                    'activated_at' => now(),
                    'metadata' => $this->mergeMetadata(null, $phone),
                ]);
            } else {
                $channel->update([
                    'epic_code' => $epicCode,
                    'store_name' => $storeName,
                    'sponsor_epic_code' => $sponsorEpicCode,
                    'sponsor_name' => $sponsorName,
                    'status' => EpiChannelStatus::Active,
                    'source' => $channel->source ?: 'oms',
                    'activated_at' => $channel->activated_at ?? now(),
                    'suspended_at' => null,
                    'metadata' => $this->mergeMetadata($channel->metadata, $phone),
                ]);
            }

            $this->ensureRoles($user);

            return [
                'user' => $user->refresh(),
                'epi_channel' => $channel->refresh(),
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildUserData(User $user, string $name, string $email, ?string $phone, bool $writePassword, string $plainPassword): array
    {
        $data = [
            'name' => $name,
            'email' => $email,
        ];

        if ($writePassword) {
            $data['password'] = Hash::make($plainPassword);
        }

        if (Schema::hasColumn('users', 'phone') && $phone !== null) {
            $data['phone'] = $phone;
        }

        // Keep guardrails for models that use guarded properties.
        return array_intersect_key($data, array_flip($user->getFillable() ?: array_keys($data)));
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     * @return array<string, mixed>
     */
    protected function mergeMetadata(?array $metadata, ?string $phone): array
    {
        $base = $metadata ?? [];

        if ($phone !== null) {
            $base['phone'] = $phone;
        }

        return $base;
    }

    protected function ensureRoles(User $user): void
    {
        foreach (['customer', 'affiliate'] as $roleName) {
            if (Role::query()->where('name', $roleName)->exists()) {
                $user->assignRole($roleName);
            }
        }
    }

    protected function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
