<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'batch_id',
    'import_key',
    'legacy_payment_id',
    'legacy_payment_number',
    'legacy_order_id',
    'legacy_v1_order_id',
    'legacy_user_id',
    'legacy_user_epic_id',
    'legacy_user_email',
    'user_id',
    'legacy_status',
    'normalized_status',
    'payment_method',
    'provider',
    'provider_reference',
    'amount',
    'currency',
    'paid_at',
    'expired_at',
    'migration_status',
    'source_note',
    'raw_payload',
])]
class LegacyV1Payment extends Model
{
    /**
     * @return BelongsTo<LegacyV1ImportBatch, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(LegacyV1ImportBatch::class, 'batch_id');
    }

    /**
     * @return BelongsTo<LegacyV1Order, $this>
     */
    public function legacyOrder(): BelongsTo
    {
        return $this->belongsTo(LegacyV1Order::class, 'legacy_v1_order_id');
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
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }
}
