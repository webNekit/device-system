<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceSpec extends Model
{
    protected $fillable = ['model_name', 'brand', 'specs_json'];

    protected $casts = [
        'specs_json' => 'array',
    ];
}
