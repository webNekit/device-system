<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    protected $fillable = [
        'pipeline_id',
        'name',
        'order_column',
        'sla_max_minutes',
    ];

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'current_stage_id');
    }
}
