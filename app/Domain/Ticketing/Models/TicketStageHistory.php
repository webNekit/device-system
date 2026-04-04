<?php

namespace App\Domain\Ticketing\Models;

use App\Domain\Branch\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketStageHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'stage_id',
        'user_id',
        'entered_at',
        'exited_at',
        'duration_minutes',
    ];

    protected $casts = [
        'entered_at' => 'datetime',
        'exited_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
