<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'epi_channel_id',
    'product_id',
    'referral_code',
    'landing_url',
    'source_url',
    'visitor_id',
    'session_id',
    'ip_address',
    'user_agent',
    'clicked_at',
    'metadata',
])]
class ReferralVisit extends Model
{
    public function epiChannel(): BelongsTo
    {
        return $this->belongsTo(EpiChannel::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function casts(): array
    {
        return [
            'clicked_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}

