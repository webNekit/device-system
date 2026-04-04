<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Checklist extends Model
{
    protected $fillable = [
        'name',
        'type',
        'is_active',
        'stage_id',
        'device_type_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    public function deviceType(): BelongsTo
    {
        return $this->belongsTo(DeviceType::class, 'device_type_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ChecklistItem::class)->orderBy('sort_order');
    }

    public function results(): HasMany
    {
        return $this->hasMany(ChecklistResult::class, 'checklist_id');
    }
}
