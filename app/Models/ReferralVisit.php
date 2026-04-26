<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'epi_channel_id',
    'product_id',
    'referral_code',
    'landing_url',
    'source_url',
    'visitor_id',
    'session_id',
    'ip_address',
    'user_agent',
    'clicked_at',
    'metadata',
])]
class ReferralVisit extends Model
{
    public function epiChannel(): BelongsTo
    {
        return $this->belongsTo(EpiChannel::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function deviceLabel(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $userAgent = Str::lower((string) $this->user_agent);

                if ($userAgent === '') {
                    return '-';
                }

                return Str::contains($userAgent, [
                    'mobile',
                    'android',
                    'iphone',
                    'ipad',
                    'ipod',
                    'windows phone',
                    'blackberry',
                    'opera mini',
                ]) ? 'Mobile' : 'Desktop';
            },
        );
    }

    protected function domicileLabel(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $metadata = is_array($this->metadata) ? $this->metadata : [];

                $city = data_get($metadata, 'city')
                    ?? data_get($metadata, 'domicile.city')
                    ?? data_get($metadata, 'geo.city');
                $region = data_get($metadata, 'region')
                    ?? data_get($metadata, 'domicile.region')
                    ?? data_get($metadata, 'geo.region');
                $country = data_get($metadata, 'country')
                    ?? data_get($metadata, 'domicile.country')
                    ?? data_get($metadata, 'geo.country');

                $parts = array_values(array_filter([
                    is_string($city) ? trim($city) : null,
                    is_string($region) ? trim($region) : null,
                    is_string($country) ? trim($country) : null,
                ]));

                if ($parts === []) {
                    return 'Tidak tersedia';
                }

                return implode(', ', $parts);
            },
        );
    }

    protected function casts(): array
    {
        return [
            'clicked_at' => 'datetime',
            'metadata' => 'array',
        ];
    }
}

