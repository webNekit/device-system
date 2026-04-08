<?php

namespace App\Presentation\Livewire\Customer;

use App\Domain\Customer\Models\Customer;
use App\Domain\Ticketing\Models\Ticket;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Профиль клиента')]
class CustomerShow extends Component
{
    use WithPagination;

    public Customer $customer;

    #[Url(as: 'q')]
    public string $search = '';

    // Edit modal properties
    public bool $showEditModal = false;

    public string $editType = 'individual';

    public string $editName = '';

    public string $editPhone = '';

    public string $editEmail = '';

    public string $editInn = '';

    public string $editKpp = '';

    public string $editLegalAddress = '';

    public string $successMessage = '';

    public function mount(Customer $customer)
    {
        $this->customer = $customer->load('loyaltyLevel');
    }

    public function updateCustomer()
    {
        $this->validate([
            'editType' => 'required|string',
            'editName' => 'required|string|max:255',
            'editPhone' => 'nullable|string|max:20',
            'editEmail' => 'nullable|email|max:255',
            'editInn' => 'nullable|string|max:12',
            'editKpp' => 'nullable|string|max:9',
        ]);

        $this->customer->update([
            'type' => $this->editType,
            'name' => $this->editName,
            'phone' => $this->editPhone ?: null,
            'email' => $this->editEmail ?: null,
            'inn' => $this->editInn ?: null,
            'kpp' => $this->editKpp ?: null,
            'legal_address' => $this->editLegalAddress ?: null,
        ]);

        $this->showEditModal = false;
        $this->successMessage = 'Данные клиента успешно обновлены';
        $this->customer->refresh();
    }

    public function deleteCustomer()
    {
        $this->customer->delete();

        return redirect()->route('customers.index');
    }

    public function openEditModal()
    {
        $this->editType = $this->customer->type;
        $this->editName = $this->customer->name;
        $this->editPhone = $this->customer->phone ?? '';
        $this->editEmail = $this->customer->email ?? '';
        $this->editInn = $this->customer->inn ?? '';
        $this->editKpp = $this->customer->kpp ?? '';
        $this->editLegalAddress = $this->customer->legal_address ?? '';
        $this->showEditModal = true;
    }

    #[Computed]
    public function stats()
    {
        // Получаем все заявки клиента
        $tickets = Ticket::where('customer_id', $this->customer->id)->get();

        // Активные заявки (не дошли до 7 или 8 стадии)
        $active = $tickets->where('currentStage.order_column', '<', 7)->count();

        // Выполненные (стадия 7 или 8, и сумма > 0)
        $completed = $tickets->where('currentStage.order_column', '>=', 7)
            ->where('estimated_cost', '>', 0);

        // Отказы (закрыто, но сумма = 0)
        $rejected = $tickets->where('currentStage.order_column', '>=', 7)
            ->where('estimated_cost', 0)->count();

        // LTV (Сколько всего денег принес клиент)
        $ltv = $completed->sum('estimated_cost');

        return [
            'total' => $tickets->count(),
            'active' => $active,
            'completed' => $completed->count(),
            'rejected' => $rejected,
            'ltv' => $ltv,
        ];
    }

    #[Computed]
    public function tickets()
    {
        return Ticket::where('customer_id', $this->customer->id)
            ->with(['currentStage', 'usedParts.inventoryItem.product'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('device_brand', 'like', '%'.$this->search.'%')
                        ->orWhere('device_model', 'like', '%'.$this->search.'%')
                        ->orWhere('ulid', 'like', '%'.$this->search.'%')
                        ->orWhere('defect_description', 'like', '%'.$this->search.'%');
                });
            })
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.customer.customer-show');
    }
}
