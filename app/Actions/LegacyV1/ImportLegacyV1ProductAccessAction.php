<?php

namespace App\Actions\LegacyV1;

use App\Models\LegacyV1ImportBatch;
use App\Models\LegacyV1ProductAccess;
use App\Models\LegacyV1ProductMapping;
use App\Models\LegacyV1User;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ImportLegacyV1ProductAccessAction
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
            'epic_id',
            'email',
            'whatsapp',
            'legacy_product_key',
            'legacy_product_name',
            'granted_at',
        ]);

        $batch = LegacyV1ImportBatch::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Legacy V1 Accesses - '.$parsed['file_name'],
            'source_type' => 'accesses',
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
                $normalized = $this->normalizeLegacyUser->execute([
                    'epic_id' => $row['epic_id'] ?? null,
                    'email' => $row['email'] ?? null,
                    'whatsapp' => $row['whatsapp'] ?? null,
                ]);

                $legacyProductKey = $this->normalizeLegacyProductKey(
                    $row['legacy_product_key'] ?? $row['legacy_product_name'] ?? null,
                );

                $legacyUser = $this->resolveLegacyUserLink(
                    epicId: $normalized['epic_id'],
                    email: $normalized['email'],
                    whatsapp: $normalized['whatsapp'],
                );

                $match = $this->resolveUserMatch->execute(
                    epicId: $normalized['epic_id'],
                    email: $normalized['email'],
                    whatsapp: $normalized['whatsapp'],
                );

                $mapping = $legacyProductKey
                    ? LegacyV1ProductMapping::query()->where('legacy_product_key', $legacyProductKey)->where('is_active', true)->first()
                    : null;

                $access = LegacyV1ProductAccess::query()->updateOrCreate(
                    [
                        'batch_id' => $batch->id,
                        'row_number' => (int) $row['line'],
                    ],
                    [
                        'legacy_v1_user_id' => $legacyUser?->id,
                        'status' => 'staged',
                        'raw_identifier_type' => $this->rawIdentifierType($normalized['epic_id'], $normalized['email'], $normalized['whatsapp']),
                        'raw_identifier_value' => $this->rawIdentifierValue($normalized['epic_id'], $normalized['email'], $normalized['whatsapp']),
                        'raw_legacy_product_key' => $this->nullableString($row['legacy_product_key'] ?? null),
                        'raw_legacy_product_name' => $this->nullableString($row['legacy_product_name'] ?? null),
                        'raw_granted_at' => $this->nullableString($row['granted_at'] ?? null),
                        'normalized_email' => $normalized['email'],
                        'normalized_epic_id' => $normalized['epic_id'],
                        'normalized_whatsapp' => $normalized['whatsapp'],
                        'normalized_legacy_product_key' => $legacyProductKey,
                        'matched_user_id' => $match['user']?->id,
                        'matched_by' => $match['matched_by'],
                        'product_mapping_id' => $mapping?->id,
                        'mapped_product_id' => $mapping?->product_id,
                        'metadata' => [
                            'raw_row' => $row,
                        ],
                    ],
                );

                $this->validateAccessRow($batch, $access, $match['conflict']);
            });
        }

        $summary = $this->generateReport->execute($batch, persist: true);

        $batch->forceFill([
            'status' => (($summary['error_count'] ?? 0) > 0 || ($summary['conflict_count'] ?? 0) > 0) ? 'completed_with_issues' : 'completed',
            'completed_at' => now(),
        ])->save();

        return $batch->fresh();
    }

    protected function validateAccessRow(LegacyV1ImportBatch $batch, LegacyV1ProductAccess $access, ?string $matchConflict): void
    {
        if ($access->normalized_epic_id === null && $access->normalized_email === null && $access->normalized_whatsapp === null) {
            $access->forceFill(['status' => 'conflict'])->save();

            $this->recordImportError->execute(
                batch: $batch,
                scope: 'access',
                code: 'missing_identifier',
                message: 'Baris akses produk legacy tidak memiliki identifier user yang bisa dipakai.',
                legacyProductAccess: $access,
                severity: 'conflict',
            );

            return;
        }

        if ($access->normalized_email !== null && ! filter_var($access->normalized_email, FILTER_VALIDATE_EMAIL)) {
            $access->forceFill(['status' => 'conflict'])->save();

            $this->recordImportError->execute(
                batch: $batch,
                scope: 'access',
                code: 'invalid_email',
                message: 'Email pada baris akses produk legacy tidak valid.',
                legacyProductAccess: $access,
                severity: 'conflict',
            );

            return;
        }

        if ($matchConflict !== null) {
            $access->forceFill(['status' => 'conflict'])->save();

            $this->recordImportError->execute(
                batch: $batch,
                scope: 'access',
                code: 'identity_conflict',
                message: $matchConflict,
                legacyProductAccess: $access,
                severity: 'conflict',
            );

            return;
        }

        if ($access->normalized_legacy_product_key === null) {
            $access->forceFill(['status' => 'conflict'])->save();

            $this->recordImportError->execute(
                batch: $batch,
                scope: 'access',
                code: 'missing_legacy_product_key',
                message: 'Baris akses produk legacy tidak memiliki legacy_product_key atau legacy_product_name yang bisa dipakai mapping.',
                legacyProductAccess: $access,
                severity: 'conflict',
            );

            return;
        }

        if ($access->raw_granted_at !== null) {
            try {
                Carbon::parse($access->raw_granted_at);
            } catch (Throwable) {
                $access->forceFill(['status' => 'error'])->save();

                $this->recordImportError->execute(
                    batch: $batch,
                    scope: 'access',
                    code: 'invalid_granted_at',
                    message: 'Format granted_at tidak valid.',
                    legacyProductAccess: $access,
                    severity: 'error',
                );
            }
        }
    }

    protected function resolveLegacyUserLink(?string $epicId, ?string $email, ?string $whatsapp): ?LegacyV1User
    {
        if ($epicId !== null) {
            $legacyUser = LegacyV1User::query()->where('normalized_epic_id', $epicId)->latest('id')->first();

            if ($legacyUser) {
                return $legacyUser;
            }
        }

        if ($email !== null) {
            $legacyUser = LegacyV1User::query()->where('normalized_email', $email)->latest('id')->first();

            if ($legacyUser) {
                return $legacyUser;
            }
        }

        if ($whatsapp !== null) {
            return LegacyV1User::query()->where('normalized_whatsapp', $whatsapp)->latest('id')->first();
        }

        return null;
    }

    protected function normalizeLegacyProductKey(mixed $value): ?string
    {
        $value = trim(Str::lower((string) $value));

        return $value !== '' ? $value : null;
    }

    protected function rawIdentifierType(?string $epicId, ?string $email, ?string $whatsapp): ?string
    {
        return match (true) {
            $epicId !== null => 'epic_id',
            $email !== null => 'email',
            $whatsapp !== null => 'whatsapp',
            default => null,
        };
    }

    protected function rawIdentifierValue(?string $epicId, ?string $email, ?string $whatsapp): ?string
    {
        return $epicId ?? $email ?? $whatsapp;
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function aliases(): array
    {
        return [
            'epic_id' => ['epic_id', 'id_epic', 'kode_epic', 'epic_code'],
            'email' => ['email', 'e_mail'],
            'whatsapp' => ['whatsapp', 'whatsapp_number', 'phone', 'nomor_hp', 'no_hp', 'no_wa'],
            'legacy_product_key' => ['legacy_product_key', 'legacy_product_id', 'old_product_key', 'product_key', 'kode_produk_lama'],
            'legacy_product_name' => ['legacy_product_name', 'old_product_name', 'product_name', 'nama_produk_lama'],
            'granted_at' => ['granted_at', 'access_granted_at', 'tanggal_grant', 'tanggal_aktif'],
        ];
    }

    protected function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
