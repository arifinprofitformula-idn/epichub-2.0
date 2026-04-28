<?php

namespace App\Console\Commands;

use App\Actions\LegacyV1\GenerateLegacyV1CommissionReportAction;
use App\Models\LegacyV1CommissionImportBatch;
use Throwable;

class LegacyV1CommissionReportCommand extends LegacyV1Command
{
    protected $signature = 'legacy:v1-commission-report {batch}';

    protected $description = 'Generate a report for one legacy commission import batch.';

    public function handle(GenerateLegacyV1CommissionReportAction $report): int
    {
        try {
            $batch = $this->resolveCommissionBatch((string) $this->argument('batch'));
            $summary = $report->execute($batch, persist: true);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->renderSummary($summary);

        return self::SUCCESS;
    }
}
