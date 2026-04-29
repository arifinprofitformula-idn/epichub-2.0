<?php

namespace App\Actions\LegacyV1;

use App\Models\LegacyV1ImportBatch;

class RollbackLegacyV1BatchAction
{
    public function __construct(
        protected RollbackLegacyImportBatchAction $rollback,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(LegacyV1ImportBatch $batch): array
    {
        return $this->rollback->execute($batch);
    }
}
