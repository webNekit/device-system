<?php

namespace App\Domain\Branch\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, HasUlids, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'commission_percent',
        'branch_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
