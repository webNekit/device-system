<?php

namespace App\Presentation\Livewire\Branch;

use App\Domain\Branch\Actions\CreateBranchAction;
use App\Domain\Branch\DTOs\BranchData;
use App\Domain\Branch\Models\Branch;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Управление филиалами')]
class BranchManager extends Component
{
    public bool $showCreateForm = false;

    public bool $showEditForm = false;

    // Свойства формы (общие для создания и редактирования)
    public string $name = '';

    public string $address = '';

    public string $timezone = 'Europe/Moscow';

    // ID редактируемого филиала (если null - режим создания)
    public ?int $editingBranchId = null;

    // Сообщение об успехе
    public string $successMessage = '';

    #[Computed]
    public function branches()
    {
        return Branch::latest()->get();
    }

    public function save(CreateBranchAction $action)
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'timezone' => 'required|string',
        ]);

        if ($this->editingBranchId) {
            // РЕЖИМ РЕДАКТИРОВАНИЯ
            $branch = Branch::findOrFail($this->editingBranchId);
            $branch->update([
                'name' => $this->name,
                'address' => $this->address,
                'timezone' => $this->timezone,
            ]);
            $this->successMessage = 'Филиал обновлен!';
            $this->showEditForm = false;
        } else {
            // РЕЖИМ СОЗДАНИЯ
            $dto = new BranchData(
                name: $this->name,
                address: $this->address,
                timezone: $this->timezone,
                settings: null
            );
            $action->execute($dto);
            $this->successMessage = 'Филиал успешно добавлен!';
            $this->showCreateForm = false;
        }

        $this->cancelEdit(); // Сброс формы
        unset($this->branches);
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showCreateForm = true;
        $this->showEditForm = false;
    }

    public function edit(int $id)
    {
        $branch = Branch::findOrFail($id);

        $this->editingBranchId = $id;
        $this->name = $branch->name;
        $this->address = $branch->address;
        $this->timezone = $branch->timezone;

        $this->showEditForm = true;
        $this->showCreateForm = false;
        $this->successMessage = '';
    }

    public function cancelEdit()
    {
        $this->reset(['name', 'address', 'timezone', 'editingBranchId']);
        $this->showCreateForm = false;
        $this->showEditForm = false;
    }

    public function closeModal()
    {
        $this->showCreateForm = false;
        $this->showEditForm = false;
    }

    public function resetForm()
    {
        $this->reset(['name', 'address', 'timezone', 'editingBranchId']);
    }

    public function delete(int $id)
    {
        $branch = Branch::findOrFail($id);

        if ($this->editingBranchId === $id) {
            $this->cancelEdit();
        }

        $branch->delete();
        $this->successMessage = 'Филиал удален!';
        unset($this->branches);
    }

    public function render()
    {
        return view('livewire.branch.branch-manager');
    }
}
