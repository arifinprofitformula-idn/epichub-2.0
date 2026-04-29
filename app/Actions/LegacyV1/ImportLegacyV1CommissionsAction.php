<?php

namespace App\Actions\LegacyV1;

use App\Enums\LegacyV1CommissionMigrationStatus;
use App\Enums\LegacyV1CommissionStatus;
use App\Models\LegacyV1Commission;
use App\Models\LegacyV1CommissionImportBatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class ImportLegacyV1CommissionsAction
{
    public function __construct(
        protected ParseLegacyV1CsvAction $parseCsv,
        protected NormalizeLegacyV1CommissionAction $normalizeCommission,
        protected ResolveLegacyV1CommissionUserAction $resolveCommissionUser,
        protected ResolveLegacyV1CommissionProductAction $resolveCommissionProduct,
        protected RecordLegacyV1CommissionImportErrorAction $recordError,
        protected GenerateLegacyV1CommissionReportAction $generateReport,
    ) {}

    public function execute(string $absolutePath, ?User $actor = null): LegacyV1CommissionImportBatch
    {
        $parsed = $this->parseCsv->execute($absolutePath, $this->aliases(), [
            'legacy_commission_id',
            'user_epic_id',
            'user_name',
            'user_email',
            'user_whatsapp',
            'sponsor_epic_id',
            'downline_epic_id',
            'downline_name',
            'product_code',
            'product_name',
            'commission_type',
            'commission_level',
            'commission_amount',
            'commission_status',
            'earned_at',
            'approved_at',
            'paid_at',
            'period_month',
            'period_year',
            'legacy_order_id',
            'note',
        ]);

        return $this->importRows(
            rows: $parsed['rows'],
            actor: $actor,
            name: 'Legacy V1 Commissions - '.$parsed['file_name'],
            fileName: $parsed['file_name'],
            filePath: $absolutePath,
            fileHash: $parsed['file_hash'],
            fileSize: $parsed['file_size'],
        );
    }

    public function executeFromDatabase(?User $actor = null): LegacyV1CommissionImportBatch
    {
        $connectionName = (string) config('legacy_v1.connection', 'legacy_mysql');
        $source = (array) config('legacy_v1.sources.commissions');
        $table = (string) data_get($source, 'table');
        $columns = (array) data_get($source, 'columns', []);
        $rows = DB::connection($connectionName)->table($table)->get();

        $normalizedRows = collect($rows)
            ->values()
            ->map(function (object $row, int $index) use ($columns): array {
                $payload = (array) $row;

                return [
                    'line' => $index + 1,
                    'legacy_commission_id' => data_get($payload, (string) ($columns['legacy_commission_id'] ?? 'id')),
                    'user_epic_id' => data_get($payload, (string) ($columns['user_epic_id'] ?? 'user_epic_id')),
                    'user_name' => data_get($payload, (string) ($columns['user_name'] ?? 'user_name')),
                    'user_email' => data_get($payload, (string) ($columns['user_email'] ?? 'user_email')),
                    'user_whatsapp' => data_get($payload, (string) ($columns['user_whatsapp'] ?? 'user_whatsapp')),
                    'sponsor_epic_id' => data_get($payload, (string) ($columns['sponsor_epic_id'] ?? 'sponsor_epic_id')),
                    'downline_epic_id' => data_get($payload, (string) ($columns['downline_epic_id'] ?? 'downline_epic_id')),
                    'downline_name' => data_get($payload, (string) ($columns['downline_name'] ?? 'downline_name')),
                    'product_code' => data_get($payload, (string) ($columns['product_code'] ?? 'product_code')),
                    'product_name' => data_get($payload, (string) ($columns['product_name'] ?? 'product_name')),
                    'commission_type' => data_get($payload, (string) ($columns['commission_type'] ?? 'commission_type')),
                    'commission_level' => data_get($payload, (string) ($columns['commission_level'] ?? 'commission_level')),
                    'commission_amount' => data_get($payload, (string) ($columns['commission_amount'] ?? 'commission_amount')),
                    'commission_status' => data_get($payload, (string) ($columns['commission_status'] ?? 'commission_status')),
                    'earned_at' => data_get($payload, (string) ($columns['earned_at'] ?? 'earned_at')),
                    'approved_at' => data_get($payload, (string) ($columns['approved_at'] ?? 'approved_at')),
                    'paid_at' => data_get($payload, (string) ($columns['paid_at'] ?? 'paid_at')),
                    'period_month' => data_get($payload, (string) ($columns['period_month'] ?? 'period_month')),
                    'period_year' => data_get($payload, (string) ($columns['period_year'] ?? 'period_year')),
                    'legacy_order_id' => data_get($payload, (string) ($columns['legacy_order_id'] ?? 'legacy_order_id')),
                    'note' => data_get($payload, (string) ($columns['note'] ?? 'note')),
                ];
            })
            ->all();

        return $this->importRows(
            rows: $normalizedRows,
            actor: $actor,
            name: 'Legacy V1 Commissions from Database',
            fileName: $table,
            filePath: $connectionName.'::'.$table,
            fileHash: hash('sha256', json_encode($normalizedRows, JSON_THROW_ON_ERROR)),
            fileSize: count($normalizedRows),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected function importRows(
        array $rows,
        ?User $actor,
        string $name,
        string $fileName,
        string $filePath,
        string $fileHash,
        int $fileSize,
    ): LegacyV1CommissionImportBatch {
        $existingBatch = LegacyV1CommissionImportBatch::query()
            ->where('file_hash', $fileHash)
            ->where('file_size', $fileSize)
            ->whereIn('status', ['processing', 'completed', 'completed_with_issues'])
            ->latest('id')
            ->first();

        if ($existingBatch) {
            return $existingBatch->summary !== null
                ? $existingBatch->fresh()
                : tap($existingBatch, fn (LegacyV1CommissionImportBatch $batch) => $this->generateReport->execute($batch, persist: true))->fresh();
        }

        $batch = LegacyV1CommissionImportBatch::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => $name,
            'status' => 'processing',
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_hash' => $fileHash,
            'file_size' => $fileSize,
            'imported_by' => $actor?->id,
            'started_at' => now(),
        ]);

        foreach ($rows as $row) {
            DB::transaction(function () use ($batch, $row): void {
                $normalized = $this->normalizeCommission->execute($row);

                $commission = LegacyV1Commission::query()->firstOrNew([
                    'import_key' => $normalized['import_key'],
                ]);

                $isNew = ! $commission->exists;

                $commission->fill([
                    'import_batch_id' => $commission->exists ? $commission->import_batch_id : $batch->id,
                    'row_number' => (int) $row['line'],
                    'legacy_commission_id' => $normalized['legacy_commission_id'],
                    'legacy_user_epic_id' => $normalized['legacy_user_epic_id'],
                    'legacy_user_name' => $normalized['legacy_user_name'],
                    'legacy_user_email' => $normalized['legacy_user_email'],
                    'legacy_user_whatsapp' => $normalized['legacy_user_whatsapp'],
                    'legacy_sponsor_epic_id' => $normalized['legacy_sponsor_epic_id'],
                    'legacy_downline_epic_id' => $normalized['legacy_downline_epic_id'],
                    'legacy_downline_name' => $normalized['legacy_downline_name'],
                    'legacy_order_id' => $normalized['legacy_order_id'],
                    'legacy_product_code' => $normalized['legacy_product_code'],
                    'legacy_product_name' => $normalized['legacy_product_name'],
                    'commission_type' => $normalized['commission_type'],
                    'commission_level' => $normalized['commission_level'],
                    'commission_amount' => $normalized['commission_amount'],
                    'commission_status' => $normalized['commission_status'],
                    'earned_at' => $normalized['earned_at'],
                    'approved_at' => $normalized['approved_at'],
                    'paid_at' => $normalized['paid_at'],
                    'legacy_period_month' => $normalized['legacy_period_month'],
                    'legacy_period_year' => $normalized['legacy_period_year'],
                    'is_payable' => false,
                    'source_note' => $normalized['source_note'] ?: 'EPIC HUB 1.0',
                    'raw_payload' => [
                        'source' => 'EPIC HUB 1.0',
                        'raw_status' => $normalized['raw_status'],
                        'raw_row' => $row,
                    ],
                    'migration_status' => $normalized['migration_status'],
                ]);
                $commission->save();

                $this->resolveCommission($batch, $commission, $isNew);
            });
        }

        $summary = $this->generateReport->execute($batch, persist: true);

        $batch->forceFill([
            'status' => (($summary['error_count'] ?? 0) > 0 || ($summary['conflict_count'] ?? 0) > 0) ? 'completed_with_issues' : 'completed',
            'completed_at' => now(),
        ])->save();

        return $batch->fresh();
    }

    protected function resolveCommission(LegacyV1CommissionImportBatch $batch, LegacyV1Commission $commission, bool $isNew): void
    {
        if ($commission->commission_amount < 0) {
            $commission->forceFill([
                'migration_status' => LegacyV1CommissionMigrationStatus::Error,
            ])->save();

            $this->recordError->execute(
                batch: $batch,
                scope: 'commission',
                code: 'invalid_amount',
                message: 'Commission amount tidak valid.',
                legacyCommission: $commission,
            );

            return;
        }

        $resolvedUser = $this->resolveCommissionUser->execute($commission);

        if ($resolvedUser['conflict'] !== null) {
            $commission->forceFill([
                'migration_status' => LegacyV1CommissionMigrationStatus::UnresolvedUser,
                'user_id' => null,
                'epi_channel_id' => null,
            ])->save();

            $this->recordError->execute(
                batch: $batch,
                scope: 'commission_user',
                code: 'identity_conflict',
                message: $resolvedUser['conflict'],
                legacyCommission: $commission,
                severity: 'conflict',
            );

            return;
        }

        $product = $this->resolveCommissionProduct->execute($commission);

        $migrationStatus = $resolvedUser['migration_status'];

        if ($product === null && ($commission->legacy_product_code !== null || $commission->legacy_product_name !== null)) {
            $migrationStatus = $migrationStatus === LegacyV1CommissionMigrationStatus::UnresolvedUser
                ? LegacyV1CommissionMigrationStatus::UnresolvedUser
                : LegacyV1CommissionMigrationStatus::UnresolvedProduct;
        }

        $commission->forceFill([
            'user_id' => $resolvedUser['user']?->id,
            'epi_channel_id' => $resolvedUser['user']?->epiChannel?->id,
            'product_id' => $product?->id,
            'migration_status' => $commission->commission_status === LegacyV1CommissionStatus::Unknown
                ? LegacyV1CommissionMigrationStatus::UnknownStatus
                : $migrationStatus,
        ])->save();

        if ($resolvedUser['user'] === null) {
            $this->recordError->execute(
                batch: $batch,
                scope: 'commission_user',
                code: 'unresolved_user',
                message: 'Komisi legacy belum bisa dihubungkan ke user EPIC HUB 2.0.',
                legacyCommission: $commission,
                severity: 'warning',
                context: [
                    'epic_id' => $commission->legacy_user_epic_id,
                    'email' => $commission->legacy_user_email,
                    'whatsapp' => $commission->legacy_user_whatsapp,
                ],
            );
        }

        if ($product === null && ($commission->legacy_product_code !== null || $commission->legacy_product_name !== null)) {
            $this->recordError->execute(
                batch: $batch,
                scope: 'commission_product',
                code: 'unresolved_product',
                message: 'Produk legacy belum bisa dihubungkan ke produk EPIC HUB 2.0.',
                legacyCommission: $commission,
                severity: 'warning',
                context: [
                    'product_code' => $commission->legacy_product_code,
                    'product_name' => $commission->legacy_product_name,
                ],
            );
        }

        if ($commission->commission_status === LegacyV1CommissionStatus::Unknown) {
            $this->recordError->execute(
                batch: $batch,
                scope: 'commission_status',
                code: 'unknown_status',
                message: 'Status komisi legacy tidak dikenali dan disimpan sebagai unknown.',
                legacyCommission: $commission,
                severity: 'warning',
                context: [
                    'raw_status' => data_get($commission->raw_payload, 'raw_status'),
                ],
            );
        }

        if (! $isNew) {
            $existingSourceNote = trim((string) ($commission->source_note ?? ''));

            $commission->forceFill([
                'source_note' => $existingSourceNote !== '' ? $existingSourceNote : 'EPIC HUB 1.0',
            ])->save();
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function aliases(): array
    {
        return [
            'legacy_commission_id' => ['legacy_commission_id', 'commission_id', 'id_komisi_lama'],
            'user_epic_id' => ['user_epic_id', 'epic_id', 'id_epic', 'kode_epic'],
            'user_name' => ['user_name', 'name', 'nama'],
            'user_email' => ['user_email', 'email'],
            'user_whatsapp' => ['user_whatsapp', 'whatsapp', 'phone', 'nomor_hp'],
            'sponsor_epic_id' => ['sponsor_epic_id', 'id_epic_sponsor'],
            'downline_epic_id' => ['downline_epic_id', 'id_epic_downline'],
            'downline_name' => ['downline_name', 'nama_downline'],
            'product_code' => ['product_code', 'legacy_product_code', 'kode_produk'],
            'product_name' => ['product_name', 'legacy_product_name', 'nama_produk'],
            'commission_type' => ['commission_type', 'tipe_komisi'],
            'commission_level' => ['commission_level', 'level_komisi'],
            'commission_amount' => ['commission_amount', 'amount', 'nominal_komisi'],
            'commission_status' => ['commission_status', 'status_komisi'],
            'earned_at' => ['earned_at', 'tanggal_komisi', 'created_at'],
            'approved_at' => ['approved_at'],
            'paid_at' => ['paid_at'],
            'period_month' => ['period_month', 'bulan', 'month'],
            'period_year' => ['period_year', 'tahun', 'year'],
            'legacy_order_id' => ['legacy_order_id', 'order_id', 'id_order_lama'],
            'note' => ['note', 'notes', 'catatan'],
        ];
    }
}
