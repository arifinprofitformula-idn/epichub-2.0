<?php

namespace App\Console\Commands;

use App\Actions\LegacyV1\GenerateLegacyMigrationReportAction;
use Throwable;

class LegacyV1VerifyCommand extends LegacyV1Command
{
    protected $signature = 'legacy:v1-verify {batch}';

    protected $description = 'Generate a verification report for a legacy migration batch.';

    public function handle(GenerateLegacyMigrationReportAction $report): int
    {
        try {
            $batch = $this->resolveBatch((string) $this->argument('batch'));
            $summary = $report->execute($batch, persist: true);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Verification report batch #%d.', $batch->id));
        $this->renderSummary($summary);

        $errors = $batch->importErrors()->latest('id')->limit(10)->get(['scope', 'severity', 'code', 'message']);

        if ($errors->isNotEmpty()) {
            $this->newLine();
            $this->table(
                ['Scope', 'Severity', 'Code', 'Message'],
                $errors->map(fn ($error) => [$error->scope, $error->severity, $error->code, $error->message])->all(),
            );
        }

        return self::SUCCESS;
    }
}
