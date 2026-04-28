<?php

namespace App\Console\Commands;

use App\Actions\LegacyV1\GenerateLegacyV1CommissionReportAction;
use App\Actions\LegacyV1\ImportLegacyV1CommissionsAction;
use Throwable;

class LegacyV1ImportCommissionsCommand extends LegacyV1Command
{
    protected $signature = 'legacy:v1-import-commissions {file} {--actor-email=}';

    protected $description = 'Import legacy EPIC HUB 1.0 commission ledger into dedicated legacy tables.';

    public function handle(ImportLegacyV1CommissionsAction $importAction, GenerateLegacyV1CommissionReportAction $report): int
    {
        try {
            $batch = $importAction->execute(
                absolutePath: $this->argument('file'),
                actor: $this->resolveActor($this->option('actor-email')),
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Batch legacy commission dibuat: #%d (%s)', $batch->id, $batch->uuid));
        $this->renderSummary($report->execute($batch));

        return self::SUCCESS;
    }
}
