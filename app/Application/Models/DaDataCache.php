<?php

namespace App\Application\Models;

use Illuminate\Database\Eloquent\Model;

class DaDataCache extends Model
{
    protected $table = 'dadata_cache';

    protected $fillable = [
        'query_hash',
        'response_json',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
