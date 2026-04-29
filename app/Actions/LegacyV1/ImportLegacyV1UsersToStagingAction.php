<?php

namespace App\Actions\LegacyV1;

use App\Models\LegacyV1ImportBatch;
use App\Models\LegacyV1User;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportLegacyV1UsersToStagingAction
{
    public function __construct(
        protected CreateLegacyImportBatchAction $createBatch,
        protected NormalizeLegacyV1UserAction $normalizeLegacyUser,
    ) {}

    /**
     * @return array{batch: ?LegacyV1ImportBatch, summary: array<string, mixed>}
     */
    public function execute(?LegacyV1ImportBatch $batch = null, ?User $actor = null, bool $dryRun = false): array
    {
        $connectionName = (string) config('legacy_v1.connection', 'legacy_mysql');
        $source = (array) config('legacy_v1.sources.users');
        $table = (string) data_get($source, 'table');
        $columns = (array) data_get($source, 'columns', []);
        $connection = DB::connection($connectionName);
        $rows = $connection->table($table)->get();

        $batch ??= $dryRun
            ? null
            : $this->createBatch->execute(
                sourceType: 'users_db',
                actor: $actor,
                name: 'Legacy V1 Users from Database',
                metadata: [
                    'connection' => $connectionName,
                    'table' => $table,
                    'dry_run' => false,
                ],
            );

        $summary = [
            'source' => 'users',
            'connection' => $connectionName,
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
            $importKey = $this->buildImportKey($payload, $normalized);

            if ($dryRun) {
                $summary['staged_rows']++;

                continue;
            }

            $legacyUser = LegacyV1User::query()->firstOrNew([
                'import_key' => $importKey,
            ]);

            $exists = $legacyUser->exists;

            $legacyUser->fill([
                'batch_id' => $legacyUser->exists ? $legacyUser->batch_id : $batch->id,
                'row_number' => $index + 1,
                'legacy_user_id' => $payload['legacy_user_id'],
                'source_type' => 'database',
                'status' => $legacyUser->exists ? $legacyUser->status : 'staged',
                'match_status' => $legacyUser->exists ? $legacyUser->match_status : 'pending',
                'sponsor_status' => $legacyUser->exists ? $legacyUser->sponsor_status : 'pending',
                'raw_name' => $payload['name'],
                'raw_epic_id' => $payload['epic_id'],
                'raw_email' => $payload['email'],
                'raw_whatsapp' => $payload['whatsapp'],
                'raw_sponsor_epic_id' => $payload['sponsor_epic_id'],
                'raw_city' => $payload['city'],
                'normalized_name' => $normalized['name'],
                'normalized_epic_id' => $normalized['epic_id'],
                'normalized_email' => $normalized['email'],
                'normalized_whatsapp' => $normalized['whatsapp'],
                'normalized_sponsor_epic_id' => $normalized['sponsor_epic_id'],
                'normalized_city' => $normalized['city'],
                'metadata' => [
                    'source' => 'legacy_mysql',
                    'raw_row' => (array) $row,
                    'password_hash_available' => $payload['password_hash'] !== null,
                ],
            ]);
            $legacyUser->save();

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
            'legacy_user_id' => $this->nullableString(data_get($row, (string) ($columns['legacy_user_id'] ?? 'legacy_user_id'))),
            'name' => $this->nullableString(data_get($row, (string) ($columns['name'] ?? 'name'))),
            'epic_id' => $this->nullableString(data_get($row, (string) ($columns['epic_id'] ?? 'epic_id'))),
            'email' => $this->nullableString(data_get($row, (string) ($columns['email'] ?? 'email'))),
            'whatsapp' => $this->nullableString(data_get($row, (string) ($columns['whatsapp'] ?? 'whatsapp'))),
            'sponsor_epic_id' => $this->nullableString(data_get($row, (string) ($columns['sponsor_epic_id'] ?? 'sponsor_epic_id'))),
            'city' => $this->nullableString(data_get($row, (string) ($columns['city'] ?? 'city'))),
            'password_hash' => $this->nullableString(data_get($row, (string) ($columns['password_hash'] ?? 'password'))),
        ];
    }

    /**
     * @param  array<string, ?string>  $payload
     * @param  array{name: ?string, epic_id: ?string, email: ?string, whatsapp: ?string, sponsor_epic_id: ?string, city: ?string}  $normalized
     */
    protected function buildImportKey(array $payload, array $normalized): string
    {
        return hash('sha256', implode('|', [
            'user',
            $payload['legacy_user_id'] ?? '',
            $normalized['epic_id'] ?? '',
            $normalized['email'] ?? '',
            $normalized['whatsapp'] ?? '',
        ]));
    }

    protected function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
