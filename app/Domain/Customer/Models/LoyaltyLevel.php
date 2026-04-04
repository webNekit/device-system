<?php

namespace App\Domain\Customer\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyLevel extends Model
{
    protected $fillable = [
        'name',
        'discount_percent',
        'min_spend',
    ];
}
