<?php

namespace App\Models;

use App\Enums\EventRegistrationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'event_id',
    'user_id',
    'user_product_id',
    'order_id',
    'order_item_id',
    'status',
    'registered_at',
    'attended_at',
    'cancelled_at',
    'cancelled_by',
    'checked_in_by',
    'source_product_id',
    'notes',
    'metadata',
])]
class EventRegistration extends Model
{
    use SoftDeletes;

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userProduct(): BelongsTo
    {
        return $this->belongsTo(UserProduct::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function sourceProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'source_product_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereIn('status', [EventRegistrationStatus::Registered, EventRegistrationStatus::Attended]);
    }

    public function scopeAttended(Builder $query): void
    {
        $query->where('status', EventRegistrationStatus::Attended);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [EventRegistrationStatus::Registered, EventRegistrationStatus::Attended], true);
    }

    public function isAttended(): bool
    {
        return $this->status === EventRegistrationStatus::Attended;
    }

    public function isCancelled(): bool
    {
        return $this->status === EventRegistrationStatus::Cancelled;
    }

    protected function casts(): array
    {
        return [
            'status' => EventRegistrationStatus::class,
            'registered_at' => 'datetime',
            'attended_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}

