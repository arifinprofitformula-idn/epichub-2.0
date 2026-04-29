<?php

namespace App\Actions\LegacyV1;

use App\Models\LegacyV1ImportBatch;
use App\Models\LegacyV1Order;
use App\Models\LegacyV1Payment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ImportLegacyV1PaymentsAction
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
        $source = (array) config('legacy_v1.sources.payments');
        $table = (string) data_get($source, 'table');
        $columns = (array) data_get($source, 'columns', []);
        $rows = DB::connection($connectionName)->table($table)->get();

        $batch ??= $dryRun
            ? null
            : $this->createBatch->execute('payments_db', $actor, 'Legacy V1 Payments from Database', [
                'connection' => $connectionName,
                'table' => $table,
            ]);

        $summary = [
            'source' => 'payments',
            'table' => $table,
            'total_rows' => 0,
            'staged_rows' => 0,
            'duplicate_rows' => 0,
            'dry_run' => $dryRun,
        ];

        foreach ($rows as $row) {
            $summary['total_rows']++;
            $payload = $this->extractRow((array) $row, $columns);
            $normalizedUser = $this->normalizeLegacyV1User($payload);
            $match = $this->resolveUserMatch->execute(
                epicId: $normalizedUser['epic_id'],
                email: $normalizedUser['email'],
                whatsapp: null,
            );

            if ($dryRun) {
                $summary['staged_rows']++;

                continue;
            }

            $legacyOrder = $payload['legacy_order_id']
                ? LegacyV1Order::query()->where('legacy_order_id', $payload['legacy_order_id'])->latest('id')->first()
                : null;

            $record = LegacyV1Payment::query()->firstOrNew([
                'import_key' => $this->buildImportKey($payload),
            ]);

            $exists = $record->exists;

            $record->fill([
                'batch_id' => $record->exists ? $record->batch_id : $batch->id,
                'legacy_payment_id' => $payload['legacy_payment_id'],
                'legacy_payment_number' => $payload['legacy_payment_number'],
                'legacy_order_id' => $payload['legacy_order_id'],
                'legacy_v1_order_id' => $legacyOrder?->id,
                'legacy_user_id' => $payload['legacy_user_id'],
                'legacy_user_epic_id' => $normalizedUser['epic_id'],
                'legacy_user_email' => $normalizedUser['email'],
                'user_id' => $match['user']?->id,
                'legacy_status' => $payload['status'],
                'normalized_status' => $this->normalizeStatus($payload['status']),
                'payment_method' => $payload['payment_method'],
                'provider' => $payload['provider'],
                'provider_reference' => $payload['provider_reference'],
                'amount' => $this->decimal($payload['amount']),
                'currency' => $payload['currency'] ?: 'IDR',
                'paid_at' => $this->parseDate($payload['paid_at']),
                'expired_at' => $this->parseDate($payload['expired_at']),
                'migration_status' => $match['conflict'] ? 'conflict' : ($match['user'] ? 'resolved' : 'unresolved_user'),
                'source_note' => 'EPIC HUB 1.0',
                'raw_payload' => [
                    'source' => 'legacy_mysql',
                    'raw_row' => (array) $row,
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
     * @param  array<string, mixed>  $payload
     * @return array{epic_id: ?string, email: ?string}
     */
    protected function normalizeLegacyV1User(array $payload): array
    {
        $normalized = $this->normalizeLegacyUser->execute([
            'epic_id' => $payload['epic_id'],
            'email' => $payload['email'],
        ]);

        return [
            'epic_id' => $normalized['epic_id'],
            'email' => $normalized['email'],
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
            'legacy_payment_id' => $this->nullableString(data_get($row, (string) ($columns['legacy_payment_id'] ?? 'id'))),
            'legacy_payment_number' => $this->nullableString(data_get($row, (string) ($columns['legacy_payment_number'] ?? 'payment_number'))),
            'legacy_order_id' => $this->nullableString(data_get($row, (string) ($columns['legacy_order_id'] ?? 'order_id'))),
            'legacy_user_id' => $this->nullableString(data_get($row, (string) ($columns['legacy_user_id'] ?? 'user_id'))),
            'epic_id' => $this->nullableString(data_get($row, (string) ($columns['epic_id'] ?? 'epic_id'))),
            'email' => $this->nullableString(data_get($row, (string) ($columns['email'] ?? 'email'))),
            'status' => $this->nullableString(data_get($row, (string) ($columns['status'] ?? 'status'))),
            'payment_method' => $this->nullableString(data_get($row, (string) ($columns['payment_method'] ?? 'payment_method'))),
            'provider' => $this->nullableString(data_get($row, (string) ($columns['provider'] ?? 'provider'))),
            'provider_reference' => $this->nullableString(data_get($row, (string) ($columns['provider_reference'] ?? 'provider_reference'))),
            'amount' => $this->nullableString(data_get($row, (string) ($columns['amount'] ?? 'amount'))),
            'currency' => $this->nullableString(data_get($row, (string) ($columns['currency'] ?? 'currency'))),
            'paid_at' => $this->nullableString(data_get($row, (string) ($columns['paid_at'] ?? 'paid_at'))),
            'expired_at' => $this->nullableString(data_get($row, (string) ($columns['expired_at'] ?? 'expired_at'))),
        ];
    }

    /**
     * @param  array<string, ?string>  $payload
     */
    protected function buildImportKey(array $payload): string
    {
        return hash('sha256', implode('|', [
            'payment',
            $payload['legacy_payment_id'] ?? '',
            $payload['legacy_payment_number'] ?? '',
            $payload['legacy_order_id'] ?? '',
            $payload['amount'] ?? '',
        ]));
    }

    protected function normalizeStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'paid', 'success', 'completed', 'settled' => 'paid',
            'pending', 'waiting', 'unpaid' => 'pending',
            'cancelled', 'canceled', 'expired' => 'cancelled',
            'failed', 'rejected' => 'failed',
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
