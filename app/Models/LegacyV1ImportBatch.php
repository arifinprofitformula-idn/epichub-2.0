<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'uuid',
    'name',
    'source_type',
    'status',
    'file_name',
    'file_path',
    'file_hash',
    'file_size',
    'imported_by',
    'started_at',
    'completed_at',
    'rolled_back_at',
    'summary',
    'metadata',
])]
class LegacyV1ImportBatch extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    /**
     * @return HasMany<LegacyV1User, $this>
     */
    public function legacyUsers(): HasMany
    {
        return $this->hasMany(LegacyV1User::class, 'batch_id');
    }

    /**
     * @return HasMany<LegacyV1ProductAccess, $this>
     */
    public function productAccesses(): HasMany
    {
        return $this->hasMany(LegacyV1ProductAccess::class, 'batch_id');
    }

    /**
     * @return HasMany<LegacyV1SponsorLink, $this>
     */
    public function sponsorLinks(): HasMany
    {
        return $this->hasMany(LegacyV1SponsorLink::class, 'batch_id');
    }

    /**
     * @return HasMany<LegacyV1UserMapping, $this>
     */
    public function userMappings(): HasMany
    {
        return $this->hasMany(LegacyV1UserMapping::class, 'batch_id');
    }

    /**
     * @return HasMany<LegacyV1Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(LegacyV1Order::class, 'batch_id');
    }

    /**
     * @return HasMany<LegacyV1Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(LegacyV1Payment::class, 'batch_id');
    }

    /**
     * @return HasMany<LegacyV1Payout, $this>
     */
    public function payouts(): HasMany
    {
        return $this->hasMany(LegacyV1Payout::class, 'batch_id');
    }

    /**
     * @return HasMany<LegacyV1ImportError, $this>
     */
    public function importErrors(): HasMany
    {
        return $this->hasMany(LegacyV1ImportError::class, 'batch_id');
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'rolled_back_at' => 'datetime',
            'summary' => 'array',
            'metadata' => 'array',
        ];
    }
}
