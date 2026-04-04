<?php

namespace App\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasUlids;

    protected $fillable = [
        'product_id',
        'storage_location_id',
        'serial_number',
        'purchase_price',
        'status',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(StorageLocation::class, 'storage_location_id');
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}
