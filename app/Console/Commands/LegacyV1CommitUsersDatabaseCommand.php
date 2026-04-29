<?php

namespace App\Console\Commands;

use App\Actions\LegacyV1\CommitLegacyV1UsersAction;
use Throwable;

class LegacyV1CommitUsersDatabaseCommand extends LegacyV1Command
{
    protected $signature = 'legacy:v1:commit-users {--batch=} {--dry-run}';

    protected $description = 'Commit staged legacy users into users and epi_channels safely.';

    public function handle(CommitLegacyV1UsersAction $commit): int
    {
        try {
            $batch = $this->resolveOptionalBatch($this->option('batch')) ?? $this->latestBatch('users_db');

            if (! $batch) {
                throw new \RuntimeException('Batch users legacy belum ditemukan.');
            }

            $summary = $commit->execute($batch, (bool) $this->option('dry-run'));
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Commit users batch #%d selesai diproses.', $batch->id));
        $this->renderSummary($summary);

        return self::SUCCESS;
    }
}
