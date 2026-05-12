<?php

namespace App\Presentation\Livewire\Ticketing;

use App\Domain\Branch\Models\Branch;
use App\Domain\Branch\Models\User;
use App\Domain\Customer\Actions\CreateCustomerAction;
use App\Domain\Customer\DTOs\CustomerData;
use App\Domain\Customer\Models\Customer;
use App\Domain\Ticketing\Actions\CreateTicketAction;
use App\Domain\Ticketing\DTOs\CreateTicketData;
use App\Domain\Ticketing\Models\DeviceType;
use App\Domain\Ticketing\Models\Pipeline;
use App\Domain\Ticketing\Models\PipelineStage;
use App\Domain\Ticketing\Models\Ticket;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Канбан-доска заявок')]
class TicketManager extends Component
{
    public bool $showCreateForm = false;

    public string|int|null $branch_id = null;

    public string|int|null $customer_id = null;

    public string|int|null $pipeline_id = null;

    public string|int|null $filterTechnicianId = null;

    public string|int|null $filterCustomerId = null;

    public string|int|null $filterBranchId = null;

    public ?string $serial_number = '';

    public ?string $defect_description = '';

    public ?string $priority = 'normal';

    public array $visibleStageIds = [];

    public ?int $selectedPipelineId = null; // Для переключения воронок

    // Свойства для выбора устройства из справочника
    public ?string $device_type = null;

    public ?string $device_brand = null;

    public ?string $device_model = null;

    // Свойства для быстрого создания клиента
    public bool $showNewCustomerForm = false;

    public string $newCustomerName = '';

    public string $newCustomerPhone = '';

    public string $newCustomerEmail = '';

    public function mount()
    {
        $this->visibleStageIds = PipelineStage::pluck('id')->map(fn ($id) => (string) $id)->toArray();

        if (! auth()->user()->hasRole('Admin')) {
            $this->branch_id = auth()->user()->branch_id;
        }

        $firstPipeline = Pipeline::first();
        if ($firstPipeline) {
            $this->selectedPipelineId = $firstPipeline->id;
            $this->pipeline_id = $firstPipeline->id;
        }
    }

    #[Computed]
    public function deviceTypes()
    {
        return DeviceType::all();
    }

    #[Computed]
    public function deviceDict()
    {
        // Строим иерархический справочник: Тип -> Бренд -> Модели
        $dict = [];

        $types = DeviceType::with(['brands.models'])->get();

        foreach ($types as $type) {
            $dict[$type->name] = [];
            foreach ($type->brands as $brand) {
                $dict[$type->name][$brand->name] = $brand->models->pluck('name')->toArray();
            }
        }

        return $dict;
    }

    #[Computed]
    public function pipelines()
    {
        return Pipeline::all();
    }

    #[Computed]
    public function branches()
    {
        return Branch::all();
    }

    #[Computed]
    public function customers()
    {
        return Customer::all();
    }

    #[Computed]
    public function technicians()
    {
        return User::role('Technician')->get();
    }

    #[Computed(persist: false, cache: false)]
    public function stages()
    {
        if (! $this->selectedPipelineId) {
            return collect();
        }

        $isAdmin = auth()->user()->hasRole('Admin');
        $isTechnician = auth()->user()->hasRole('Technician');
        $technicianId = auth()->id();

        $filterTechnicianId = $this->filterTechnicianId !== null ? (int) $this->filterTechnicianId : null;
        $filterCustomerId = $this->filterCustomerId !== null ? $this->filterCustomerId : null;
        $filterBranchId = $this->filterBranchId !== null ? (int) $this->filterBranchId : null;

        return PipelineStage::where('pipeline_id', $this->selectedPipelineId)
            ->with([
                'tickets' => function ($query) use ($isAdmin, $isTechnician, $technicianId, $filterTechnicianId, $filterCustomerId, $filterBranchId) {
                    if ($isTechnician && ! $isAdmin) {
                        $query->where(function ($q) use ($technicianId) {
                            $q->where('assigned_technician_id', $technicianId)
                                ->orWhere(function ($subQ) {
                                    $subQ->whereHas('currentStage', function ($ss) {
                                        $ss->where('order_column', 1);
                                    })->whereNull('assigned_technician_id');
                                });
                        });
                    }

                    if ($filterTechnicianId !== null) {
                        $query->where('assigned_technician_id', $filterTechnicianId);
                    }

                    if ($filterCustomerId !== null) {
                        $query->where('customer_id', $filterCustomerId);
                    }

                    if ($filterBranchId !== null) {
                        $query->where('branch_id', $filterBranchId);
                    }

                    $query->latest();
                },
                'tickets.customer',
                'tickets.histories',
                'tickets.checklistResults.checklist.items',
                'tickets.checklistResults.user',
            ])
            ->orderBy('order_column')
            ->get();
    }

