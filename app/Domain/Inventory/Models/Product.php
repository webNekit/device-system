<?php

namespace App\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = ['sku', 'name', 'category_id', 'min_stock', 'description'];

    public function items()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function getStockCountAttribute()
    {
        return $this->items()->where('status', 'available')->count();
    }
}
