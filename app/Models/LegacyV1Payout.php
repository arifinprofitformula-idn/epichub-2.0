<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'batch_id',
    'import_key',
    'legacy_payout_id',
    'legacy_user_id',
    'legacy_user_epic_id',
    'legacy_user_email',
    'user_id',
    'epi_channel_id',
    'legacy_status',
    'normalized_status',
    'amount',
    'requested_at',
    'approved_at',
    'paid_at',
    'migration_status',
    'source_note',
    'raw_payload',
])]
class LegacyV1Payout extends Model
{
    /**
     * @return BelongsTo<LegacyV1ImportBatch, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(LegacyV1ImportBatch::class, 'batch_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<EpiChannel, $this>
     */
    public function epiChannel(): BelongsTo
    {
        return $this->belongsTo(EpiChannel::class);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }
}
