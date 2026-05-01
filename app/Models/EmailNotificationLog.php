<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmailNotificationLog extends Model
{
    protected $fillable = [
        'channel',
        'provider',
        'event_type',
        'notifiable_type',
        'notifiable_id',
        'recipient_email',
        'recipient_name',
        'subject',
        'status',
        'provider_response',
        'error_message',
        'sent_at',
        'failed_at',
        'metadata',
    ];

    protected $casts = [
        'provider_response' => 'array',
        'metadata'          => 'array',
        'sent_at'           => 'datetime',
        'failed_at'         => 'datetime',
    ];

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function record(array $data): static
    {
        return static::create(array_merge([
            'channel'  => 'email',
            'provider' => 'mailketing',
            'status'   => 'pending',
        ], $data));
    }
}
