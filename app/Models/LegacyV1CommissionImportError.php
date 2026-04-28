<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'import_batch_id',
    'legacy_v1_commission_id',
    'scope',
    'severity',
    'code',
    'message',
    'context',
    'resolved_at',
    'resolved_by',
])]
class LegacyV1CommissionImportError extends Model
{
    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(LegacyV1CommissionImportBatch::class, 'import_batch_id');
    }

    public function legacyCommission(): BelongsTo
    {
        return $this->belongsTo(LegacyV1Commission::class, 'legacy_v1_commission_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'resolved_at' => 'datetime',
        ];
    }
}
