<?php

namespace App\Actions\LegacyV1;

use App\Models\LegacyV1ImportBatch;

class GenerateLegacyMigrationReportAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(LegacyV1ImportBatch $batch, bool $persist = false): array
    {
        $batch->loadMissing(['legacyUsers', 'productAccesses', 'sponsorLinks', 'importErrors']);

        $summary = [
            'batch_id' => $batch->id,
            'batch_uuid' => $batch->uuid,
            'source_type' => $batch->source_type,
            'status' => $batch->status,
            'total_users' => $batch->legacyUsers->count(),
            'users_imported' => $batch->legacyUsers->where('status', 'imported')->count(),
            'users_created' => $batch->legacyUsers->where('match_status', 'created')->count(),
            'user_conflicts' => $batch->legacyUsers->where('status', 'conflict')->count(),
            'total_accesses' => $batch->productAccesses->count(),
            'accesses_granted' => $batch->productAccesses->where('status', 'granted')->count(),
            'accesses_reactivated' => $batch->productAccesses->where('status', 'reactivated')->count(),
            'accesses_duplicate' => $batch->productAccesses->where('status', 'duplicate')->count(),
            'unmapped_product_count' => $batch->productAccesses->where('status', 'unmapped_product')->count(),
            'unresolved_user_count' => $batch->productAccesses->where('status', 'unresolved_user')->count(),
            'sponsor_resolved' => $batch->sponsorLinks->whereIn('resolution_status', ['resolved', 'forced'])->count(),
            'sponsor_fallback_house' => $batch->sponsorLinks->where('resolution_status', 'fallback_house')->count(),
            'sponsor_existing_locked' => $batch->sponsorLinks->where('resolution_status', 'existing_locked')->count(),
            'sponsor_unresolved' => $batch->sponsorLinks->where('resolution_status', 'unresolved')->count(),
            'conflict_count' => $batch->importErrors->where('severity', 'conflict')->count(),
            'warning_count' => $batch->importErrors->where('severity', 'warning')->count(),
            'error_count' => $batch->importErrors->where('severity', 'error')->count(),
            'generated_at' => now()->toIso8601String(),
        ];

        if ($persist) {
            $batch->forceFill(['summary' => $summary])->save();
        }

        return $summary;
    }
}
