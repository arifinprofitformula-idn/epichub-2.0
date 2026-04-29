<?php

namespace App\Actions\LegacyV1;

use App\Models\LegacyV1ImportBatch;
use App\Models\User;
use Illuminate\Support\Str;

class CreateLegacyImportBatchAction
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function execute(
        string $sourceType,
        ?User $actor = null,
        ?string $name = null,
        array $metadata = [],
    ): LegacyV1ImportBatch {
        return LegacyV1ImportBatch::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => $name ?: 'Legacy V1 '.str($sourceType)->replace('_', ' ')->title(),
            'source_type' => $sourceType,
            'status' => 'processing',
            'imported_by' => $actor?->id,
            'started_at' => now(),
            'metadata' => $metadata !== [] ? $metadata : null,
        ]);
    }
}
