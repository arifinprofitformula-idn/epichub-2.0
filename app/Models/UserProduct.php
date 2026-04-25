<?php

namespace App\Models;

use App\Enums\UserProductStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'product_id',
    'order_id',
    'order_item_id',
    'source_product_id',
    'access_type',
    'status',
    'starts_at',
    'expires_at',
    'granted_by',
    'granted_at',
    'revoked_by',
    'revoked_at',
    'revoke_reason',
    'metadata',
])]
class UserProduct extends Model
{
    use SoftDeletes;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<OrderItem, $this>
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function sourceProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'source_product_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /**
     * @return HasMany<AccessLog, $this>
     */
    public function accessLogs(): HasMany
    {
        return $this->hasMany(AccessLog::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query
            ->where('status', UserProductStatus::Active)
            ->where(function (Builder $q): void {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function scopeRevoked(Builder $query): void
    {
        $query->where('status', UserProductStatus::Revoked);
    }

    public function scopeExpired(Builder $query): void
    {
        $query->where(function (Builder $q): void {
            $q
                ->where('status', UserProductStatus::Expired)
                ->orWhere(function (Builder $q): void {
                    $q->whereNotNull('expires_at')->where('expires_at', '<=', now());
                });
        });
    }

    public function isActive(): bool
    {
        if ($this->status !== UserProductStatus::Active) {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        if ($this->status === UserProductStatus::Expired) {
            return true;
        }

        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isRevoked(): bool
    {
        return $this->status === UserProductStatus::Revoked;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => UserProductStatus::class,
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'granted_at' => 'datetime',
            'revoked_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}

