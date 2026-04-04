<div class="space-y-8">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 uppercase tracking-tighter italic">WMS: Склады и ячейки</h1>
        <div class="flex space-x-2">
            <a href="{{ route('inventory.products') }}" wire:navigate
                class="px-4 py-2 text-sm font-medium text-indigo-600 bg-white border border-indigo-600 rounded-md hover:bg-indigo-50 transition-colors">Номенклатура</a>
        </div>
    </div>

    @if($successMessage)
        <div
            class="p-4 bg-green-50 text-green-700 rounded-md border border-green-200 text-sm font-bold flex justify-between">
            {{ $successMessage }}
            <button wire:click="$set('successMessage', '')" class="text-green-700 hover:text-green-900">×</button>
        </div>
    @endif

    @error('location_error')
        <div class="p-4 bg-red-50 text-red-700 rounded-md border border-red-200 text-sm font-bold flex justify-between">
            {{ $message }}
            <button wire:click="$set('errorBag', [])" class="text-red-700 hover:text-red-900">×</button>
        </div>
    @enderror

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Левая колонка: Создание склада -->
        <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-fit">
            <h2 class="text-xs font-black uppercase text-gray-400 tracking-widest mb-4">Новый склад</h2>
            <form wire:submit="createWarehouse" class="space-y-4">
                <div>
                    <label class="block text-[10px] font-black uppercase text-gray-500 mb-1">Название</label>
                    <input type="text" wire:model="name" placeholder="Основной склад"
                        class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5">
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-gray-500 mb-1">Филиал</label>

                    @if(auth()->user()->hasRole('Admin'))
                        <select wire:model="branch_id"
                            class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5">
                            <option value="">Выбрать филиал...</option>
                            @foreach($this->branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <div
                            class="w-full rounded-lg border border-gray-200 bg-gray-100 sm:text-sm p-2.5 text-gray-700 font-bold">
                            {{ auth()->user()->branch->name ?? 'Не привязан' }}
                        </div>
                    @endif
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase text-gray-500 mb-1">Описание</label>
                    <textarea wire:model="description" rows="2"
                        class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2.5"></textarea>
                </div>
                <button type="submit"
                    class="w-full py-2.5 bg-gray-900 text-white font-black uppercase tracking-widest rounded-lg shadow hover:bg-black transition-all text-[10px]">
                    <span wire:loading.remove>Создать склад</span>
                    <span wire:loading>Обработка...</span>
                </button>
            </form>
        </div>

        <!-- Правая колонка: Список складов и ячеек -->
        <div class="lg:col-span-3 grid grid-cols-1 gap-6 items-start">
            @foreach($this->warehouses as $warehouse)
                <div
                    class="bg-white border {{ $selectedWarehouseId === $warehouse->id ? 'border-indigo-500 shadow-md ring-1 ring-indigo-500' : 'border-gray-100' }} rounded-2xl overflow-hidden flex flex-col transition-all">

                    <div class="p-5 bg-gray-50 border-b border-gray-100 flex justify-between items-start">
                        <div>
                            <h3 class="font-black italic text-gray-900 uppercase tracking-tighter">{{ $warehouse->name }}
                            </h3>
                            <div class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mt-1">
                                {{ $warehouse->branch->name }}</div>
                        </div>
                        <div class="flex space-x-3">
                            <button wire:click="selectWarehouse('{{ $warehouse->id }}')"
                                class="text-indigo-600 hover:text-indigo-800 text-[10px] font-black uppercase tracking-widest">
                                + Ячейка
                            </button>
                            <button wire:click="deleteWarehouse('{{ $warehouse->id }}')"
                                wire:confirm="Удалить склад и ВСЕ его пустые ячейки?"
                                class="text-gray-400 hover:text-red-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="p-5 flex-1">
                        @if($selectedWarehouseId === $warehouse->id)
                            <!-- Форма добавления ячейки -->
                            <div class="bg-indigo-50 p-4 rounded-xl mb-4 border border-indigo-100 relative">
                                <button wire:click="$set('selectedWarehouseId', null)"
                                    class="absolute top-2 right-2 text-indigo-300 hover:text-indigo-600">×</button>
                                <h4 class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-3">Добавить
                                    ячейку</h4>
                                <div class="grid grid-cols-3 gap-2 mb-3">
                                    <input type="text" wire:model="rack" placeholder="Стеллаж (А)"
                                        class="text-xs p-2 border border-indigo-200 rounded focus:border-indigo-500 focus:ring-0 w-full">
                                    <input type="text" wire:model="shelf" placeholder="Полка (1)"
                                        class="text-xs p-2 border border-indigo-200 rounded focus:border-indigo-500 focus:ring-0 w-full">
                                    <input type="text" wire:model="bin" placeholder="Место (1)"
                                        class="text-xs p-2 border border-indigo-200 rounded focus:border-indigo-500 focus:ring-0 w-full">
                                </div>
                                <button wire:click="addLocation"
                                    class="w-full bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest py-2 rounded hover:bg-indigo-700 transition-colors">Сохранить</button>
                            </div>
                        @endif

                        <!-- Вывод ячеек (Пилюли с нумерацией и крестиком) -->
                        <div class="flex flex-wrap gap-2">
                            @forelse($warehouse->locations as $index => $location)
                                <div
                                    class="group flex items-center bg-gray-100 rounded-md border {{ $viewingLocationId === $location->id ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-700' }} transition-colors">

                                    <!-- Кнопка просмотра внутренностей -->
                                    <button wire:click="viewLocation({{ $location->id }})"
                                        class="pl-2 pr-1 py-1 text-[11px] font-mono hover:text-indigo-600 flex items-center">
                                        <span class="text-[9px] font-bold text-gray-400 mr-1.5 pt-0.5">#{{ $index + 1 }}</span>
                                        {{ $location->full_address }}
                                    </button>

                                    <!-- Кнопка удаления (появляется при наведении) -->
                                    <button wire:click="deleteLocation({{ $location->id }})"
                                        wire:confirm="Удалить ячейку {{ $location->full_address }}?"
                                        class="hidden group-hover:flex px-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-r-md transition-colors h-full items-center">
                                        ×
                                    </button>
                                </div>
                            @empty
                                <div class="text-xs text-gray-400 italic">На этом складе еще нет созданных ячеек.</div>
                            @endforelse
                        </div>

                        <!-- Просмотр содержимого конкретной ячейки -->
                        @if($viewingLocationId && $warehouse->locations->contains('id', $viewingLocationId))
                            <div class="mt-6 p-4 border border-indigo-100 bg-indigo-50/30 rounded-xl">
                                <div class="flex justify-between items-center mb-4 border-b border-indigo-100 pb-2">
                                    <h4 class="text-[10px] font-black text-indigo-900 uppercase tracking-widest">
                                        Содержимое ячейки:
                                        {{ $warehouse->locations->firstWhere('id', $viewingLocationId)->full_address }}
                                    </h4>
                                    <button wire:click="$set('viewingLocationId', null)"
                                        class="text-gray-400 hover:text-gray-600 font-bold">×</button>
                                </div>

                                @if($this->locationItems->isEmpty())
                                    <p class="text-xs text-gray-500 italic text-center py-2">Ячейка физически пуста.</p>
                                @else
                                    <ul class="space-y-2">
                                        @foreach($this->locationItems as $item)
                                            <li
                                                class="flex justify-between items-center bg-white p-3 rounded-lg shadow-sm text-xs border border-gray-100">
                                                <div>
                                                    <div class="font-bold text-gray-800">{{ $item->product->name }}</div>
                                                    <div class="text-[10px] font-bold text-gray-400 uppercase mt-0.5">S/N: <span
                                                            class="text-gray-600">{{ $item->serial_number ?? 'Б/Н' }}</span></div>
                                                </div>
                                                <div class="font-black italic text-indigo-600">
                                                    {{ number_format($item->purchase_price, 0, '.', ' ') }} ₽</div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>