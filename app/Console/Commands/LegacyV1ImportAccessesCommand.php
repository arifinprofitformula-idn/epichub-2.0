<?php

namespace App\Console\Commands;

use App\Actions\LegacyV1\GenerateLegacyMigrationReportAction;
use App\Actions\LegacyV1\ImportLegacyV1ProductAccessAction;
use Throwable;

class LegacyV1ImportAccessesCommand extends LegacyV1Command
{
    protected $signature = 'legacy:v1-import-accesses {file} {--actor-email=}';

    protected $description = 'Import legacy EPIC HUB 1.0 product accesses into staging safely.';

    public function handle(ImportLegacyV1ProductAccessAction $importAccesses, GenerateLegacyMigrationReportAction $report): int
    {
        try {
            $batch = $importAccesses->execute(
                absolutePath: $this->argument('file'),
                actor: $this->resolveActor($this->option('actor-email')),
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Batch akses dibuat: #%d (%s)', $batch->id, $batch->uuid));
        $this->renderSummary($report->execute($batch));

        return self::SUCCESS;
    }
}
