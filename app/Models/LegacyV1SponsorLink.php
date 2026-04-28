<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'batch_id',
    'legacy_v1_user_id',
    'user_id',
    'sponsor_legacy_epic_id',
    'previous_referrer_epi_channel_id',
    'resolved_sponsor_user_id',
    'resolved_referrer_epi_channel_id',
    'resolution_status',
    'forced',
    'resolution_reason',
    'applied_at',
    'metadata',
])]
class LegacyV1SponsorLink extends Model
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

    /**
     * @return BelongsTo<User, $this>
     */
    public function resolvedSponsorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_sponsor_user_id');
    }

    /**
     * @return BelongsTo<EpiChannel, $this>
     */
    public function previousReferrerEpiChannel(): BelongsTo
    {
        return $this->belongsTo(EpiChannel::class, 'previous_referrer_epi_channel_id');
    }

    /**
     * @return BelongsTo<EpiChannel, $this>
     */
    public function resolvedReferrerEpiChannel(): BelongsTo
    {
        return $this->belongsTo(EpiChannel::class, 'resolved_referrer_epi_channel_id');
    }

    protected function casts(): array
    {
        return [
            'forced' => 'boolean',
            'applied_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
