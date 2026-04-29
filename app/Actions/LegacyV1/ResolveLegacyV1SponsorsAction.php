<?php

namespace App\Actions\LegacyV1;

use App\Models\LegacyV1ImportBatch;

class ResolveLegacyV1SponsorsAction
{
    public function __construct(
        protected ResolveLegacyV1SponsorAction $resolveSponsor,
        protected GenerateLegacyMigrationReportAction $generateReport,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(LegacyV1ImportBatch $batch, bool $force = false, bool $dryRun = false): array
    {
        $rows = $batch->legacyUsers()->whereNotNull('imported_user_id')->orderBy('id')->get();
        $summary = [
            'batch_id' => $batch->id,
            'eligible_rows' => $rows->count(),
            'processed_rows' => 0,
            'force' => $force,
            'dry_run' => $dryRun,
        ];

        foreach ($rows as $legacyUser) {
            $summary['processed_rows']++;

            if ($dryRun) {
                continue;
            }

            $this->resolveSponsor->execute($legacyUser, $force);
        }

        if (! $dryRun) {
            $summary = array_merge($summary, $this->generateReport->execute($batch->fresh(), persist: true));
        }

        return $summary;
    }
}
