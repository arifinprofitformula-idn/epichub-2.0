<?php

namespace App\Console\Commands;

use App\Actions\LegacyV1\GrantLegacyV1ProductAccessesAction;
use Throwable;

class LegacyV1GrantAccessesDatabaseCommand extends LegacyV1Command
{
    protected $signature = 'legacy:v1:grant-accesses {--batch=} {--dry-run}';

    protected $description = 'Grant staged legacy product accesses into user_products and access_logs.';

    public function handle(GrantLegacyV1ProductAccessesAction $grant): int
    {
        try {
            $batch = $this->resolveOptionalBatch($this->option('batch')) ?? $this->latestBatch('accesses_db');

            if (! $batch) {
                throw new \RuntimeException('Batch product accesses legacy belum ditemukan.');
            }

            $summary = $grant->execute($batch, (bool) $this->option('dry-run'));
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Grant accesses batch #%d selesai diproses.', $batch->id));
        $this->renderSummary($summary);

        return self::SUCCESS;
    }
}
