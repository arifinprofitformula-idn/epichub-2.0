<?php

namespace App\Actions\LegacyV1;

use App\Enums\EpiChannelStatus;
use App\Models\EpiChannel;
use App\Models\LegacyV1ImportBatch;
use App\Models\LegacyV1User;
use App\Models\LegacyV1UserMapping;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Throwable;

class UpsertLegacyV1UserIntoApplicationAction
{
    public function __construct(
        protected ResolveLegacyV1UserMatchAction $resolveUserMatch,
        protected RecordLegacyV1ImportErrorAction $recordImportError,
    ) {}

    public function execute(LegacyV1ImportBatch $batch, LegacyV1User $legacyUser): LegacyV1User
    {
        if ($legacyUser->normalized_epic_id === null && $legacyUser->normalized_email === null && $legacyUser->normalized_whatsapp === null) {
            return $this->markConflict(
                $batch,
                $legacyUser,
                'missing_identifier',
                'Baris user legacy tidak memiliki ID EPIC, email, atau WhatsApp yang bisa dipakai untuk pencocokan.',
            );
        }

        if ($legacyUser->normalized_email !== null && ! filter_var($legacyUser->normalized_email, FILTER_VALIDATE_EMAIL)) {
            return $this->markConflict(
                $batch,
                $legacyUser,
                'invalid_email',
                'Email legacy tidak valid.',
            );
        }

        $match = $this->resolveUserMatch->execute(
            epicId: $legacyUser->normalized_epic_id,
            email: $legacyUser->normalized_email,
            whatsapp: $legacyUser->normalized_whatsapp,
        );

        if ($match['conflict'] !== null) {
            return $this->markConflict($batch, $legacyUser, 'identity_conflict', $match['conflict']);
        }

        $user = $match['user'];
        $created = false;
        $warnings = [];

        if (! $user && $legacyUser->normalized_email === null) {
            return $this->markConflict(
                $batch,
                $legacyUser,
                'missing_email_for_new_user',
                'User baru tidak bisa dibuat tanpa email yang valid.',
            );
        }

        try {
            $user = DB::transaction(function () use ($legacyUser, $match, $user, &$created, &$warnings, $batch): User {
                $user = $user
                    ? User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail()
                    : null;

                if (! $user) {
                    $created = true;

                    $user = User::query()->create([
                        'name' => $legacyUser->normalized_name ?? 'Pengguna Legacy',
                        'legacy_source' => 'legacy_v1',
                        'legacy_user_id' => $legacyUser->legacy_user_id,
                        'legacy_epic_id' => $legacyUser->normalized_epic_id,
                        'legacy_import_batch_id' => $batch->id,
                        'legacy_imported_at' => now(),
                        'email' => $legacyUser->normalized_email,
                        'email_verified_at' => $legacyUser->normalized_email ? now() : null,
                        'password' => Hash::make(Str::random(40)),
                        'must_reset_password' => true,
                        'whatsapp_number' => $legacyUser->normalized_whatsapp,
                    ]);
                } else {
                    $updates = [];

                    if ($legacyUser->normalized_epic_id !== null) {
                        if ($user->legacy_epic_id !== null && $user->legacy_epic_id !== $legacyUser->normalized_epic_id) {
                            throw new RuntimeException('User existing sudah memiliki legacy EPIC ID lain.');
                        }

                        $updates['legacy_epic_id'] = $legacyUser->normalized_epic_id;
                    }

                    if ($user->legacy_source === null) {
                        $updates['legacy_source'] = 'legacy_v1';
                    }

                    if ($user->legacy_user_id === null && $legacyUser->legacy_user_id !== null) {
                        $updates['legacy_user_id'] = $legacyUser->legacy_user_id;
                    }

                    if ($user->legacy_import_batch_id === null) {
                        $updates['legacy_import_batch_id'] = $batch->id;
                    }

                    if ($user->legacy_imported_at === null) {
                        $updates['legacy_imported_at'] = now();
                    }

                    if ($legacyUser->normalized_whatsapp !== null) {
                        $currentWhatsapp = $user->normalizedWhatsappNumber($user->whatsapp_number);

                        if ($currentWhatsapp === null) {
                            $updates['whatsapp_number'] = $legacyUser->normalized_whatsapp;
                        } elseif ($currentWhatsapp !== $legacyUser->normalized_whatsapp) {
                            $warnings[] = 'WhatsApp legacy berbeda dengan WhatsApp user existing dan tidak dioverwrite otomatis.';
                        }
                    }

                    if ($legacyUser->normalized_email !== null && Str::lower((string) $user->email) !== $legacyUser->normalized_email) {
                        $warnings[] = 'Email legacy berbeda dengan email user existing dan tidak dioverwrite otomatis.';
                    }

                    if ($updates !== []) {
                        $user->update($updates);
                    }
                }

                $epiChannel = $this->syncEpiChannel($user, $legacyUser);

                $this->ensureRoles($user, $epiChannel !== null);

                $legacyUser->forceFill([
                    'status' => 'imported',
                    'match_status' => $created ? 'created' : ($match['matched_by'] ?? 'matched_existing'),
                    'matched_user_id' => $user->id,
                    'matched_by' => $created ? null : $match['matched_by'],
                    'imported_user_id' => $user->id,
                    'epi_channel_id' => $epiChannel?->id,
                    'imported_at' => now(),
                    'metadata' => array_merge($legacyUser->metadata ?? [], [
                        'warnings' => $warnings,
                        'created_user' => $created,
                    ]),
                ])->save();

                LegacyV1UserMapping::query()->updateOrCreate(
                    [
                        'batch_id' => $batch->id,
                        'legacy_v1_user_id' => $legacyUser->id,
                    ],
                    [
                        'legacy_user_id' => $legacyUser->legacy_user_id,
                        'legacy_epic_id' => $legacyUser->normalized_epic_id,
                        'legacy_email' => $legacyUser->normalized_email,
                        'legacy_whatsapp' => $legacyUser->normalized_whatsapp,
                        'user_id' => $user->id,
                        'match_method' => $created ? 'created' : ($match['matched_by'] ?? 'matched_existing'),
                        'status' => 'resolved',
                        'notes' => $created ? 'User baru dibuat dari batch legacy.' : 'User existing dipakai sebagai target mapping.',
                        'metadata' => [
                            'created_user' => $created,
                            'warnings' => $warnings,
                        ],
                    ],
                );

                return $user;
            });
        } catch (Throwable $exception) {
            return $this->markConflict($batch, $legacyUser, 'user_upsert_failed', $exception->getMessage());
        }

        foreach ($warnings as $warning) {
            $this->recordImportError->execute(
                batch: $batch,
                scope: 'user',
                code: 'data_warning',
                message: $warning,
                legacyUser: $legacyUser,
                severity: 'warning',
                context: [
                    'user_id' => $user->id,
                ],
            );
        }

        return $legacyUser->fresh();
    }

