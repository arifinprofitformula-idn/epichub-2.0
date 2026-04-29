<?php

namespace App\Actions\LegacyV1;

use App\Models\LegacyV1ImportBatch;

class CommitLegacyV1UsersAction
{
    public function __construct(
        protected UpsertLegacyV1UserIntoApplicationAction $upsertUser,
        protected GenerateLegacyMigrationReportAction $generateReport,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(LegacyV1ImportBatch $batch, bool $dryRun = false): array
    {
        $rows = $batch->legacyUsers()->orderBy('id')->get();
        $summary = [
            'batch_id' => $batch->id,
            'total_rows' => $rows->count(),
            'processed_rows' => 0,
            'dry_run' => $dryRun,
        ];

        foreach ($rows as $legacyUser) {
            $summary['processed_rows']++;

            if ($dryRun) {
                continue;
            }

            $this->upsertUser->execute($batch, $legacyUser);
        }

        if (! $dryRun) {
            $summary = array_merge($summary, $this->generateReport->execute($batch->fresh(), persist: true));
        }

        return $summary;
    }
}
