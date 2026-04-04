<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    protected $fillable = ['checklist_id', 'question', 'field_type', 'sort_order'];
}
