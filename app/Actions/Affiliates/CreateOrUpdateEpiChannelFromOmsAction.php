<?php

namespace App\Actions\Affiliates;

use App\Actions\Support\NormalizeWhatsappNumberAction;
use App\Enums\EpiChannelStatus;
use App\Models\EpiChannel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Spatie\Permission\Models\Role;

class CreateOrUpdateEpiChannelFromOmsAction
{
    public function __construct(
        protected NormalizeWhatsappNumberAction $normalizeWhatsappNumber,
    ) {
    }

    /**
     * @param  array{
     *     epic_code: string,
     *     name: string,
     *     email: string,
     *     whatsapp_number?: ?string,
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
        $whatsappNumber = $this->normalizeWhatsappNumber->execute($this->nullableString($payload['whatsapp_number'] ?? null));
        $storeName = $this->nullableString($payload['store_name'] ?? null);
        $sponsorEpicCode = $this->nullableString($payload['sponsor_epic_code'] ?? null);
        $sponsorName = $this->nullableString($payload['sponsor_name'] ?? null);

        if ($epicCode === '' || $email === '' || $name === '') {
            throw new RuntimeException('Payload OMS tidak valid.');
        }

        return DB::transaction(function () use ($epicCode, $email, $name, $whatsappNumber, $storeName, $sponsorEpicCode, $sponsorName, $plainPassword): array {
            $channel = EpiChannel::query()
                ->withTrashed()
                ->where('epic_code', $epicCode)
                ->lockForUpdate()
                ->first();

            if ($channel) {
                if ($channel->trashed()) {
                    $channel->restore();
                }

                $user = User::query()->lockForUpdate()->find($channel->user_id);

                if (! $user) {
                    throw new RuntimeException('Akun EPI Channel tidak ditemukan.');
                }

                $emailOwner = User::query()
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->lockForUpdate()
                    ->first();

                if ($emailOwner && ! $emailOwner->is($user)) {
                    throw new RuntimeException('Email sudah terdaftar dengan kode EPIC berbeda.');
                }

                $this->ensureWhatsappAvailable($whatsappNumber, $user->id);

                $user->fill($this->buildUserData($user, $name, $email, $whatsappNumber, false, $plainPassword));
                $user->save();

                $channel->update([
                    'store_name' => $storeName ?? $channel->store_name,
                    'sponsor_epic_code' => $sponsorEpicCode ?? $channel->sponsor_epic_code,
                    'sponsor_name' => $sponsorName ?? $channel->sponsor_name,
                    'status' => EpiChannelStatus::Active,
                    'source' => $channel->source ?: 'oms',
                    'activated_at' => $channel->activated_at ?? now(),
                    'suspended_at' => null,
                    'metadata' => $this->mergeMetadata($channel->metadata, $whatsappNumber),
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

            $existingUserChannel = $existingUser?->epiChannel()->withTrashed()->lockForUpdate()->first();

            if ($existingUserChannel && strtoupper((string) $existingUserChannel->epic_code) !== $epicCode) {
                throw new RuntimeException('Email sudah terdaftar dengan kode EPIC berbeda.');
            }

            $this->ensureWhatsappAvailable($whatsappNumber, $existingUser?->id);

            $user = $existingUser ?? new User();
            $user->fill($this->buildUserData($user, $name, $email, $whatsappNumber, $existingUser === null, $plainPassword));
            $user->save();

            $channel = $existingUserChannel;

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
                    'metadata' => $this->mergeMetadata(null, $whatsappNumber),
                ]);
            } else {
                if ($channel->trashed()) {
                    $channel->restore();
                }

                $channel->update([
                    'user_id' => $user->id,
                    'epic_code' => $epicCode,
                    'store_name' => $storeName ?? $channel->store_name,
                    'sponsor_epic_code' => $sponsorEpicCode ?? $channel->sponsor_epic_code,
                    'sponsor_name' => $sponsorName ?? $channel->sponsor_name,
                    'status' => EpiChannelStatus::Active,
                    'source' => $channel->source ?: 'oms',
                    'activated_at' => $channel->activated_at ?? now(),
                    'suspended_at' => null,
                    'metadata' => $this->mergeMetadata($channel->metadata, $whatsappNumber),
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
    protected function buildUserData(User $user, string $name, string $email, ?string $whatsappNumber, bool $writePassword, string $plainPassword): array
    {
        $data = [
            'name' => $name,
            'email' => $email,
        ];

        if ($writePassword) {
            $data['password'] = Hash::make($plainPassword);
        }

        if ($whatsappNumber !== null) {
            $data['whatsapp_number'] = $whatsappNumber;
        }

        // Keep guardrails for models that use guarded properties.
        return array_intersect_key($data, array_flip($user->getFillable() ?: array_keys($data)));
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     * @return array<string, mixed>
     */
    protected function mergeMetadata(?array $metadata, ?string $whatsappNumber): array
    {
        $base = $metadata ?? [];

        if ($whatsappNumber !== null) {
            $base['whatsapp_number'] = $whatsappNumber;
        }

        return $base;
    }

    protected function ensureWhatsappAvailable(?string $whatsappNumber, ?int $ignoreUserId = null): void
    {
        if ($whatsappNumber === null) {
            return;
        }

        $query = User::query()->where('whatsapp_number', $whatsappNumber);

        if ($ignoreUserId !== null) {
            $query->where('id', '!=', $ignoreUserId);
        }

        if ($query->exists()) {
            throw new RuntimeException('Nomor WhatsApp sudah terdaftar pada akun lain.');
        }
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
