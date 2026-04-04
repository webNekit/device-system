<?php

namespace App\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Model;

class StorageLocation extends Model
{
    protected $fillable = ['warehouse_id', 'rack', 'shelf', 'bin', 'label'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getFullAddressAttribute()
    {
        return $this->label ?? "{$this->rack}-{$this->shelf}-{$this->bin}";
    }
}
