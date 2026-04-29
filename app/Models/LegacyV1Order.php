<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'batch_id',
    'import_key',
    'legacy_order_id',
    'legacy_order_number',
    'legacy_user_id',
    'legacy_user_epic_id',
    'legacy_customer_name',
    'legacy_customer_email',
    'legacy_customer_whatsapp',
    'user_id',
    'legacy_status',
    'normalized_status',
    'currency',
    'subtotal_amount',
    'discount_amount',
    'total_amount',
    'ordered_at',
    'paid_at',
    'migration_status',
    'source_note',
    'raw_payload',
])]
class LegacyV1Order extends Model
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
     * @return HasMany<LegacyV1Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(LegacyV1Payment::class, 'legacy_v1_order_id');
    }

    protected function casts(): array
    {
        return [
            'subtotal_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'ordered_at' => 'datetime',
            'paid_at' => 'datetime',
            'raw_payload' => 'array',
        ];
    }
}
