<?php

namespace App\Models;

use App\Enums\OmsIntegrationDirection;
use App\Enums\OmsIntegrationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'direction',
    'action',
    'request_id',
    'epic_code',
    'email',
    'status',
    'response_code',
    'http_status',
    'request_payload',
    'response_payload',
    'error_message',
    'ip_address',
    'user_agent',
    'processed_at',
])]
class OmsIntegrationLog extends Model
{
    protected function casts(): array
    {
        return [
            'direction' => OmsIntegrationDirection::class,
            'status' => OmsIntegrationStatus::class,
            'http_status' => 'integer',
            'request_payload' => 'array',
            'response_payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}

