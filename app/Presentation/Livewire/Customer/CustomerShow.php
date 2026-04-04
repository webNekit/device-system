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

    public function mount(Customer $customer)
    {
        $this->customer = $customer->load('loyaltyLevel');
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
