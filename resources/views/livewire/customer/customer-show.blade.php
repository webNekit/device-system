<div class="space-y-6 pb-12">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('customers.index') }}" wire:navigate
                class="p-2 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors shadow-sm">
                <span class="material-symbols-outlined text-gray-500 text-sm">arrow_back</span>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-900 uppercase tracking-tight">{{ $customer->name }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">Профиль клиента и история</p>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <button wire:click="openEditModal"
                class="px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg text-sm font-medium flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">edit</span>
                Редактировать
            </button>
            <button wire:click="deleteCustomer"
                wire:confirm="Вы уверены, что хотите удалить этого клиента? Это действие нельзя отменить."
                class="px-4 py-2 bg-red-50 border border-red-200 text-red-600 hover:bg-red-100 rounded-lg text-sm font-medium flex items-center gap-1">
                <span class="material-symbols-outlined text-sm">delete</span>
                Удалить
            </button>
            <a href="{{ route('tickets.index') }}" wire:navigate class="btn-primary">
                <span class="material-symbols-outlined text-sm">add</span>
                Новая заявка
            </a>
        </div>
    </div>

    <!-- Edit Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 modal-backdrop flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl max-w-2xl w-full p-6 shadow-xl border border-gray-200">
                <div class="flex justify-between items-start mb-5">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center">
                            <span class="material-symbols-outlined text-gray-600">edit</span>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Редактировать клиента</h3>
                            <p class="text-xs text-gray-500">Изменение контактных данных</p>
                        </div>
                    </div>
                    <button wire:click="$set('showEditModal', false)"
                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <span class="material-symbols-outlined text-gray-400">close</span>
                    </button>
                </div>

                <form wire:submit="updateCustomer" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Тип</label>
                            <select wire:model.live="editType"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg">
                                <option value="individual">Физическое лицо</option>
                                <option value="legal">Юридическое лицо</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">ФИО / Название</label>
                            <input type="text" wire:model="editName"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg"
                                placeholder="Иванов И.И." />
                            @error('editName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Телефон</label>
                            <input type="tel" wire:model="editPhone"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg"
                                placeholder="+7 (999) 000-00-00" />
                            @error('editPhone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Email</label>
                            <input type="email" wire:model="editEmail"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg"
                                placeholder="email@example.com" />
                            @error('editEmail') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @if($editType === 'legal')
                        <div class="p-4 bg-gray-50 rounded-lg space-y-3">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">ИНН</label>
                                    <input type="text" wire:model="editInn"
                                        class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg"
                                        placeholder="1234567890" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">КПП</label>
                                    <input type="text" wire:model="editKpp"
                                        class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg"
                                        placeholder="123456789" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Юр. адрес</label>
                                <input type="text" wire:model="editLegalAddress"
                                    class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg"
                                    placeholder="г. Москва, ул..." />
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="$set('showEditModal', false)"
                            class="btn-secondary cursor-pointer">Отмена</button>
                        <button type="submit"
                            class="btn-primary px-5 py-3 flex items-center bg-gray-900 text-gray-50 rounded-lg cursor-pointer">
                            <span wire:loading.remove wire:target="updateCustomer">Сохранить</span>
                            <span wire:loading wire:target="updateCustomer">Сохранение...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($successMessage)
        <div class="p-3 bg-green-50 text-green-700 rounded-lg text-sm flex items-center gap-2">
            <span class="material-symbols-outlined fill text-sm">check_circle</span>
            <span>{{ $successMessage }}</span>
            <button wire:click="$set('successMessage', '')" class="ml-auto">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
    @endif

    <!-- Клиент и Статистика (LTV) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Инфо -->
        <div class="card p-6 flex flex-col justify-center">
            <div class="flex items-center space-x-4 mb-6">
                <div
                    class="w-16 h-16 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 text-2xl font-black uppercase shadow-sm">
                    {{ mb_substr($customer->name, 0, 1) }}
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900">{{ $customer->name }}</h2>
                    <div class="mt-1">
                        @if($customer->type === 'legal')
                            <span class="badge badge-gray">Юр. лицо (ИНН: {{ $customer->inn }})</span>
                        @else
                            <span class="badge badge-info">Физ. лицо</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-3 text-sm text-gray-600 border-t border-gray-100 pt-4">
                <div class="flex items-center"><span
                        class="material-symbols-outlined text-sm mr-2 text-gray-400">call</span> {{ $customer->phone }}
                </div>
                @if($customer->email)
                    <div class="flex items-center"><span
                            class="material-symbols-outlined text-sm mr-2 text-gray-400">mail</span> {{ $customer->email }}
                    </div>
                @endif
                <div class="flex items-center"><span
                        class="material-symbols-outlined text-sm mr-2 text-gray-400">loyalty</span> Уровень: <strong
                        class="ml-1 text-indigo-600">{{ $customer->loyaltyLevel->name ?? 'Базовый' }}</strong></div>
            </div>
        </div>

        <!-- Показатели (KPI) -->
        <div class="lg:col-span-2 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div
                class="card p-5 flex flex-col justify-center bg-gradient-to-br from-gray-900 to-black text-white border-0">
                <div class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Принес выручки (LTV)
                </div>
                <div class="text-2xl font-black italic">
                    <span
                        class="text-gray-900">{{ $this->stats['ltv'] > 0 ? number_format($this->stats['ltv'], 0, '.', ' ') : '0' }}</span>
                    <span class="text-lg text-gray-400">₽</span>
                </div>
            </div>
            <div class="card p-5 flex flex-col justify-center">
                <div class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-1">Всего заявок</div>
                <div class="text-2xl font-black text-gray-900">{{ $this->stats['total'] }}</div>
            </div>
            <div class="card p-5 flex flex-col justify-center">
                <div class="text-[10px] font-bold uppercase tracking-widest text-indigo-500 mb-1">В работе сейчас</div>
                <div class="text-2xl font-black text-indigo-600">{{ $this->stats['active'] }}</div>
            </div>
            <div class="card p-5 flex flex-col justify-center">
                <div class="text-[10px] font-bold uppercase tracking-widest text-red-500 mb-1">Отказы от ремонта</div>
                <div class="text-2xl font-black text-red-600">{{ $this->stats['rejected'] }}</div>
            </div>
        </div>
    </div>

    <!-- Таблица истории заявок -->
    <div class="table-container">
        <div
            class="px-5 py-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gray-50/50">
            <div class="flex items-center space-x-3">
                <div
                    class="w-8 h-8 rounded-lg bg-white border border-gray-200 shadow-sm flex items-center justify-center">
                    <span class="material-symbols-outlined text-gray-600 text-sm">history</span>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">История обращений</h3>
                    <p class="text-xs text-gray-500">Техника, запчасти и суммы</p>
                </div>
            </div>
            <div class="relative w-full md:w-72">
                <span
                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">search</span>
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Поиск по устройству или дефекту..."
                    class="pl-9 pr-4 py-2 w-full bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm" />
            </div>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($this->tickets as $ticket)
                <div class="p-5 hover:bg-gray-50 transition-colors">
                    <div class="flex flex-col md:flex-row justify-between gap-4">

                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <h4 class="text-sm font-bold text-gray-900 uppercase">{{ $ticket->device_brand }}
                                    {{ $ticket->device_model }}
                                </h4>
                                <span
                                    class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-widest border {{ $ticket->currentStage->order_column >= 7 ? 'bg-green-50 border-green-200 text-green-700' : 'bg-indigo-50 border-indigo-200 text-indigo-700' }}">
                                    {{ $ticket->currentStage->name }}
                                </span>
                                <span
                                    class="text-[10px] text-gray-400 font-mono">{{ $ticket->created_at->format('d.m.Y H:i') }}</span>
                            </div>

                            <p class="text-xs text-gray-600 mb-3 italic">«{{ $ticket->defect_description }}»</p>

                            <!-- Использованные запчасти -->
                            @if($ticket->usedParts->isNotEmpty())
                                <div class="flex flex-wrap gap-2 mt-2">
                                    @foreach($ticket->usedParts as $part)
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100 border border-gray-200 text-[10px] text-gray-600 font-medium">
                                            <span class="material-symbols-outlined text-[12px] mr-1 text-gray-400">build</span>
                                            {{ $part->inventoryItem->product->name ?? 'Деталь' }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col items-end justify-between md:min-w-[150px]">
                            <div
                                class="text-lg font-black italic {{ $ticket->estimated_cost == 0 && $ticket->currentStage->order_column >= 7 ? 'text-red-500' : 'text-gray-900' }}">
                                {{ $ticket->estimated_cost == 0 && $ticket->currentStage->order_column >= 7 ? 'ОТКАЗ' : number_format($ticket->estimated_cost, 0, '.', ' ') . ' ₽' }}
                            </div>
                            <a href="{{ route('tickets.show', $ticket->ulid) }}" wire:navigate
                                class="mt-3 px-4 py-2 bg-white border border-gray-200 text-gray-700 hover:bg-gray-900 hover:text-white hover:border-gray-900 rounded-lg text-xs font-bold uppercase tracking-widest transition-all shadow-sm">
                                Карточка ->
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <div
                        class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-50 border border-gray-200 flex items-center justify-center">
                        <span class="material-symbols-outlined text-gray-400 text-lg">search_off</span>
                    </div>
                    <p class="text-sm font-medium text-gray-500">Заявки не найдены</p>
                </div>
            @endforelse
        </div>

        @if($this->tickets->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $this->tickets->links() }}
            </div>
        @endif
    </div>
</div>