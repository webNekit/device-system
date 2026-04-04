<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecklistTemplate extends Model
{
    protected $fillable = [
        'name',
        'type',
        'stage_id',
        'device_type_id',
        'is_active',
        'sort_order',
        'description',
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
        return $this->hasMany(ChecklistTemplateItem::class, 'template_id')->orderBy('sort_order');
    }
}
