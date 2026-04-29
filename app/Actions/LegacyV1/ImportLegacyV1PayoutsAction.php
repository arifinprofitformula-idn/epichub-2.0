<?php

namespace App\Actions\LegacyV1;

use App\Models\LegacyV1ImportBatch;
use App\Models\LegacyV1Payout;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ImportLegacyV1PayoutsAction
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
        $source = (array) config('legacy_v1.sources.payouts');
        $table = (string) data_get($source, 'table');
        $columns = (array) data_get($source, 'columns', []);
        $rows = DB::connection($connectionName)->table($table)->get();

        $batch ??= $dryRun
            ? null
            : $this->createBatch->execute('payouts_db', $actor, 'Legacy V1 Payouts from Database', [
                'connection' => $connectionName,
                'table' => $table,
            ]);

        $summary = [
            'source' => 'payouts',
            'table' => $table,
            'total_rows' => 0,
            'staged_rows' => 0,
            'duplicate_rows' => 0,
            'dry_run' => $dryRun,
        ];

        foreach ($rows as $row) {
            $summary['total_rows']++;
            $payload = $this->extractRow((array) $row, $columns);
            $normalizedUser = $this->normalizeLegacyUser->execute([
                'epic_id' => $payload['user_epic_id'],
                'email' => $payload['user_email'],
            ]);
            $match = $this->resolveUserMatch->execute(
                epicId: $normalizedUser['epic_id'],
                email: $normalizedUser['email'],
            );

            if ($dryRun) {
                $summary['staged_rows']++;

                continue;
            }

            $record = LegacyV1Payout::query()->firstOrNew([
                'import_key' => $this->buildImportKey($payload),
            ]);

            $exists = $record->exists;

            $record->fill([
                'batch_id' => $record->exists ? $record->batch_id : $batch->id,
                'legacy_payout_id' => $payload['legacy_payout_id'],
                'legacy_user_id' => $payload['legacy_user_id'],
                'legacy_user_epic_id' => $normalizedUser['epic_id'],
                'legacy_user_email' => $normalizedUser['email'],
                'user_id' => $match['user']?->id,
                'epi_channel_id' => $match['user']?->epiChannel?->id,
                'legacy_status' => $payload['status'],
                'normalized_status' => $this->normalizeStatus($payload['status']),
                'amount' => $this->decimal($payload['amount']),
                'requested_at' => $this->parseDate($payload['requested_at']),
                'approved_at' => $this->parseDate($payload['approved_at']),
                'paid_at' => $this->parseDate($payload['paid_at']),
                'migration_status' => $match['conflict'] ? 'conflict' : ($match['user'] ? 'resolved' : 'unresolved_user'),
                'source_note' => 'EPIC HUB 1.0',
                'raw_payload' => [
                    'source' => 'legacy_mysql',
                    'raw_row' => (array) $row,
                    'note' => $payload['note'],
                    'match_conflict' => $match['conflict'],
                ],
            ]);
            $record->save();

            $summary[$exists ? 'duplicate_rows' : 'staged_rows']++;
        }

        if ($batch) {
            $batch->forceFill([
                'status' => 'completed',
                'completed_at' => now(),
                'summary' => $summary,
            ])->save();
        }

        return ['batch' => $batch?->fresh(), 'summary' => $summary];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $columns
     * @return array<string, ?string>
     */
    protected function extractRow(array $row, array $columns): array
    {
        return [
            'legacy_payout_id' => $this->nullableString(data_get($row, (string) ($columns['legacy_payout_id'] ?? 'id'))),
            'legacy_user_id' => $this->nullableString(data_get($row, (string) ($columns['legacy_user_id'] ?? 'user_id'))),
            'user_epic_id' => $this->nullableString(data_get($row, (string) ($columns['user_epic_id'] ?? 'user_epic_id'))),
            'user_email' => $this->nullableString(data_get($row, (string) ($columns['user_email'] ?? 'user_email'))),
            'status' => $this->nullableString(data_get($row, (string) ($columns['status'] ?? 'status'))),
            'amount' => $this->nullableString(data_get($row, (string) ($columns['amount'] ?? 'amount'))),
            'requested_at' => $this->nullableString(data_get($row, (string) ($columns['requested_at'] ?? 'requested_at'))),
            'approved_at' => $this->nullableString(data_get($row, (string) ($columns['approved_at'] ?? 'approved_at'))),
            'paid_at' => $this->nullableString(data_get($row, (string) ($columns['paid_at'] ?? 'paid_at'))),
            'note' => $this->nullableString(data_get($row, (string) ($columns['note'] ?? 'note'))),
        ];
    }

    /**
     * @param  array<string, ?string>  $payload
     */
    protected function buildImportKey(array $payload): string
    {
        return hash('sha256', implode('|', [
            'payout',
            $payload['legacy_payout_id'] ?? '',
            $payload['legacy_user_id'] ?? '',
            $payload['amount'] ?? '',
            $payload['paid_at'] ?? '',
        ]));
    }

    protected function normalizeStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'paid', 'success', 'completed', 'settled' => 'paid',
            'approved' => 'approved',
            'pending', 'waiting', 'requested' => 'pending',
            'cancelled', 'canceled', 'rejected' => 'cancelled',
            default => 'unknown',
        };
    }

    protected function parseDate(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value);
    }

    protected function decimal(?string $value): string
    {
        return number_format((float) ($value ?: 0), 2, '.', '');
    }

    protected function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
