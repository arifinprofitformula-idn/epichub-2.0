<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'batch_id',
    'row_number',
    'status',
    'match_status',
    'sponsor_status',
    'raw_name',
    'raw_epic_id',
    'raw_email',
    'raw_whatsapp',
    'raw_sponsor_epic_id',
    'raw_city',
    'normalized_name',
    'normalized_epic_id',
    'normalized_email',
    'normalized_whatsapp',
    'normalized_sponsor_epic_id',
    'normalized_city',
    'matched_user_id',
    'matched_by',
    'imported_user_id',
    'epi_channel_id',
    'imported_at',
    'metadata',
])]
class LegacyV1User extends Model
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
    public function matchedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function importedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_user_id');
    }

    /**
     * @return BelongsTo<EpiChannel, $this>
     */
    public function epiChannel(): BelongsTo
    {
        return $this->belongsTo(EpiChannel::class, 'epi_channel_id');
    }

    /**
     * @return HasMany<LegacyV1ProductAccess, $this>
     */
    public function productAccesses(): HasMany
    {
        return $this->hasMany(LegacyV1ProductAccess::class, 'legacy_v1_user_id');
    }

    /**
     * @return HasMany<LegacyV1SponsorLink, $this>
     */
    public function sponsorLinks(): HasMany
    {
        return $this->hasMany(LegacyV1SponsorLink::class, 'legacy_v1_user_id');
    }

    /**
     * @return HasMany<LegacyV1ImportError, $this>
     */
    public function importErrors(): HasMany
    {
        return $this->hasMany(LegacyV1ImportError::class, 'legacy_v1_user_id');
    }

    protected function casts(): array
    {
        return [
            'imported_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
