<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DripsenderList extends Model
{
    protected $table = 'dripsender_lists';

    protected $fillable = [
        'list_id',
        'list_name',
        'contact_count',
        'raw_payload',
        'synced_at',
    ];

    protected $casts = [
        'contact_count' => 'integer',
        'raw_payload' => 'array',
        'synced_at' => 'datetime',
    ];
}
