<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'event_key',
        'event_label',
        'target_key',
        'target_label',
        'email_enabled',
        'whatsapp_enabled',
        'email_subject',
        'email_body',
        'whatsapp_body',
        'available_shortcodes',
        'default_email_subject',
        'default_email_body',
        'default_whatsapp_body',
        'metadata',
    ];

    protected $casts = [
        'email_enabled'       => 'boolean',
        'whatsapp_enabled'    => 'boolean',
        'available_shortcodes'=> 'array',
        'metadata'            => 'array',
    ];

    public static function forEvent(string $eventKey): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('event_key', $eventKey)->orderBy('target_key')->get();
    }

    public static function findTemplate(string $eventKey, string $targetKey): ?static
    {
        return static::where('event_key', $eventKey)
            ->where('target_key', $targetKey)
            ->first();
    }

    public function resetToDefault(): void
    {
        $this->update([
            'email_subject'   => $this->default_email_subject,
            'email_body'      => $this->default_email_body,
            'whatsapp_body'   => $this->default_whatsapp_body,
            'email_enabled'   => (bool) data_get($this->metadata, 'default_email_enabled', true),
            'whatsapp_enabled'=> (bool) data_get($this->metadata, 'default_whatsapp_enabled', true),
        ]);
    }
}
