<?php

namespace App\Presentation\Livewire\Ticketing;

use App\Domain\Branch\Models\Branch;
use App\Domain\Customer\Models\Customer;
use App\Domain\Ticketing\Models\Pipeline;
use App\Domain\Ticketing\Models\PipelineStage;
use App\Domain\Ticketing\Models\Ticket;
use App\Domain\Ticketing\Models\TicketStageHistory;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Service Desk: Канбан')]
class TicketKanban extends Component
{
    public ?int $selectedPipelineId = null;

    // Свойства для создания тикета
    public bool $isCreating = false;

    public string $customer_id = '';

    public string $device_brand = '';

    public string $device_model = '';

    public string $defect_description = '';

    public string $priority = 'normal';

    public float $estimated_cost = 0;

    public string $successMessage = '';

    public function mount()
    {
        // Выбираем последний созданный пайплайн (чтобы видеть актуальные данные после сидинга)
        $this->selectedPipelineId = Pipeline::latest()->first()?->id;
    }

    #[Computed]
    public function stages()
    {
        return PipelineStage::where('pipeline_id', $this->selectedPipelineId)
            ->orderBy('order_column')
            ->get();
    }

    #[Computed]
    public function customers()
    {
        return Customer::latest()->get();
    }

    #[Computed]
    public function tickets()
    {
        if (! $this->selectedPipelineId) {
            return collect();
        }

        return Ticket::with(['customer', 'currentStage'])
            ->where('pipeline_id', $this->selectedPipelineId)
            ->get()
            ->map(function ($ticket) {
                // Расчет SLA
                $history = TicketStageHistory::where('ticket_id', $ticket->id)
                    ->where('stage_id', $ticket->current_stage_id)
                    ->whereNull('exited_at')
                    ->first();

                if ($history) {
                    $minutes = now()->diffInMinutes($history->entered_at);
                    $limit = $ticket->currentStage->sla_max_minutes;

                    $ticket->sla_spent = $minutes;
                    $ticket->is_sla_breached = $limit ? ($minutes > $limit) : false;
                } else {
                    $ticket->sla_spent = 0;
                    $ticket->is_sla_breached = false;
                }

                return $ticket;
            })
            ->groupBy('current_stage_id');
    }

    public function openCreateForm()
    {
        $this->isCreating = true;
        $this->successMessage = '';
    }

    public function createTicket()
    {
        $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'device_brand' => 'required|string|max:255',
            'device_model' => 'required|string|max:255',
            'defect_description' => 'required|string',
        ]);

        $firstStage = PipelineStage::where('pipeline_id', $this->selectedPipelineId)
            ->orderBy('order_column')
            ->first();

        $ticket = Ticket::create([
            'ulid' => (string) Str::ulid(),
            'branch_id' => Branch::first()->id, // Временно берем первый филиал
            'customer_id' => $this->customer_id,
            'pipeline_id' => $this->selectedPipelineId,
            'current_stage_id' => $firstStage->id,
            'device_type' => 'Смартфон', // По умолчанию
            'device_brand' => $this->device_brand,
            'device_model' => $this->device_model,
            'defect_description' => $this->defect_description,
            'priority' => $this->priority,
            'estimated_cost' => $this->estimated_cost,
        ]);

        // СРАЗУ создаем историю стадии для работы SLA
        TicketStageHistory::create([
            'ticket_id' => $ticket->id,
            'stage_id' => $firstStage->id,
            'entered_at' => now(),
        ]);

        $this->reset(['customer_id', 'device_brand', 'device_model', 'defect_description', 'priority', 'estimated_cost', 'isCreating']);
        $this->successMessage = 'Заявка успешно создана!';

        unset($this->tickets); // Очищаем кэш для обновления доски
    }

    public function moveToNextStage(string $ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $currentStage = $ticket->currentStage;

        $nextStage = PipelineStage::where('pipeline_id', $ticket->pipeline_id)
            ->where('order_column', '>', $currentStage->order_column)
            ->orderBy('order_column')
            ->first();

        if ($nextStage) {
            TicketStageHistory::where('ticket_id', $ticket->id)
                ->where('stage_id', $currentStage->id)
                ->whereNull('exited_at')
                ->update(['exited_at' => now()]);

            $ticket->update(['current_stage_id' => $nextStage->id]);

            TicketStageHistory::create([
                'ticket_id' => $ticket->id,
                'stage_id' => $nextStage->id,
                'entered_at' => now(),
            ]);

            unset($this->tickets);
        }
    }

    public function render()
    {
        return view('livewire.ticketing.ticket-kanban');
    }
}
