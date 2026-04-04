<?php

namespace App\Domain\Finance\Models;

use App\Domain\Branch\Models\Branch;
use App\Domain\Branch\Models\User;
use App\Domain\Ticketing\Models\Ticket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'branch_id', 'user_id', 'ticket_id',
        'type', 'category', 'payment_method',
        'amount', 'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
