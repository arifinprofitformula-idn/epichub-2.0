<?php

namespace App\Models;

use App\Enums\AffiliateCommissionType;
use App\Enums\CommissionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'epi_channel_id',
    'referral_order_id',
    'order_id',
    'order_item_id',
    'product_id',
    'buyer_user_id',
    'commission_type',
    'commission_value',
    'base_amount',
    'commission_amount',
    'status',
    'approved_by',
    'approved_at',
    'rejected_by',
    'rejected_at',
    'rejection_reason',
    'paid_at',
    'commission_payout_id',
    'metadata',
])]
class Commission extends Model
{
    use SoftDeletes;

    public function epiChannel(): BelongsTo
    {
        return $this->belongsTo(EpiChannel::class);
    }

    public function referralOrder(): BelongsTo
    {
        return $this->belongsTo(ReferralOrder::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(CommissionPayout::class, 'commission_payout_id');
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', CommissionStatus::Approved);
    }

    protected function casts(): array
    {
        return [
            'commission_type' => AffiliateCommissionType::class,
            'commission_value' => 'decimal:2',
            'base_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'status' => CommissionStatus::class,
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}

