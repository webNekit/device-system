<?php

namespace App\Presentation\Livewire\Ticketing;

use App\Domain\Ticketing\Models\MagicLink;
use App\Domain\Ticketing\Models\PipelineStage;
use App\Domain\Ticketing\Models\Ticket;
use App\Domain\Ticketing\Models\TicketComment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Статус вашего ремонта')]
#[Layout('components.layouts.guest')]
class ClientPortal extends Component
{
    public Ticket $ticket;

    public MagicLink $magicLink;

    public bool $isApproved = false;

    public function mount(string $token)
    {
        $this->magicLink = MagicLink::where('token', $token)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->firstOrFail();

        $this->magicLink->update(['last_visited_at' => now()]);
        $this->ticket = $this->magicLink->ticket->load(['customer', 'currentStage', 'usedParts.inventoryItem.product']);
    }

    // Вспомогательный метод для смены стадии с записью истории (как настоящий микросервис)
    private function performStageChange($newStageId)
    {
        // 1. Закрываем текущую историю
        $currentHistory = $this->ticket->histories()->whereNull('exited_at')->latest()->first();
        if ($currentHistory) {
            $currentHistory->update([
                'exited_at' => now(),
                'duration_minutes' => now()->diffInMinutes($currentHistory->entered_at),
            ]);
        }

        // 2. Обновляем стадию в заявке
        $this->ticket->update(['current_stage_id' => $newStageId]);

        // 3. Открываем новую историю (user_id = null, т.к. это сделал клиент/система)
        $this->ticket->histories()->create([
            'stage_id' => $newStageId,
            'user_id' => null,
            'entered_at' => now(),
        ]);
    }

    public function approveRepair()
    {
        TicketComment::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => null,
            'content' => '✅ КЛИЕНТ СОГЛАСОВАЛ СУММУ РЕМОНТА ('.number_format($this->ticket->estimated_cost, 0, '.', ' ').' ₽)',
        ]);

        // Ищем следующую стадию (Ожидание запчастей / Ремонт)
        $nextStage = PipelineStage::where('pipeline_id', $this->ticket->pipeline_id)
            ->where('order_column', '>', $this->ticket->currentStage->order_column)
            ->orderBy('order_column', 'asc')
            ->first();

        if ($nextStage) {
            $this->performStageChange($nextStage->id);
        }

        $this->isApproved = true;
    }

    public function rejectRepair()
    {
        TicketComment::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => null,
            'content' => '❌ КЛИЕНТ ОТКАЗАЛСЯ ОТ РЕМОНТА. Заявка автоматически закрыта.',
        ]);

        // Ищем САМУЮ ПОСЛЕДНЮЮ стадию (Выдано / Закрыто)
        $closedStage = PipelineStage::where('pipeline_id', $this->ticket->pipeline_id)
            ->orderBy('order_column', 'desc')
            ->first();

        if ($closedStage) {
            $this->performStageChange($closedStage->id);
        }

        $this->isApproved = true;
    }

    public function render()
    {
        return view('livewire.ticketing.client-portal');
    }
}
