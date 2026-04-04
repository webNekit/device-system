<?php

namespace App\Presentation\Livewire\Customer;

use App\Application\Services\DaDataService;
use App\Domain\Customer\Actions\CreateCustomerAction;
use App\Domain\Customer\DTOs\CustomerData;
use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Models\LoyaltyLevel;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Умная CRM')]
class CustomerManager extends Component
{
    use WithPagination;

    public bool $showCreateForm = false;

    // Свойства формы
    public string $type = 'individual'; // individual, legal

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $inn = '';

    public string $kpp = '';

    public string $legal_address = '';

    public ?int $loyalty_level_id = null;

    public ?string $editingCustomerId = null; // ULID

    public string $successMessage = '';

    #[Url(as: 'q')]
    public string $search = '';

    // #[Computed] для уровней лояльности
    #[Computed]
    public function loyaltyLevels()
    {
        return LoyaltyLevel::all();
    }

    // Список клиентов с пагинацией и поиском
    #[Computed]
    public function customers()
    {
        return Customer::with('loyaltyLevel')
            ->where('name', 'like', '%'.$this->search.'%')
            ->orWhere('phone', 'like', '%'.$this->search.'%')
            ->orWhere('inn', 'like', '%'.$this->search.'%')
            ->latest()
            ->paginate(10);
    }

    // Слушатель изменения ИНН (DaData)
    public function updatedInn($value, DaDataService $service)
    {
        if ($this->type === 'legal' && strlen($value) >= 10) {
            $data = $service->findByInn($value);

            if ($data) {
                $this->name = $data['value'] ?? '';
                $this->kpp = $data['data']['kpp'] ?? '';
                $this->legal_address = $data['data']['address']['value'] ?? '';
                $this->successMessage = 'Данные организации подгружены из DaData';
            }
        }
    }

    public function save(CreateCustomerAction $action)
    {
        $this->validate([
            'type' => 'required|string',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'inn' => 'nullable|string|max:12',
            'kpp' => 'nullable|string|max:9',
        ]);

        $data = new CustomerData(
            type: $this->type,
            name: $this->name,
            phone: $this->phone,
            email: $this->email,
            inn: $this->inn,
            kpp: $this->kpp,
            legal_address: $this->legal_address,
            loyalty_level_id: $this->loyalty_level_id,
        );

        if ($this->editingCustomerId) {
            $customer = Customer::findOrFail($this->editingCustomerId);
            $customer->update($data->toArray());
            $this->successMessage = 'Данные клиента обновлены';
        } else {
            $action->execute($data);
            $this->successMessage = 'Клиент успешно добавлен';
        }

        $this->cancelEdit();
        $this->showCreateForm = false;
    }

    public function openCreate()
    {
        $this->cancelEdit();
        $this->showCreateForm = true;
    }

    public function edit(string $id)
    {
        $customer = Customer::findOrFail($id);

        $this->editingCustomerId = $id;
        $this->type = $customer->type;
        $this->name = $customer->name;
        $this->phone = $customer->phone ?? '';
        $this->email = $customer->email ?? '';
        $this->inn = $customer->inn ?? '';
        $this->kpp = $customer->kpp ?? '';
        $this->legal_address = $customer->legal_address ?? '';
        $this->loyalty_level_id = $customer->loyalty_level_id;

        $this->successMessage = '';
        $this->showCreateForm = true;
    }

    public function cancelEdit()
    {
        $this->reset(['type', 'name', 'phone', 'email', 'inn', 'kpp', 'legal_address', 'loyalty_level_id', 'editingCustomerId']);
        $this->showCreateForm = false;
    }

    public function closeModal()
    {
        $this->showCreateForm = false;
    }

    public function delete(string $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        $this->successMessage = 'Клиент удален';

        if ($this->editingCustomerId === $id) {
            $this->cancelEdit();
        }
    }

    public function render()
    {
        return view('livewire.customer.customer-manager');
    }
}
