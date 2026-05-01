<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailketingSubscriberLog extends Model
{
    protected $fillable = [
        'list_id',
        'list_name',
        'user_id',
        'email',
        'status',
        'response',
        'error_message',
        'event_type',
        'metadata',
    ];

    protected $casts = [
        'response' => 'array',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
