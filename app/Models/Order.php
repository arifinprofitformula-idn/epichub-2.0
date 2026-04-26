<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'referrer_epi_channel_id',
    'referral_source',
    'order_number',
    'status',
    'subtotal_amount',
    'discount_amount',
    'total_amount',
    'currency',
    'customer_name',
    'customer_email',
    'customer_phone',
    'notes',
    'paid_at',
    'cancelled_at',
    'metadata',
])]
class Order extends Model
{
    use SoftDeletes;

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referrerEpiChannel(): BelongsTo
    {
        return $this->belongsTo(EpiChannel::class, 'referrer_epi_channel_id');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function referralOrder(): HasOne
    {
        return $this->hasOne(ReferralOrder::class);
    }

    public function latestPayment(): ?Payment
    {
        return $this->payments()->latest()->first();
    }

    public function isPaid(): bool
    {
        return $this->status === OrderStatus::Paid;
    }

    public function canBePaid(): bool
    {
        return in_array($this->status, [OrderStatus::Pending, OrderStatus::Unpaid], true);
    }

    public function scopePaid(Builder $query): void
    {
        $query->where('status', OrderStatus::Paid);
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', OrderStatus::Pending);
    }

    public function scopeUnpaid(Builder $query): void
    {
        $query->where('status', OrderStatus::Unpaid);
    }

    protected function formattedTotal(): Attribute
    {
        return Attribute::make(
            get: fn (): string => 'Rp '.number_format((float) $this->total_amount, 0, ',', '.'),
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}
