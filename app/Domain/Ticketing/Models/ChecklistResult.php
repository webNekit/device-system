<?php

namespace App\Domain\Ticketing\Models;

use App\Domain\Branch\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistResult extends Model
{
    protected $fillable = ['ticket_id', 'checklist_id', 'user_id', 'answers_json'];

    protected function casts(): array
    {
        return [
            'answers_json' => 'array',
        ];
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(Checklist::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
