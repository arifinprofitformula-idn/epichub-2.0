<?php

namespace App\Models;

use App\Enums\ContributorCommissionBase;
use App\Enums\ContributorCommissionStatus;
use App\Enums\ContributorCommissionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContributorCommission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'contributor_user_id',
        'product_id',
        'order_id',
        'order_item_id',
        'buyer_user_id',
        'commission_type',
        'commission_value',
        'base_amount',
        'commission_amount',
        'commission_base',
        'status',
        'approved_by',
        'approved_at',
        'paid_by',
        'paid_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'contributor_payout_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'commission_type' => ContributorCommissionType::class,
            'commission_value' => 'decimal:2',
            'base_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'commission_base' => ContributorCommissionBase::class,
            'status' => ContributorCommissionStatus::class,
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
            'rejected_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function contributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contributor_user_id');
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<OrderItem, $this> */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /** @return BelongsTo<User, $this> */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return BelongsTo<User, $this> */
    public function paidByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', ContributorCommissionStatus::Approved);
    }

    public function scopePaid(Builder $query): void
    {
        $query->where('status', ContributorCommissionStatus::Paid);
    }

    public function scopeEligibleForReport(Builder $query): void
    {
        $query->whereIn('status', [
            ContributorCommissionStatus::Approved,
            ContributorCommissionStatus::Paid,
        ]);
    }
}
