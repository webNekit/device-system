<?php

namespace App\Presentation\Livewire\Inventory;

use App\Domain\Branch\Models\Branch;
use App\Domain\Inventory\Models\InventoryItem;
use App\Domain\Inventory\Models\StorageLocation;
use App\Domain\Inventory\Models\Warehouse;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('WMS: Склады и ячейки')]
class WarehouseManager extends Component
{
    // Свойства для склада
    public string $name = '';

    public ?int $branch_id = null;

    public string $description = '';

    // Свойства для ячейки (быстрое добавление)
    public ?string $selectedWarehouseId = null;

    public string $rack = '';

    public string $shelf = '';

    public string $bin = '';

    // ID ячейки, которую мы сейчас просматриваем (чтобы увидеть детали внутри)
    public ?int $viewingLocationId = null;

    public string $successMessage = '';

    #[Computed]
    public function branches()
    {
        return Branch::all();
    }

    #[Computed]
    public function warehouses()
    {
        return Warehouse::with(['branch', 'locations'])->latest()->get();
    }

    // Получаем запчасти, которые лежат в выбранной ячейке
    #[Computed]
    public function locationItems()
    {
        if (! $this->viewingLocationId) {
            return collect();
        }

        return InventoryItem::with('product')
            ->where('storage_location_id', $this->viewingLocationId)
            ->where('status', 'available') // Показываем только те, что физически там лежат
            ->latest()
            ->get();
    }

    public function createWarehouse()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'required|exists:branches,id',
        ]);

        Warehouse::create([
            'name' => $this->name,
            'branch_id' => $this->branch_id,
            'description' => $this->description,
        ]);

        $this->reset(['name', 'branch_id', 'description']);
        $this->successMessage = 'Склад успешно создан';
        unset($this->warehouses);
    }

    public function selectWarehouse(string $id)
    {
        $this->selectedWarehouseId = $id;
        $this->viewingLocationId = null; // Сбрасываем просмотр ячейки при смене склада
        $this->successMessage = '';
    }

    public function addLocation()
    {
        $this->validate([
            'selectedWarehouseId' => 'required|exists:warehouses,id',
            'rack' => 'required|string',
            'shelf' => 'required|string',
            'bin' => 'required|string',
        ]);

        StorageLocation::create([
            'warehouse_id' => $this->selectedWarehouseId,
            'rack' => $this->rack,
            'shelf' => $this->shelf,
            'bin' => $this->bin,
            'label' => "{$this->rack}-{$this->shelf}-{$this->bin}",
        ]);

        $this->reset(['rack', 'shelf', 'bin']);
        $this->successMessage = 'Ячейка добавлена';
        unset($this->warehouses);
    }

    // Открываем просмотр внутренностей ячейки
    public function viewLocation(int $locationId)
    {
        $this->viewingLocationId = $locationId;
        $this->resetErrorBag('location_error');
    }

    // Удаление конкретной ячейки
    public function deleteLocation(int $id)
    {
        // Защита от дурака: проверяем, есть ли там детали
        $hasItems = InventoryItem::where('storage_location_id', $id)->where('status', 'available')->exists();

        if ($hasItems) {
            $this->addError('location_error', 'Нельзя удалить ячейку: в ней лежат запчасти!');

            return;
        }

        StorageLocation::findOrFail($id)->delete();
        $this->successMessage = 'Ячейка удалена';

        if ($this->viewingLocationId === $id) {
            $this->viewingLocationId = null;
        }

        unset($this->warehouses);
    }

    public function deleteWarehouse(string $id)
    {
        Warehouse::findOrFail($id)->delete();
        $this->successMessage = 'Склад удален';
        unset($this->warehouses);
    }

    public function mount()
    {
        if (! auth()->user()->hasRole('Admin')) {
            $this->branch_id = auth()->user()->branch_id;
        }
    }

    public function render()
    {
        return view('livewire.inventory.warehouse-manager');
    }
}
