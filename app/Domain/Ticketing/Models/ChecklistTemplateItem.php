<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistTemplateItem extends Model
{
    protected $fillable = [
        'template_id',
        'question',
        'field_type',
        'options_json',
        'sort_order',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'options_json' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class, 'template_id');
    }
}
