<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class SecurityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event',
        'user_type',
        'identifier',
        'ip_address',
        'user_agent',
        'details',
    ];

    protected $casts = [
        'details'    => 'array',
        'created_at' => 'datetime',
    ];
}
