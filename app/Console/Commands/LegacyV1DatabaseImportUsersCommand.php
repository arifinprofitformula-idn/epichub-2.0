<?php

namespace App\Console\Commands;

use App\Actions\LegacyV1\ImportLegacyV1UsersToStagingAction;
use Throwable;

class LegacyV1DatabaseImportUsersCommand extends LegacyV1Command
{
    protected $signature = 'legacy:v1:import-users {--batch=} {--dry-run} {--actor-email=}';

    protected $description = 'Import legacy users from configured legacy database into staging.';

    public function handle(ImportLegacyV1UsersToStagingAction $import): int
    {
        try {
            $result = $import->execute(
                batch: $this->resolveOptionalBatch($this->option('batch')),
                actor: $this->resolveActor($this->option('actor-email')),
                dryRun: (bool) $this->option('dry-run'),
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($result['batch']) {
            $this->info(sprintf('Batch users staging: #%d (%s)', $result['batch']->id, $result['batch']->uuid));
        }

        $this->renderSummary($result['summary']);

        return self::SUCCESS;
    }
}
