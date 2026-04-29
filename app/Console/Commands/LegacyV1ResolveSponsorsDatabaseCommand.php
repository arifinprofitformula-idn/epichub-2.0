<?php

namespace App\Console\Commands;

use App\Actions\LegacyV1\ResolveLegacyV1SponsorsAction;
use Throwable;

class LegacyV1ResolveSponsorsDatabaseCommand extends LegacyV1Command
{
    protected $signature = 'legacy:v1:resolve-sponsors {--batch=} {--force} {--dry-run}';

    protected $description = 'Resolve sponsor links for a staged legacy user batch.';

    public function handle(ResolveLegacyV1SponsorsAction $resolve): int
    {
        try {
            $batch = $this->resolveOptionalBatch($this->option('batch')) ?? $this->latestBatch('users_db');

            if (! $batch) {
                throw new \RuntimeException('Batch users legacy belum ditemukan.');
            }

            $summary = $resolve->execute(
                batch: $batch,
                force: (bool) $this->option('force'),
                dryRun: (bool) $this->option('dry-run'),
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Resolve sponsor batch #%d selesai diproses.', $batch->id));
        $this->renderSummary($summary);

        return self::SUCCESS;
    }
}
