<?php

namespace App\Domain\Ticketing\Models;

use App\Domain\Inventory\Models\InventoryItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketInventory extends Model
{
    protected $table = 'ticket_inventory';

    protected $fillable = [
        'ticket_id',
        'inventory_item_id',
        'selling_price',
        'warranty_days',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'decimal:2',
            'warranty_days' => 'integer',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
