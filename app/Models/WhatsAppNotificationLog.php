<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WhatsAppNotificationLog extends Model
{
    protected $table = 'whatsapp_notification_logs';

    protected $fillable = [
        'provider',
        'event_type',
        'notifiable_type',
        'notifiable_id',
        'recipient_phone',
        'recipient_name',
        'message',
        'media_url',
        'group_id',
        'status',
        'http_status',
        'provider_response',
        'error_message',
        'sent_at',
        'failed_at',
        'retry_count',
        'metadata',
    ];

    protected $casts = [
        'provider_response' => 'array',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'metadata' => 'array',
        'retry_count' => 'integer',
        'http_status' => 'integer',
    ];

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function record(array $data): static
    {
        return static::create(array_merge([
            'provider' => 'dripsender',
            'status' => 'pending',
            'retry_count' => 0,
        ], $data));
    }
}
