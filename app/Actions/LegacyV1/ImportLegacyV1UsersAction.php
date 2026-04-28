<?php

namespace App\Actions\LegacyV1;

use App\Enums\EpiChannelStatus;
use App\Models\EpiChannel;
use App\Models\LegacyV1ImportBatch;
use App\Models\LegacyV1User;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Permission\Models\Role;
use Throwable;

class ImportLegacyV1UsersAction
{
    public function __construct(
        protected ParseLegacyV1CsvAction $parseCsv,
        protected NormalizeLegacyV1UserAction $normalizeLegacyUser,
        protected ResolveLegacyV1UserMatchAction $resolveUserMatch,
        protected RecordLegacyV1ImportErrorAction $recordImportError,
        protected GenerateLegacyMigrationReportAction $generateReport,
    ) {}

    public function execute(string $absolutePath, ?User $actor = null): LegacyV1ImportBatch
    {
        $parsed = $this->parseCsv->execute($absolutePath, $this->aliases(), [
            'name',
            'epic_id',
            'email',
            'whatsapp',
            'sponsor_epic_id',
            'city',
        ]);

        $batch = LegacyV1ImportBatch::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Legacy V1 Users - '.$parsed['file_name'],
            'source_type' => 'users',
            'status' => 'processing',
            'file_name' => $parsed['file_name'],
            'file_path' => $absolutePath,
            'file_hash' => $parsed['file_hash'],
            'file_size' => $parsed['file_size'],
            'imported_by' => $actor?->id,
            'started_at' => now(),
        ]);

        foreach ($parsed['rows'] as $row) {
            DB::transaction(function () use ($batch, $row): void {
                $normalized = $this->normalizeLegacyUser->execute($row);

                $legacyUser = LegacyV1User::query()->updateOrCreate(
                    [
                        'batch_id' => $batch->id,
                        'row_number' => (int) $row['line'],
                    ],
                    [
                        'status' => 'pending',
                        'match_status' => 'pending',
                        'sponsor_status' => 'pending',
                        'raw_name' => $this->nullableString($row['name'] ?? null),
                        'raw_epic_id' => $this->nullableString($row['epic_id'] ?? null),
                        'raw_email' => $this->nullableString($row['email'] ?? null),
                        'raw_whatsapp' => $this->nullableString($row['whatsapp'] ?? null),
                        'raw_sponsor_epic_id' => $this->nullableString($row['sponsor_epic_id'] ?? null),
                        'raw_city' => $this->nullableString($row['city'] ?? null),
                        'normalized_name' => $normalized['name'],
                        'normalized_epic_id' => $normalized['epic_id'],
                        'normalized_email' => $normalized['email'],
                        'normalized_whatsapp' => $normalized['whatsapp'],
                        'normalized_sponsor_epic_id' => $normalized['sponsor_epic_id'],
                        'normalized_city' => $normalized['city'],
                        'metadata' => [
                            'raw_row' => $row,
                        ],
                    ],
                );

                $this->processRow($batch, $legacyUser);
            });
        }

        $summary = $this->generateReport->execute($batch, persist: true);

        $batch->forceFill([
            'status' => (($summary['error_count'] ?? 0) > 0 || ($summary['conflict_count'] ?? 0) > 0) ? 'completed_with_issues' : 'completed',
            'completed_at' => now(),
        ])->save();

        return $batch->fresh();
    }

    protected function processRow(LegacyV1ImportBatch $batch, LegacyV1User $legacyUser): void
    {
        if ($legacyUser->normalized_epic_id === null && $legacyUser->normalized_email === null && $legacyUser->normalized_whatsapp === null) {
            $this->markConflict(
                $batch,
                $legacyUser,
                'missing_identifier',
                'Baris user legacy tidak memiliki ID EPIC, email, atau WhatsApp yang bisa dipakai untuk pencocokan.',
            );

            return;
        }

        if ($legacyUser->normalized_email !== null && ! filter_var($legacyUser->normalized_email, FILTER_VALIDATE_EMAIL)) {
            $this->markConflict(
                $batch,
                $legacyUser,
                'invalid_email',
                'Email legacy tidak valid.',
            );

            return;
        }

        $match = $this->resolveUserMatch->execute(
            epicId: $legacyUser->normalized_epic_id,
            email: $legacyUser->normalized_email,
            whatsapp: $legacyUser->normalized_whatsapp,
        );

        if ($match['conflict'] !== null) {
            $this->markConflict($batch, $legacyUser, 'identity_conflict', $match['conflict']);

            return;
        }

        $user = $match['user'];
        $created = false;
        $warnings = [];

        if (! $user && $legacyUser->normalized_email === null) {
            $this->markConflict(
                $batch,
                $legacyUser,
                'missing_email_for_new_user',
                'User baru tidak bisa dibuat tanpa email yang valid.',
            );

            return;
        }

        try {
            $user = DB::transaction(function () use ($legacyUser, $match, $user, &$created, &$warnings): User {
                $user = $user
                    ? User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail()
                    : null;

                if (! $user) {
                    $created = true;

                    $user = User::query()->create([
                        'name' => $legacyUser->normalized_name ?? 'Pengguna Legacy',
                        'legacy_epic_id' => $legacyUser->normalized_epic_id,
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

                    if ($legacyUser->normalized_whatsapp !== null) {
                        $currentWhatsapp = $user->normalizedWhatsappNumber($user->whatsapp_number);

                        if ($currentWhatsapp === null) {
                            $updates['whatsapp_number'] = $legacyUser->normalized_whatsapp;
                        } elseif ($currentWhatsapp !== $legacyUser->normalized_whatsapp) {
                            $warnings[] = 'WhatsApp legacy berbeda dengan WhatsApp user existing dan tidak dioverwrite otomatis.';
                        }
                    }

                    if ($legacyUser->normalized_email !== null && Str::lower($user->email) !== $legacyUser->normalized_email) {
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

                return $user;
            });
        } catch (Throwable $exception) {
            $this->markConflict($batch, $legacyUser, 'user_upsert_failed', $exception->getMessage());

            return;
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
                'sponsor_name' => $currentChannel->sponsor_name,
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

    protected function markConflict(LegacyV1ImportBatch $batch, LegacyV1User $legacyUser, string $code, string $message): void
    {
        $legacyUser->forceFill([
            'status' => 'conflict',
            'match_status' => 'conflict',
        ])->save();

        $this->recordImportError->execute(
            batch: $batch,
            scope: 'user',
            code: $code,
            message: $message,
            legacyUser: $legacyUser,
            severity: 'conflict',
            context: [
                'row_number' => $legacyUser->row_number,
                'email' => $legacyUser->normalized_email,
                'epic_id' => $legacyUser->normalized_epic_id,
                'whatsapp' => $legacyUser->normalized_whatsapp,
            ],
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function aliases(): array
    {
        return [
            'name' => ['name', 'nama', 'nama_lengkap', 'full_name', 'fullname'],
            'epic_id' => ['epic_id', 'id_epic', 'kode_epic', 'epic_code'],
            'email' => ['email', 'e_mail', 'alamat_email'],
            'whatsapp' => ['whatsapp', 'whatsapp_number', 'phone', 'nomor_hp', 'no_hp', 'no_wa'],
            'sponsor_epic_id' => ['sponsor', 'sponsor_epic_id', 'id_epic_sponsor', 'kode_epic_sponsor'],
            'city' => ['city', 'kota', 'city_name'],
        ];
    }

    protected function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

}
