<?php

namespace App\Actions\LegacyV1;

use App\Models\LegacyV1ImportBatch;
use App\Models\LegacyV1ImportError;
use App\Models\LegacyV1ProductAccess;
use App\Models\LegacyV1User;

class RecordLegacyV1ImportErrorAction
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function execute(
        LegacyV1ImportBatch $batch,
        string $scope,
        string $code,
        string $message,
        ?LegacyV1User $legacyUser = null,
        ?LegacyV1ProductAccess $legacyProductAccess = null,
        string $severity = 'error',
        array $context = [],
    ): LegacyV1ImportError {
        return LegacyV1ImportError::query()->create([
            'batch_id' => $batch->id,
            'legacy_v1_user_id' => $legacyUser?->id,
            'legacy_v1_product_access_id' => $legacyProductAccess?->id,
            'scope' => $scope,
            'severity' => $severity,
            'code' => $code,
            'message' => $message,
            'context' => $context !== [] ? $context : null,
        ]);
    }
}
