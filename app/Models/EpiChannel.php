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
    'payout_bank_name',
    'payout_bank_account_number',
    'payout_bank_account_holder_name',
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

    public function referredUsers(): HasMany
    {
        return $this->hasMany(User::class, 'referrer_epi_channel_id');
    }

    public function attributedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'referrer_epi_channel_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function commissionPayouts(): HasMany
    {
        return $this->hasMany(CommissionPayout::class);
    }

    public function clientNotes(): HasMany
    {
        return $this->hasMany(AffiliateClientNote::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', EpiChannelStatus::Active);
    }

    public function isActive(): bool
    {
        return $this->status === EpiChannelStatus::Active;
    }

    public function isHouseChannel(): bool
    {
        return (bool) data_get($this->metadata, 'is_house_channel', false);
    }

    public function hasCompletePayoutBankInfo(): bool
    {
        return filled($this->payout_bank_name)
            && filled($this->payout_bank_account_number)
            && filled($this->payout_bank_account_holder_name);
    }

    public function maskedPayoutBankAccountNumber(): ?string
    {
        if (blank($this->payout_bank_account_number)) {
            return null;
        }

        $value = preg_replace('/\s+/', '', (string) $this->payout_bank_account_number) ?? '';

        if (strlen($value) <= 4) {
            return $value;
        }

        return str_repeat('*', max(strlen($value) - 4, 0)).substr($value, -4);
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

