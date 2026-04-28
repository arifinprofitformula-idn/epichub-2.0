<?php

namespace App\Actions\LegacyV1;

use App\Models\LegacyV1Commission;
use App\Models\LegacyV1CommissionImportBatch;
use App\Models\LegacyV1CommissionImportError;

class RecordLegacyV1CommissionImportErrorAction
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function execute(
        LegacyV1CommissionImportBatch $batch,
        string $scope,
        string $code,
        string $message,
        ?LegacyV1Commission $legacyCommission = null,
        string $severity = 'error',
        array $context = [],
    ): LegacyV1CommissionImportError {
        return LegacyV1CommissionImportError::query()->updateOrCreate(
            [
                'import_batch_id' => $batch->id,
                'legacy_v1_commission_id' => $legacyCommission?->id,
                'scope' => $scope,
                'severity' => $severity,
                'code' => $code,
            ],
            [
                'message' => $message,
                'context' => $context !== [] ? $context : null,
                'resolved_at' => null,
                'resolved_by' => null,
            ],
        );
    }
}
