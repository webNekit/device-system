<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pipeline extends Model
{
    protected $fillable = ['name'];

    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class)->orderBy('order_column');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
