<?php

namespace App\Domain\Ticketing\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MagicLink extends Model
{
    use HasUlids;

    protected $table = 'magic_links';

    protected $fillable = [
        'ticket_id',
        'token',
        'expires_at',
        'last_visited_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'last_visited_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
