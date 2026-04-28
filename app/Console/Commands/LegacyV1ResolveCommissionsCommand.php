<?php

namespace App\Console\Commands;

use App\Actions\LegacyV1\RecordLegacyV1CommissionImportErrorAction;
use App\Actions\LegacyV1\ResolveLegacyV1CommissionProductAction;
use App\Actions\LegacyV1\ResolveLegacyV1CommissionUserAction;
use App\Actions\LegacyV1\GenerateLegacyV1CommissionReportAction;
use App\Enums\LegacyV1CommissionMigrationStatus;
use App\Enums\LegacyV1CommissionStatus;
use Throwable;

class LegacyV1ResolveCommissionsCommand extends LegacyV1Command
{
    protected $signature = 'legacy:v1-resolve-commissions {batch}';

    protected $description = 'Retry resolving legacy commission users and products for a batch.';

    public function handle(
        ResolveLegacyV1CommissionUserAction $resolveUser,
        ResolveLegacyV1CommissionProductAction $resolveProduct,
        RecordLegacyV1CommissionImportErrorAction $recordError,
        GenerateLegacyV1CommissionReportAction $report,
    ): int {
        try {
            $commissionBatch = $this->resolveCommissionBatch((string) $this->argument('batch'));

            $rows = $commissionBatch->commissions()
                ->whereIn('migration_status', [
                    LegacyV1CommissionMigrationStatus::UnresolvedUser,
                    LegacyV1CommissionMigrationStatus::UnresolvedProduct,
                    LegacyV1CommissionMigrationStatus::UnknownStatus,
                    LegacyV1CommissionMigrationStatus::Pending,
                ])
                ->get();

            foreach ($rows as $commission) {
                $resolved = $resolveUser->execute($commission);
                $product = $resolveProduct->execute($commission);

                $status = $resolved['migration_status'];

                if ($commission->commission_status === LegacyV1CommissionStatus::Unknown) {
                    $status = LegacyV1CommissionMigrationStatus::UnknownStatus;
                } elseif ($status === LegacyV1CommissionMigrationStatus::Resolved && $product === null && ($commission->legacy_product_code || $commission->legacy_product_name)) {
                    $status = LegacyV1CommissionMigrationStatus::UnresolvedProduct;
                }

                $commission->forceFill([
                    'user_id' => $resolved['user']?->id,
                    'epi_channel_id' => $resolved['user']?->epiChannel?->id,
                    'product_id' => $product?->id,
                    'migration_status' => $status,
                ])->save();

                if ($resolved['user'] === null) {
                    $recordError->execute(
                        batch: $commissionBatch,
                        scope: 'commission_user',
                        code: 'unresolved_user',
                        message: 'Retry resolve user belum berhasil.',
                        legacyCommission: $commission,
                        severity: 'warning',
                    );
                }
            }
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Resolve legacy commissions batch #%d selesai.', $commissionBatch->id));
        $this->renderSummary($report->execute($commissionBatch, persist: true));

        return self::SUCCESS;
    }
}
