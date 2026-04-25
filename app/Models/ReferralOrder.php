<?php

namespace App\Models;

use App\Enums\ReferralOrderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'epi_channel_id',
    'referral_visit_id',
    'buyer_user_id',
    'status',
    'attributed_at',
    'metadata',
])]
class ReferralOrder extends Model
{
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function epiChannel(): BelongsTo
    {
        return $this->belongsTo(EpiChannel::class);
    }

    public function referralVisit(): BelongsTo
    {
        return $this->belongsTo(ReferralVisit::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    public function scopeConverted(Builder $query): void
    {
        $query->where('status', ReferralOrderStatus::Converted);
    }

    protected function casts(): array
    {
        return [
            'status' => ReferralOrderStatus::class,
            'attributed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}

