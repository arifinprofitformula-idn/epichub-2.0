<?php

namespace App\Console\Commands;

use App\Actions\LegacyV1\GenerateLegacyMigrationReportAction;
use App\Actions\LegacyV1\GrantLegacyUserProductAccessAction;
use Throwable;

class LegacyV1GrantAccessesCommand extends LegacyV1Command
{
    protected $signature = 'legacy:v1-grant-accesses {batch}';

    protected $description = 'Grant mapped legacy product accesses into user_products and access_logs.';

    public function handle(GrantLegacyUserProductAccessAction $grantAccess, GenerateLegacyMigrationReportAction $report): int
    {
        try {
            $batch = $this->resolveBatch((string) $this->argument('batch'));

            if ($batch->source_type !== 'accesses') {
                throw new \RuntimeException('Batch ini bukan batch import accesses.');
            }

            $rows = $batch->productAccesses()
                ->whereNotIn('status', ['granted', 'reactivated', 'duplicate', 'rolled_back'])
                ->get();

            foreach ($rows as $row) {
                $grantAccess->execute($row);
            }
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Grant akses batch #%d selesai diproses.', $batch->id));
        $this->renderSummary($report->execute($batch, persist: true));

        return self::SUCCESS;
    }
}
