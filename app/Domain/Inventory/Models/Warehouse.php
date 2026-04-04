<?php

namespace App\Domain\Inventory\Models;

use App\Application\Scopes\BranchIsolationScope;
use App\Domain\Branch\Models\Branch;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasUlids;

    protected static function booted()
    {
        static::addGlobalScope(new BranchIsolationScope);
    }

    protected $fillable = ['branch_id', 'name', 'description'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function locations()
    {
        return $this->hasMany(StorageLocation::class);
    }
}
