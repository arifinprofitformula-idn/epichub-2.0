<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'batch_id',
    'legacy_v1_user_id',
    'legacy_user_id',
    'legacy_epic_id',
    'legacy_email',
    'legacy_whatsapp',
    'user_id',
    'match_method',
    'status',
    'notes',
    'metadata',
])]
class LegacyV1UserMapping extends Model
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }
}
