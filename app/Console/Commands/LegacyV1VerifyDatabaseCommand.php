<?php

namespace App\Console\Commands;

use App\Actions\LegacyV1\GenerateLegacyV1MigrationReportAction;
use Throwable;

class LegacyV1VerifyDatabaseCommand extends LegacyV1Command
{
    protected $signature = 'legacy:v1:verify {--batch=}';

    protected $description = 'Generate verification summary for a legacy import batch.';

    public function handle(GenerateLegacyV1MigrationReportAction $report): int
    {
        try {
            $batch = $this->resolveOptionalBatch($this->option('batch')) ?? $this->latestBatch();

            if (! $batch) {
                throw new \RuntimeException('Batch legacy belum ditemukan.');
            }

            $summary = $report->execute($batch, persist: true);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Verification report batch #%d.', $batch->id));
        $this->renderSummary($summary);

        return self::SUCCESS;
    }
}
