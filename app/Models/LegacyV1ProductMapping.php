<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'legacy_product_key',
    'legacy_product_name',
    'product_id',
    'is_active',
    'mapped_by',
    'mapped_at',
    'notes',
    'metadata',
])]
class LegacyV1ProductMapping extends Model
{
    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function mappedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mapped_by');
    }

    /**
     * @return HasMany<LegacyV1ProductAccess, $this>
     */
    public function productAccesses(): HasMany
    {
        return $this->hasMany(LegacyV1ProductAccess::class, 'product_mapping_id');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'mapped_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
