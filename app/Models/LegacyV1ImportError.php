<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'batch_id',
    'legacy_v1_user_id',
    'legacy_v1_product_access_id',
    'scope',
    'severity',
    'code',
    'message',
    'context',
    'resolved_at',
    'resolved_by',
])]
class LegacyV1ImportError extends Model
{
    /**
     * @return BelongsTo<LegacyV1ImportBatch, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(LegacyV1ImportBatch::class, 'batch_id');
    }

    /**
     * @return BelongsTo<LegacyV1User, $this>
     */
    public function legacyUser(): BelongsTo
    {
        return $this->belongsTo(LegacyV1User::class, 'legacy_v1_user_id');
    }

    /**
     * @return BelongsTo<LegacyV1ProductAccess, $this>
     */
    public function legacyProductAccess(): BelongsTo
    {
        return $this->belongsTo(LegacyV1ProductAccess::class, 'legacy_v1_product_access_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
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
