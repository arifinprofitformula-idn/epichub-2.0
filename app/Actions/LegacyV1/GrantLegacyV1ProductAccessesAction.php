<?php

namespace App\Actions\LegacyV1;

use App\Models\LegacyV1ImportBatch;

class GrantLegacyV1ProductAccessesAction
{
    public function __construct(
        protected GrantLegacyUserProductAccessAction $grantAccess,
        protected GenerateLegacyMigrationReportAction $generateReport,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(LegacyV1ImportBatch $batch, bool $dryRun = false): array
    {
        $rows = $batch->productAccesses()
            ->whereNotIn('status', ['granted', 'reactivated', 'duplicate', 'rolled_back'])
            ->orderBy('id')
            ->get();

        $summary = [
            'batch_id' => $batch->id,
            'eligible_rows' => $rows->count(),
            'processed_rows' => 0,
            'dry_run' => $dryRun,
        ];

        foreach ($rows as $row) {
            $summary['processed_rows']++;

            if ($dryRun) {
                continue;
            }

            $this->grantAccess->execute($row);
        }

        if (! $dryRun) {
            $summary = array_merge($summary, $this->generateReport->execute($batch->fresh(), persist: true));
        }

        return $summary;
    }
}
