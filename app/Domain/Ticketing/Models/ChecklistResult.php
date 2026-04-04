<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistResult extends Model
{
    protected $fillable = ['ticket_id', 'checklist_id', 'user_id', 'answers_json'];

    protected function casts(): array
    {
        return [
            'answers_json' => 'array',
        ];
    }
}
