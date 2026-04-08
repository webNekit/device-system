<?php

namespace App\Domain\Ticketing\Models;

use App\Application\Scopes\BranchIsolationScope;
use App\Domain\Branch\Models\Branch;
use App\Domain\Branch\Models\User;
use App\Domain\Customer\Models\Customer;
use App\Domain\Finance\Models\FinancialTransaction;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasUlids, SoftDeletes;

    protected static function booted()
    {
        static::addGlobalScope(new BranchIsolationScope);
    }

    protected $fillable = [
        'branch_id',
        'customer_id',
        'pipeline_id',
        'current_stage_id',
        'assigned_technician_id',
        'device_type',
        'device_brand',
        'device_model',
        'serial_number',
        'defect_description',
        'priority',
        'estimated_cost',
        'sla_deadline_at',
        'labor_cost',
    ];

    protected $casts = [
        'sla_deadline_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'current_stage_id');
    }

    public function assignedTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class)->latest();
    }

    public function usedParts()
    {
        return $this->hasMany(TicketInventory::class, 'ticket_id');
    }

    public function transactions()
    {
        return $this->hasMany(FinancialTransaction::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TicketStageHistory::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }
}