    public function save(CreateTicketAction $action)
    {
        $this->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_id' => 'required|exists:customers,id',
            'pipeline_id' => 'required|exists:pipelines,id',
            'device_type' => 'required|string',
            'device_brand' => 'required|string',
            'device_model' => 'required|string',
            'defect_description' => 'required|string|max:1000',
            'priority' => 'required|in:low,normal,urgent',
        ]);

        $dto = new CreateTicketData(
            branch_id: $this->branch_id,
            customer_id: $this->customer_id,
            pipeline_id: $this->pipeline_id,
            assigned_technician_id: null,
            device_type: $this->device_type,
            device_brand: $this->device_brand,
            device_model: $this->device_model,
            serial_number: empty($this->serial_number) ? null : $this->serial_number,
            defect_description: $this->defect_description,
            priority: $this->priority,
            estimated_cost: 0
        );

        try {
            $action->execute($dto);

            $targetPipelineId = $this->pipeline_id;

            $this->reset([
                'branch_id',
                'customer_id',
                'pipeline_id',
                'device_type',
                'device_brand',
                'device_model',
                'serial_number',
                'defect_description',
                'priority',
            ]);

            if (! auth()->user()->hasRole('Admin')) {
                $this->branch_id = auth()->user()->branch_id;
            }

            $this->showCreateForm = false;
            $this->selectedPipelineId = $targetPipelineId; // Переключаем доску на ту воронку, где создали заявку
            unset($this->stages);

        } catch (\Throwable $e) {
            // ЛОВИМ ЛЮБЫЕ ОШИБКИ (Включая БД и типы данных), чтобы кнопка не зависла!
            $this->addError('pipeline_id', $e->getMessage());
        }
    }

    public function moveTicket($ticketId, $nextStageId)
    {
        $ticket = Ticket::findOrFail($ticketId);

        $currentHistory = $ticket->histories()->whereNull('exited_at')->latest()->first();
        if ($currentHistory) {
            $currentHistory->update(['exited_at' => now(), 'duration_minutes' => now()->diffInMinutes($currentHistory->entered_at)]);
        }

        $ticket->update(['current_stage_id' => $nextStageId]);

        $ticket->histories()->create(['stage_id' => $nextStageId, 'user_id' => auth()->id() ?? 1, 'entered_at' => now()]);
        unset($this->stages);
    }

    public function claimTicket($ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);

        // Только техник может взять заявку без мастера
        if (! auth()->user()->hasRole('Technician') || ! is_null($ticket->assigned_technician_id)) {
            abort(403, 'Эта заявка уже назначена на мастера');
        }

        $ticket->update(['assigned_technician_id' => auth()->id()]);
        unset($this->stages);
    }

    public function saveNewCustomer(CreateCustomerAction $action)
    {
        $this->validate([
            'newCustomerName' => 'required|string|max:255',
            'newCustomerPhone' => 'nullable|string|max:20',
            'newCustomerEmail' => 'nullable|email|max:255',
        ]);

        $dto = new CustomerData(
            type: 'individual',
            name: $this->newCustomerName,
            phone: $this->newCustomerPhone ?: null,
            email: $this->newCustomerEmail ?: null,
            inn: null,
            kpp: null,
            legal_address: null,
            loyalty_level_id: null,
        );

        $customer = $action->execute($dto);

        // Automatically select the newly created customer
        $this->customer_id = $customer->id;
        $this->showNewCustomerForm = false;
        $this->reset(['newCustomerName', 'newCustomerPhone', 'newCustomerEmail']);

        // Refresh the customers list
        unset($this->customers);
    }

    public function render()
    {
        return view('livewire.ticketing.ticket-manager');
    }
}