    protected function syncEpiChannel(User $user, LegacyV1User $legacyUser): ?EpiChannel
    {
        if ($legacyUser->normalized_epic_id === null) {
            return $user->epiChannel;
        }

        $existingChannel = EpiChannel::query()
            ->where('epic_code', $legacyUser->normalized_epic_id)
            ->lockForUpdate()
            ->first();

        $currentChannel = $user->epiChannel()->lockForUpdate()->first();

        if ($existingChannel && (int) $existingChannel->user_id !== (int) $user->id) {
            throw new RuntimeException('ID EPIC legacy sudah dipakai oleh user lain di EPIC HUB 2.0.');
        }

        if ($currentChannel && $currentChannel->epic_code !== $legacyUser->normalized_epic_id) {
            throw new RuntimeException('User existing sudah terhubung ke EPI Channel dengan kode EPIC yang berbeda.');
        }

        if ($currentChannel) {
            $currentChannel->forceFill([
                'sponsor_epic_code' => $legacyUser->normalized_sponsor_epic_id,
                'status' => $currentChannel->status ?: EpiChannelStatus::Active,
                'source' => $currentChannel->source ?: 'legacy_v1',
                'activated_at' => $currentChannel->activated_at ?? now(),
                'metadata' => array_merge($currentChannel->metadata ?? [], [
                    'legacy_import' => true,
                    'legacy_city' => $legacyUser->normalized_city,
                ]),
            ])->save();

            return $currentChannel->fresh();
        }

        return EpiChannel::query()->create([
            'user_id' => $user->id,
            'epic_code' => $legacyUser->normalized_epic_id,
            'store_name' => $user->name,
            'sponsor_epic_code' => $legacyUser->normalized_sponsor_epic_id,
            'status' => EpiChannelStatus::Active,
            'source' => 'legacy_v1',
            'activated_at' => now(),
            'metadata' => [
                'legacy_import' => true,
                'legacy_city' => $legacyUser->normalized_city,
            ],
        ]);
    }

    protected function ensureRoles(User $user, bool $hasEpiChannel): void
    {
        foreach (array_filter(['customer', $hasEpiChannel ? 'affiliate' : null]) as $roleName) {
            if (Role::query()->where('name', $roleName)->exists() && ! $user->hasRole($roleName)) {
                $user->assignRole($roleName);
            }
        }
    }

    protected function markConflict(LegacyV1ImportBatch $batch, LegacyV1User $legacyUser, string $code, string $message): LegacyV1User
    {
        $legacyUser->forceFill([
            'status' => 'conflict',
            'match_status' => 'conflict',
        ])->save();

        LegacyV1UserMapping::query()->updateOrCreate(
            [
                'batch_id' => $batch->id,
                'legacy_v1_user_id' => $legacyUser->id,
            ],
            [
                'legacy_user_id' => $legacyUser->legacy_user_id,
                'legacy_epic_id' => $legacyUser->normalized_epic_id,
                'legacy_email' => $legacyUser->normalized_email,
                'legacy_whatsapp' => $legacyUser->normalized_whatsapp,
                'user_id' => null,
                'match_method' => null,
                'status' => 'conflict',
                'notes' => $message,
            ],
        );

        $this->recordImportError->execute(
            batch: $batch,
            scope: 'user',
            code: $code,
            message: $message,
            legacyUser: $legacyUser,
            severity: 'conflict',
            context: [
                'row_number' => $legacyUser->row_number,
                'legacy_user_id' => $legacyUser->legacy_user_id,
                'email' => $legacyUser->normalized_email,
                'epic_id' => $legacyUser->normalized_epic_id,
                'whatsapp' => $legacyUser->normalized_whatsapp,
            ],
        );

        return $legacyUser->fresh();
    }
}
