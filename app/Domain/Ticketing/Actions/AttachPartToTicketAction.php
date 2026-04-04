<?php

namespace App\Domain\Ticketing\Actions;

use App\Domain\Inventory\Models\InventoryItem;
use App\Domain\Ticketing\Models\Ticket;
use App\Domain\Ticketing\Models\TicketInventory;
use Exception;
use Illuminate\Support\Facades\DB;

class AttachPartToTicketAction
{
    public function execute(Ticket $ticket, $inventoryItemId, float $sellingPrice, int $warrantyDays): void
    {
        DB::transaction(function () use ($ticket, $inventoryItemId, $sellingPrice, $warrantyDays) {

            // 1. Ищем запчасть и строго проверяем, что она свободна
            $item = InventoryItem::where('id', $inventoryItemId)
                ->where('status', 'available')
                ->first();

            if (! $item) {
                throw new Exception('Эта запчасть уже использована или не найдена на складе.');
            }

            // 2. Привязываем к заявке
            TicketInventory::create([
                'ticket_id' => $ticket->id,
                'inventory_item_id' => $item->id,
                'selling_price' => $sellingPrice,
                'warranty_days' => $warrantyDays,
            ]);

            // 3. Меняем статус запчасти на складе (резервируем под этот ремонт)
            $item->update(['status' => 'reserved']);

            // 4. Пересчитываем общую стоимость: запчасти + работа
            // Берем стоимость ВСЕХ привязанных запчастей (включая только что добавленную)
            $partsTotal = $ticket->usedParts()->sum('selling_price');
            // Прибавляем стоимость работы мастера (которая лежит в колонке labor_cost)
            $newEstimatedCost = $partsTotal + ($ticket->labor_cost ?? 0);

            // Обновляем заявку новой правильной суммой
            $ticket->update(['estimated_cost' => $newEstimatedCost]);
        });
    }
}
