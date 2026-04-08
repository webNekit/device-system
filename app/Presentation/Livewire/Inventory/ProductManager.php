<?php

namespace App\Presentation\Livewire\Inventory;

use App\Domain\Inventory\Models\InventoryItem;
use App\Domain\Inventory\Models\Product;
use App\Domain\Inventory\Models\Warehouse;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('WMS: Номенклатура и остатки')]
class ProductManager extends Component
{
    use WithPagination;

    // Свойства для создания товара
    public string $sku = '';

    public string $name = '';

    public int $min_stock = 0;

    // Свойства для оприходования (Stock In)
    public ?string $selectedProductId = null;

    public int $quantity = 1;

    public string|int|null $selectedLocationId = null;

    public string $serial_number = '';

    public float $purchase_price = 0;

    public string $successMessage = '';

    #[Computed]
    public function products()
    {
        return Product::withCount(['items as stock' => function ($query) {
            $query->where('status', 'available');
        }])->latest()->paginate(10);
    }

    #[Computed]
    public function warehouses()
    {
        return Warehouse::with('locations')->get();
    }

    public function createProduct()
    {
        $this->validate([
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string|max:255',
        ]);

        Product::create([
            'sku' => $this->sku,
            'name' => $this->name,
            'min_stock' => $this->min_stock,
        ]);

        $this->reset(['sku', 'name', 'min_stock']);
        $this->successMessage = 'Товар добавлен в номенклатуру';
    }

    public function stockIn()
    {
        $this->validate([
            'selectedProductId' => 'required|exists:products,id',
            'selectedLocationId' => 'required|exists:storage_locations,id',
            'purchase_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1|max:500', // Ограничим максимум за раз
        ]);

        // Создаем записи в цикле, если пришла партия одинаковых деталей
        for ($i = 0; $i < $this->quantity; $i++) {
            InventoryItem::create([
                'product_id' => $this->selectedProductId,
                'storage_location_id' => $this->selectedLocationId,
                // Если количество больше 1, мы игнорируем S/N (т.к. у партии он не может быть один на всех)
                'serial_number' => ($this->quantity === 1 && $this->serial_number) ? $this->serial_number : null,
                'purchase_price' => $this->purchase_price,
                'status' => 'available',
            ]);
        }

        $this->reset(['selectedProductId', 'selectedLocationId', 'serial_number', 'purchase_price']);
        $this->quantity = 1; // Возвращаем по умолчанию

        $this->successMessage = "Успешно оприходовано: {$this->quantity} шт.";
        unset($this->products);
    }

    public function generateSku()
    {
        $this->sku = 'ITM-'.strtoupper(Str::random(5));
        $this->resetErrorBag('sku');
    }

    public function render()
    {
        return view('livewire.inventory.product-manager');
    }
}
