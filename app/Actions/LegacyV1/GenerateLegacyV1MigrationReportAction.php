<?php

namespace App\Actions\LegacyV1;

use App\Models\LegacyV1ImportBatch;

class GenerateLegacyV1MigrationReportAction
{
    public function __construct(
        protected GenerateLegacyMigrationReportAction $report,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(LegacyV1ImportBatch $batch, bool $persist = false): array
    {
        return $this->report->execute($batch, $persist);
    }
}
