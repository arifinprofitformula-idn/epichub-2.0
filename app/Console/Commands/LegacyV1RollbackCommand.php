<?php

namespace App\Console\Commands;

use App\Actions\LegacyV1\RollbackLegacyImportBatchAction;
use Throwable;

class LegacyV1RollbackCommand extends LegacyV1Command
{
    protected $signature = 'legacy:v1-rollback {batch}';

    protected $description = 'Rollback legacy migration effects safely for one batch.';

    public function handle(RollbackLegacyImportBatchAction $rollback): int
    {
        try {
            $batch = $this->resolveBatch((string) $this->argument('batch'));
            $summary = $rollback->execute($batch);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Rollback batch #%d selesai.', $batch->id));
        $this->renderSummary($summary);

        return self::SUCCESS;
    }
}
