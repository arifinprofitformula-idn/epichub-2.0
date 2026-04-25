<?php

namespace App\Models;

use App\Enums\PayoutStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'epi_channel_id',
    'payout_number',
    'total_amount',
    'status',
    'notes',
    'paid_by',
    'paid_at',
    'metadata',
])]
class CommissionPayout extends Model
{
    use SoftDeletes;

    public function epiChannel(): BelongsTo
    {
        return $this->belongsTo(EpiChannel::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'commission_payout_id');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function scopePaid(Builder $query): void
    {
        $query->where('status', PayoutStatus::Paid);
    }

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'status' => PayoutStatus::class,
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}

