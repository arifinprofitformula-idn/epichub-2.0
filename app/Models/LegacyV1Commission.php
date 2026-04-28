<?php

namespace App\Models;

use App\Enums\LegacyV1CommissionMigrationStatus;
use App\Enums\LegacyV1CommissionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'import_batch_id',
    'import_key',
    'row_number',
    'legacy_commission_id',
    'legacy_user_epic_id',
    'legacy_user_name',
    'legacy_user_email',
    'legacy_user_whatsapp',
    'user_id',
    'epi_channel_id',
    'legacy_sponsor_epic_id',
    'legacy_downline_epic_id',
    'legacy_downline_name',
    'legacy_order_id',
    'legacy_product_code',
    'legacy_product_name',
    'product_id',
    'commission_type',
    'commission_level',
    'commission_amount',
    'commission_status',
    'earned_at',
    'approved_at',
    'paid_at',
    'legacy_period_month',
    'legacy_period_year',
    'is_payable',
    'payout_id',
    'source_note',
    'raw_payload',
    'migration_status',
])]
class LegacyV1Commission extends Model
{
    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(LegacyV1CommissionImportBatch::class, 'import_batch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function epiChannel(): BelongsTo
    {
        return $this->belongsTo(EpiChannel::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(CommissionPayout::class, 'payout_id');
    }

    public function errors(): HasMany
    {
        return $this->hasMany(LegacyV1CommissionImportError::class, 'legacy_v1_commission_id');
    }

    public function getSourceLabelAttribute(): string
    {
        return 'EPIC HUB 1.0';
    }

    protected function casts(): array
    {
        return [
            'commission_amount' => 'decimal:2',
            'commission_status' => LegacyV1CommissionStatus::class,
            'earned_at' => 'datetime',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
            'legacy_period_month' => 'integer',
            'legacy_period_year' => 'integer',
            'is_payable' => 'boolean',
            'raw_payload' => 'array',
            'migration_status' => LegacyV1CommissionMigrationStatus::class,
        ];
    }
}
