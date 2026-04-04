<?php

namespace App\Domain\Ticketing\Models;

use App\Domain\Branch\Models\User;
use Illuminate\Database\Eloquent\Model;

class TicketComment extends Model
{
    protected $fillable = ['ticket_id', 'user_id', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
