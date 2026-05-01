<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AppSetting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    public static function get(string $key, mixed $default = null, string $group = 'general'): mixed
    {
        $record = static::where('group', $group)->where('key', $key)->first();

        if (! $record) {
            return $default;
        }

        $value = $record->value;

        if ($record->is_encrypted && filled($value)) {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Throwable) {
                return $default;
            }
        }

        return match ($record->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float'   => (float) $value,
            'json'    => json_decode($value, true),
            default   => $value,
        };
    }

    public static function set(string $key, mixed $value, string $group = 'general', bool $encrypted = false, ?string $type = null): void
    {
        $raw = is_array($value) ? json_encode($value) : (string) ($value ?? '');
        $resolvedType = $type ?? match (true) {
            is_bool($value)  => 'boolean',
            is_int($value)   => 'integer',
            is_float($value) => 'float',
            is_array($value) => 'json',
            default          => 'string',
        };

        $stored = ($encrypted && filled($raw)) ? Crypt::encryptString($raw) : $raw;

        static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $stored, 'type' => $resolvedType, 'is_encrypted' => $encrypted],
        );
    }
}
