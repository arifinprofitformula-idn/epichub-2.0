<?php

namespace App\Console\Commands;

use App\Actions\LegacyV1\GenerateLegacyMigrationReportAction;
use App\Actions\LegacyV1\ResolveLegacyV1SponsorAction;
use Throwable;

class LegacyV1ResolveSponsorsCommand extends LegacyV1Command
{
    protected $signature = 'legacy:v1-resolve-sponsors {batch} {--force}';

    protected $description = 'Resolve legacy sponsor relationships after all users are imported.';

    public function handle(ResolveLegacyV1SponsorAction $resolveSponsor, GenerateLegacyMigrationReportAction $report): int
    {
        try {
            $batch = $this->resolveBatch((string) $this->argument('batch'));

            if ($batch->source_type !== 'users') {
                throw new \RuntimeException('Batch ini bukan batch import users.');
            }

            $rows = $batch->legacyUsers()->where('status', 'imported')->get();

            foreach ($rows as $row) {
                $resolveSponsor->execute($row, (bool) $this->option('force'));
            }
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Sponsor batch #%d selesai diproses.', $batch->id));
        $this->renderSummary($report->execute($batch, persist: true));

        return self::SUCCESS;
    }
}
