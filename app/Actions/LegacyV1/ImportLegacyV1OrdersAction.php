<?php

namespace App\Actions\LegacyV1;

use App\Models\LegacyV1ImportBatch;
use App\Models\LegacyV1Order;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ImportLegacyV1OrdersAction
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
        $source = (array) config('legacy_v1.sources.orders');
        $table = (string) data_get($source, 'table');
        $columns = (array) data_get($source, 'columns', []);
        $rows = DB::connection($connectionName)->table($table)->get();

        $batch ??= $dryRun
            ? null
            : $this->createBatch->execute('orders_db', $actor, 'Legacy V1 Orders from Database', [
                'connection' => $connectionName,
                'table' => $table,
            ]);

        $summary = [
            'source' => 'orders',
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
                'epic_id' => $payload['epic_id'],
                'email' => $payload['customer_email'],
                'whatsapp' => $payload['customer_whatsapp'],
            ]);
            $match = $this->resolveUserMatch->execute(
                epicId: $normalizedUser['epic_id'],
                email: $normalizedUser['email'],
                whatsapp: $normalizedUser['whatsapp'],
            );

            if ($dryRun) {
                $summary['staged_rows']++;

                continue;
            }

            $record = LegacyV1Order::query()->firstOrNew([
                'import_key' => $this->buildImportKey($payload),
            ]);

            $exists = $record->exists;

            $record->fill([
                'batch_id' => $record->exists ? $record->batch_id : $batch->id,
                'legacy_order_id' => $payload['legacy_order_id'],
                'legacy_order_number' => $payload['legacy_order_number'],
                'legacy_user_id' => $payload['legacy_user_id'],
                'legacy_user_epic_id' => $normalizedUser['epic_id'],
                'legacy_customer_name' => $payload['customer_name'],
                'legacy_customer_email' => $normalizedUser['email'],
                'legacy_customer_whatsapp' => $normalizedUser['whatsapp'],
                'user_id' => $match['user']?->id,
                'legacy_status' => $payload['status'],
                'normalized_status' => $this->normalizeStatus($payload['status']),
                'currency' => $payload['currency'] ?: 'IDR',
                'subtotal_amount' => $this->decimal($payload['subtotal_amount']),
                'discount_amount' => $this->decimal($payload['discount_amount']),
                'total_amount' => $this->decimal($payload['total_amount']),
                'ordered_at' => $this->parseDate($payload['ordered_at']),
                'paid_at' => $this->parseDate($payload['paid_at']),
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
     * @param  array<string, mixed>  $row
     * @param  array<string, mixed>  $columns
     * @return array<string, ?string>
     */
    protected function extractRow(array $row, array $columns): array
    {
        return [
            'legacy_order_id' => $this->nullableString(data_get($row, (string) ($columns['legacy_order_id'] ?? 'id'))),
            'legacy_order_number' => $this->nullableString(data_get($row, (string) ($columns['legacy_order_number'] ?? 'order_number'))),
            'legacy_user_id' => $this->nullableString(data_get($row, (string) ($columns['legacy_user_id'] ?? 'user_id'))),
            'epic_id' => $this->nullableString(data_get($row, (string) ($columns['epic_id'] ?? 'epic_id'))),
            'customer_name' => $this->nullableString(data_get($row, (string) ($columns['customer_name'] ?? 'customer_name'))),
            'customer_email' => $this->nullableString(data_get($row, (string) ($columns['customer_email'] ?? 'customer_email'))),
            'customer_whatsapp' => $this->nullableString(data_get($row, (string) ($columns['customer_whatsapp'] ?? 'customer_phone'))),
            'status' => $this->nullableString(data_get($row, (string) ($columns['status'] ?? 'status'))),
            'currency' => $this->nullableString(data_get($row, (string) ($columns['currency'] ?? 'currency'))),
            'subtotal_amount' => $this->nullableString(data_get($row, (string) ($columns['subtotal_amount'] ?? 'subtotal_amount'))),
            'discount_amount' => $this->nullableString(data_get($row, (string) ($columns['discount_amount'] ?? 'discount_amount'))),
            'total_amount' => $this->nullableString(data_get($row, (string) ($columns['total_amount'] ?? 'total_amount'))),
            'ordered_at' => $this->nullableString(data_get($row, (string) ($columns['ordered_at'] ?? 'created_at'))),
            'paid_at' => $this->nullableString(data_get($row, (string) ($columns['paid_at'] ?? 'paid_at'))),
        ];
    }

    /**
     * @param  array<string, ?string>  $payload
     */
    protected function buildImportKey(array $payload): string
    {
        return hash('sha256', implode('|', [
            'order',
            $payload['legacy_order_id'] ?? '',
            $payload['legacy_order_number'] ?? '',
            $payload['legacy_user_id'] ?? '',
            $payload['total_amount'] ?? '',
        ]));
    }

    protected function normalizeStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'paid', 'success', 'completed', 'settled' => 'paid',
            'pending', 'waiting', 'unpaid' => 'pending',
            'cancelled', 'canceled' => 'cancelled',
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
