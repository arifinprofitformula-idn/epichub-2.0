<?php

namespace App\Console\Commands;

use App\Actions\LegacyV1\GenerateLegacyV1CommissionReportAction;
use App\Actions\LegacyV1\ImportLegacyV1CommissionsAction;
use Illuminate\Support\Facades\DB;
use Throwable;

class LegacyV1ImportCommissionsDatabaseCommand extends LegacyV1Command
{
    protected $signature = 'legacy:v1:import-commissions {--batch=} {--dry-run} {--actor-email=}';

    protected $description = 'Import legacy commission ledger from configured legacy database.';

    public function handle(ImportLegacyV1CommissionsAction $import, GenerateLegacyV1CommissionReportAction $report): int
    {
        try {
            if ((bool) $this->option('dry-run')) {
                $connectionName = (string) config('legacy_v1.connection', 'legacy_mysql');
                $table = (string) config('legacy_v1.sources.commissions.table');
                $count = DB::connection($connectionName)->table($table)->count();

                $this->renderSummary([
                    'source' => 'commissions',
                    'connection' => $connectionName,
                    'table' => $table,
                    'total_rows' => $count,
                    'dry_run' => true,
                ]);

                return self::SUCCESS;
            }

            $batch = $import->executeFromDatabase(
                actor: $this->resolveActor($this->option('actor-email')),
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Batch legacy commissions: #%d (%s)', $batch->id, $batch->uuid));
        $this->renderSummary($report->execute($batch));

        return self::SUCCESS;
    }
}
