<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'batch_id',
    'legacy_v1_user_id',
    'legacy_access_id',
    'source_type',
    'import_key',
    'row_number',
    'status',
    'raw_identifier_type',
    'raw_identifier_value',
    'raw_legacy_product_key',
    'raw_legacy_product_name',
    'raw_granted_at',
    'normalized_email',
    'normalized_epic_id',
    'normalized_whatsapp',
    'normalized_legacy_product_key',
    'matched_user_id',
    'matched_by',
    'product_mapping_id',
    'mapped_product_id',
    'granted_user_product_id',
    'granted_at',
    'metadata',
])]
class LegacyV1ProductAccess extends Model
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
    public function matchedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_user_id');
    }

    /**
     * @return BelongsTo<LegacyV1ProductMapping, $this>
     */
    public function productMapping(): BelongsTo
    {
        return $this->belongsTo(LegacyV1ProductMapping::class, 'product_mapping_id');
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function mappedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'mapped_product_id');
    }

    /**
     * @return BelongsTo<UserProduct, $this>
     */
    public function grantedUserProduct(): BelongsTo
    {
        return $this->belongsTo(UserProduct::class, 'granted_user_product_id');
    }

    /**
     * @return HasMany<LegacyV1ImportError, $this>
     */
    public function importErrors(): HasMany
    {
        return $this->hasMany(LegacyV1ImportError::class, 'legacy_v1_product_access_id');
    }

    protected function casts(): array
    {
        return [
            'granted_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
