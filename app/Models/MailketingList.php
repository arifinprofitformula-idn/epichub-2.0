<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailketingList extends Model
{
    protected $fillable = [
        'list_id',
        'list_name',
        'raw_payload',
        'synced_at',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'synced_at'   => 'datetime',
    ];
}
