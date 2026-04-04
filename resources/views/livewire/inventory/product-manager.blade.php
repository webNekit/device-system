<div class="space-y-8">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-tighter italic">WMS: Номенклатура</h1>
        <div class="flex space-x-2">
            <a href="{{ route('inventory.index') }}" wire:navigate
                class="px-4 py-2 text-sm font-medium text-indigo-600 bg-white border border-indigo-600 rounded-md hover:bg-indigo-50">Склады
                и ячейки</a>
        </div>
    </div>

    @if($successMessage)
        <div class="p-4 bg-blue-50 text-blue-700 rounded-md border border-blue-200 text-sm">
            {{ $successMessage }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Секция: Создание и Приемка -->
        <div class="lg:col-span-1 space-y-8">
            <!-- Новый товар -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold mb-4">Новый товар</h2>
                <form wire:submit="createProduct" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 mb-1">Артикул (SKU)</label>
                        <div class="flex space-x-2">
                            <input type="text" wire:model="sku" placeholder="SCR-IP15-BLK" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 uppercase font-mono">
                            <button type="button" wire:click="generateSku" class="px-3 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg text-gray-600 transition-colors" title="Сгенерировать">
                                <span class="material-symbols-outlined text-[20px] flex items-center justify-center">autorenew</span>
                            </button>
                        </div>
                        @error('sku') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest text-gray-500 mb-1">Название</label>
                        <input type="text" wire:model="name" placeholder="Дисплей iPhone 15 Black" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2">
                    </div>
                    <button type="submit"
                        class="w-full bg-gray-900 text-white py-2 px-4 rounded-md hover:bg-gray-800 transition-colors text-sm font-medium">Добавить
                        в каталог</button>
                </form>
            </div>

            <!-- Приемка (Stock In) -->
            <div class="bg-indigo-900 p-6 rounded-lg shadow-sm text-white border border-indigo-800">
                <h2 class="text-lg font-bold mb-4">Оприходование</h2>
                <form wire:submit="stockIn" class="space-y-4 text-indigo-100">
                    <div>
                        <label class="block text-xs font-bold uppercase mb-1">Товар</label>
                        <select wire:model="selectedProductId"
                            class="w-full bg-indigo-800 border-indigo-700 rounded-md text-sm p-2 text-white">
                            <option value="">Выбрать из каталога...</option>
                            @foreach($this->products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase mb-1">Ячейка (Склад)</label>
                        <select wire:model="selectedLocationId"
                            class="w-full bg-indigo-800 border-indigo-700 rounded-md text-sm p-2 text-white">
                            <option value="">Куда положить?</option>
                            @foreach($this->warehouses as $warehouse)
                                <optgroup label="{{ $warehouse->name }}">
                                    @foreach($warehouse->locations as $location)
                                        <option value="{{ $location->id }}">{{ $location->full_address }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div class="col-span-1">
                            <label class="block text-[10px] font-bold uppercase mb-1">Кол-во</label>
                            <input px-4 w-full min-h-12 border border-gray-300 rounded-lg type="number" min="1"
                                wire:model="quantity"
                                class="w-full bg-indigo-800 border-indigo-700 rounded-md text-sm p-2 text-white">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-[10px] font-bold uppercase mb-1">S/N (только для 1 шт)</label>
                            <input px-4 w-full min-h-12 border border-gray-300 rounded-lg type="text"
                                wire:model="serial_number"
                                class="w-full bg-indigo-800 border-indigo-700 rounded-md text-sm p-2 text-white placeholder-indigo-400"
                                placeholder="Если есть">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-[10px] font-bold uppercase mb-1">Закуп (за 1 шт)</label>
                            <input px-4 w-full min-h-12 border border-gray-300 rounded-lg type="number" step="0.01"
                                wire:model="purchase_price"
                                class="w-full bg-indigo-800 border-indigo-700 rounded-md text-sm p-2 text-white">
                        </div>
                    </div>
                    <button type="submit"
                        class="w-full bg-white text-indigo-900 py-2 px-4 rounded-md hover:bg-indigo-50 transition-colors text-sm font-bold uppercase tracking-wider">Принять
                        на склад</button>
                </form>
            </div>
        </div>

        <!-- Таблица Номенклатуры -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden h-fit">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Товар / SKU</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">В наличии</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Статус</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($this->products as $product)
                        <tr class="hover:bg-gray-50 transition-colors cursor-pointer">
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900">{{ $product->name }}</div>
                                <div class="text-xs font-mono text-gray-500 uppercase">{{ $product->sku }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span
                                    class="text-lg font-bold {{ $product->stock <= $product->min_stock ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $product->stock }}
                                </span>
                                <span class="text-xs text-gray-400">шт.</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($product->stock <= $product->min_stock)
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Мало</span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Норма</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-sm text-gray-500 italic">Номенклатура пуста.
                                Начните с добавления первого товара.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4 border-t border-gray-100">
                {{ $this->products->links() }}
            </div>
        </div>
    </div>
</div>
