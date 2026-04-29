<?php

namespace App\Actions\LegacyV1;

use App\Models\LegacyV1ImportBatch;
use App\Models\LegacyV1ProductAccess;
use App\Models\LegacyV1ProductMapping;
use App\Models\LegacyV1User;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ImportLegacyV1ProductAccessesAction
{
    public function __construct(
        protected CreateLegacyImportBatchAction $createBatch,
        protected NormalizeLegacyV1UserAction $normalizeLegacyUser,
        protected ResolveLegacyV1UserMatchAction $resolveUserMatch,
    ) {}

    /**
     * @return array{batch: ?LegacyV1ImportBatch, summary: array<string, mixed>}
     */
    public function execute(?LegacyV1ImportBatch $batch = null, ?User $actor = null, bool $dryRun = false): array
    {
        $connectionName = (string) config('legacy_v1.connection', 'legacy_mysql');
        $source = (array) config('legacy_v1.sources.product_accesses');
        $table = (string) data_get($source, 'table');
        $columns = (array) data_get($source, 'columns', []);
        $rows = DB::connection($connectionName)->table($table)->get();

        $batch ??= $dryRun
            ? null
            : $this->createBatch->execute(
                sourceType: 'accesses_db',
                actor: $actor,
                name: 'Legacy V1 Product Accesses from Database',
                metadata: [
                    'connection' => $connectionName,
                    'table' => $table,
                ],
            );

        $summary = [
            'source' => 'product_accesses',
            'table' => $table,
            'total_rows' => 0,
            'staged_rows' => 0,
            'duplicate_rows' => 0,
            'dry_run' => $dryRun,
        ];

        foreach ($rows as $index => $row) {
            $summary['total_rows']++;

            $payload = $this->extractRow((array) $row, $columns);
            $normalized = $this->normalizeLegacyUser->execute($payload);
            $legacyProductKey = $this->normalizeLegacyProductKey($payload['legacy_product_key'] ?? $payload['legacy_product_name']);
            $importKey = hash('sha256', implode('|', [
                'access',
                $payload['legacy_access_id'] ?? '',
                $payload['legacy_user_id'] ?? '',
                $legacyProductKey ?? '',
                $payload['granted_at'] ?? '',
            ]));

            if ($dryRun) {
                $summary['staged_rows']++;

                continue;
            }

            $match = $this->resolveUserMatch->execute(
                epicId: $normalized['epic_id'],
                email: $normalized['email'],
                whatsapp: $normalized['whatsapp'],
            );

            $legacyUser = $this->resolveLegacyUser($payload['legacy_user_id'], $normalized['epic_id'], $normalized['email'], $normalized['whatsapp']);
            $mapping = $legacyProductKey
                ? LegacyV1ProductMapping::query()->where('legacy_product_key', $legacyProductKey)->where('is_active', true)->first()
                : null;

            $access = LegacyV1ProductAccess::query()->firstOrNew([
                'import_key' => $importKey,
            ]);

            $exists = $access->exists;

            $access->fill([
                'batch_id' => $access->exists ? $access->batch_id : $batch->id,
                'legacy_v1_user_id' => $legacyUser?->id,
                'legacy_access_id' => $payload['legacy_access_id'],
                'source_type' => 'database',
                'row_number' => $index + 1,
                'status' => $access->exists ? $access->status : 'staged',
                'raw_identifier_type' => $normalized['epic_id'] ? 'epic_id' : ($normalized['email'] ? 'email' : ($normalized['whatsapp'] ? 'whatsapp' : null)),
                'raw_identifier_value' => $normalized['epic_id'] ?? $normalized['email'] ?? $normalized['whatsapp'],
                'raw_legacy_product_key' => $payload['legacy_product_key'],
                'raw_legacy_product_name' => $payload['legacy_product_name'],
                'raw_granted_at' => $payload['granted_at'],
                'normalized_email' => $normalized['email'],
                'normalized_epic_id' => $normalized['epic_id'],
                'normalized_whatsapp' => $normalized['whatsapp'],
                'normalized_legacy_product_key' => $legacyProductKey,
                'matched_user_id' => $match['user']?->id,
                'matched_by' => $match['matched_by'],
                'product_mapping_id' => $mapping?->id,
                'mapped_product_id' => $mapping?->product_id,
                'metadata' => [
                    'source' => 'legacy_mysql',
                    'raw_row' => (array) $row,
                    'legacy_user_id' => $payload['legacy_user_id'],
                ],
            ]);
            $access->save();

            $summary[$exists ? 'duplicate_rows' : 'staged_rows']++;
        }

        if ($batch) {
            $batch->forceFill([
                'status' => 'completed',
                'completed_at' => now(),
                'summary' => $summary,
            ])->save();
        }

        return [
            'batch' => $batch?->fresh(),
            'summary' => $summary,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $columns
     * @return array<string, ?string>
     */
    protected function extractRow(array $row, array $columns): array
    {
        return [
            'legacy_access_id' => $this->nullableString(data_get($row, (string) ($columns['legacy_access_id'] ?? 'id'))),
            'legacy_user_id' => $this->nullableString(data_get($row, (string) ($columns['legacy_user_id'] ?? 'user_id'))),
            'epic_id' => $this->nullableString(data_get($row, (string) ($columns['epic_id'] ?? 'epic_id'))),
            'email' => $this->nullableString(data_get($row, (string) ($columns['email'] ?? 'email'))),
            'whatsapp' => $this->nullableString(data_get($row, (string) ($columns['whatsapp'] ?? 'whatsapp'))),
            'legacy_product_key' => $this->nullableString(data_get($row, (string) ($columns['legacy_product_key'] ?? 'product_code'))),
            'legacy_product_name' => $this->nullableString(data_get($row, (string) ($columns['legacy_product_name'] ?? 'product_name'))),
            'granted_at' => $this->nullableString(data_get($row, (string) ($columns['granted_at'] ?? 'granted_at'))),
        ];
    }

    protected function resolveLegacyUser(?string $legacyUserId, ?string $epicId, ?string $email, ?string $whatsapp): ?LegacyV1User
    {
        $query = LegacyV1User::query();
        $hasCondition = false;

        foreach ([
            ['legacy_user_id', $legacyUserId],
            ['normalized_epic_id', $epicId],
            ['normalized_email', $email],
            ['normalized_whatsapp', $whatsapp],
        ] as [$column, $value]) {
            if ($value === null) {
                continue;
            }

            if (! $hasCondition) {
                $query->where($column, $value);
                $hasCondition = true;
            } else {
                $query->orWhere($column, $value);
            }
        }

        return $hasCondition ? $query->latest('id')->first() : null;
    }

    protected function normalizeLegacyProductKey(?string $value): ?string
    {
        $value = trim(strtolower((string) $value));

        return $value !== '' ? $value : null;
    }

    protected function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
