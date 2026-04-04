<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceModel extends Model
{
    protected $fillable = ['brand_id', 'name'];

    public function brand()
    {
        return $this->belongsTo(DeviceBrand::class, 'brand_id');
    }
}
