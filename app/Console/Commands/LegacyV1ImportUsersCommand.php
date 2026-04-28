<?php

namespace App\Console\Commands;

use App\Actions\LegacyV1\GenerateLegacyMigrationReportAction;
use App\Actions\LegacyV1\ImportLegacyV1UsersAction;
use Throwable;

class LegacyV1ImportUsersCommand extends LegacyV1Command
{
    protected $signature = 'legacy:v1-import-users {file} {--actor-email=}';

    protected $description = 'Import legacy EPIC HUB 1.0 users into staging and users table safely.';

    public function handle(ImportLegacyV1UsersAction $importUsers, GenerateLegacyMigrationReportAction $report): int
    {
        try {
            $batch = $importUsers->execute(
                absolutePath: $this->argument('file'),
                actor: $this->resolveActor($this->option('actor-email')),
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Batch users dibuat: #%d (%s)', $batch->id, $batch->uuid));
        $this->renderSummary($report->execute($batch));

        return self::SUCCESS;
    }
}
