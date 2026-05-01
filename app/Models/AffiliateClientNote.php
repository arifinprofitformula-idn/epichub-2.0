<?php

namespace App\Models;

use App\Enums\AffiliateClientFollowUpStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'epi_channel_id',
    'client_user_id',
    'note',
    'follow_up_status',
    'next_follow_up_at',
    'created_by',
])]
class AffiliateClientNote extends Model
{
    public function epiChannel(): BelongsTo
    {
        return $this->belongsTo(EpiChannel::class);
    }

    public function clientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function casts(): array
    {
        return [
            'follow_up_status' => AffiliateClientFollowUpStatus::class,
            'next_follow_up_at' => 'datetime',
        ];
    }
}
