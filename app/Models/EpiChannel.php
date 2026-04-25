<?php

namespace App\Models;

use App\Enums\EpiChannelStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'epic_code',
    'store_name',
    'sponsor_epic_code',
    'sponsor_name',
    'status',
    'source',
    'activated_at',
    'suspended_at',
    'metadata',
])]
class EpiChannel extends Model
{
    use SoftDeletes;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referralVisits(): HasMany
    {
        return $this->hasMany(ReferralVisit::class);
    }

    public function referralOrders(): HasMany
    {
        return $this->hasMany(ReferralOrder::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function commissionPayouts(): HasMany
    {
        return $this->hasMany(CommissionPayout::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', EpiChannelStatus::Active);
    }

    public function isActive(): bool
    {
        return $this->status === EpiChannelStatus::Active;
    }

    protected function casts(): array
    {
        return [
            'status' => EpiChannelStatus::class,
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}

