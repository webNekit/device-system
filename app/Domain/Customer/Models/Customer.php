<?php

namespace App\Domain\Customer\Models;

use App\Domain\Ticketing\Models\Ticket;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'phone',
        'email',
        'inn',
        'kpp',
        'legal_address',
        'loyalty_level_id',
        'bonus_balance',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function loyaltyLevel()
    {
        return $this->belongsTo(LoyaltyLevel::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'customer_id');
    }
}
