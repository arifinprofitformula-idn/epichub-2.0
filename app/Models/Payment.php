<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'order_id',
    'payment_number',
    'payment_method',
    'status',
    'amount',
    'currency',
    'provider',
    'provider_reference',
    'proof_of_payment',
    'paid_at',
    'expired_at',
    'verified_by',
    'verified_at',
    'failure_reason',
    'metadata',
])]
class Payment extends Model
{
    use SoftDeletes;

    public function getRouteKeyName(): string
    {
        return 'payment_number';
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * @return HasMany<PaymentCallback, $this>
     */
    public function callbacks(): HasMany
    {
        return $this->hasMany(PaymentCallback::class);
    }

    public function isSuccess(): bool
    {
        return $this->status === PaymentStatus::Success;
    }

    public function scopeSuccess(Builder $query): void
    {
        $query->where($query->getModel()->qualifyColumn('status'), PaymentStatus::Success);
    }

    public function scopePending(Builder $query): void
    {
        $query->where($query->getModel()->qualifyColumn('status'), PaymentStatus::Pending);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
            'verified_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
