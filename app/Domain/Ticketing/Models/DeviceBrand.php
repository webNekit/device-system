<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceBrand extends Model
{
    protected $fillable = ['type_id', 'name'];

    public function type()
    {
        return $this->belongsTo(DeviceType::class, 'type_id');
    }

    public function models()
    {
        return $this->hasMany(DeviceModel::class, 'brand_id');
    }
}
