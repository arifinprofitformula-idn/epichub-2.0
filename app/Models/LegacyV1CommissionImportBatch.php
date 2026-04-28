<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'uuid',
    'name',
    'status',
    'file_name',
    'file_path',
    'file_hash',
    'file_size',
    'imported_by',
    'started_at',
    'completed_at',
    'summary',
    'metadata',
])]
class LegacyV1CommissionImportBatch extends Model
{
    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(LegacyV1Commission::class, 'import_batch_id');
    }

    public function errors(): HasMany
    {
        return $this->hasMany(LegacyV1CommissionImportError::class, 'import_batch_id');
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'summary' => 'array',
            'metadata' => 'array',
        ];
    }
}
