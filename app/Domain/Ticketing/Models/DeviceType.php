<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceType extends Model
{
    protected $fillable = ['name'];

    public function brands()
    {
        return $this->hasMany(DeviceBrand::class, 'type_id');
    }
}
