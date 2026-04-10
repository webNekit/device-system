<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Заявки на ремонт</h1>
            <p class="text-sm text-gray-500 mt-0.5">Управление сервисным центром</p>
        </div>
        @if(!$showCreateForm)
            <button wire:click="$set('showCreateForm', true)"
                class="btn-primary px-5 py-3 flex items-center bg-gray-900 text-gray-50 rounded-lg cursor-pointer">
                Создать заявку
            </button>
        @endif
    </div>

    <!-- Modal: Create Form -->
    @if($showCreateForm)
        <div class="fixed inset-0 modal-backdrop flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl max-w-3xl w-full p-6 shadow-xl border border-gray-200">
                <div class="flex justify-between items-start mb-5">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center">
                            <span class="material-symbols-outlined text-gray-600">confirmation_number</span>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Новая заявка</h3>
                            <p class="text-xs text-gray-500">Регистрация устройства</p>
                        </div>
                    </div>
                    <button wire:click="$set('showCreateForm', false)"
                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <span class="material-symbols-outlined text-gray-400">close</span>
                    </button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Филиал</label>
                            @if(auth()->user()->hasRole('Admin'))
                                <select wire:model="branch_id"
                                    class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg">
                                    <option value="">-- Выберите филиал --</option>
                                    @foreach($this->branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            @else
                                <div class="px-3 py-2 bg-gray-50 rounded-lg text-sm text-gray-700">
                                    {{ auth()->user()->branch->name ?? 'Не привязан' }}
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Клиент</label>
                            @if($showNewCustomerForm)
                                <div class="space-y-2 bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <input type="text" wire:model="newCustomerName"
                                        class="input px-3 w-full min-h-10 border border-gray-300 rounded-lg text-sm"
                                        placeholder="ФИО клиента" />
                                    <input type="tel" wire:model="newCustomerPhone"
                                        class="input px-3 w-full min-h-10 border border-gray-300 rounded-lg text-sm"
                                        placeholder="+7 (999) 000-00-00" />
                                    <input type="email" wire:model="newCustomerEmail"
                                        class="input px-3 w-full min-h-10 border border-gray-300 rounded-lg text-sm"
                                        placeholder="email@example.com" />
                                    <div class="flex space-x-2">
                                        <button type="button" wire:click="saveNewCustomer"
                                            class="text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-lg transition">
                                            <span wire:loading.remove wire:target="saveNewCustomer">Создать и выбрать</span>
                                            <span wire:loading wire:target="saveNewCustomer">Создание...</span>
                                        </button>
                                        <button type="button" wire:click="$set('showNewCustomerForm', false)"
                                            class="text-xs font-medium text-gray-600 hover:text-gray-800 px-2 py-1.5">
                                            Отмена
                                        </button>
                                    </div>
                                    @error('newCustomerName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    @error('newCustomerPhone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            @else
                                <select wire:model="customer_id"
                                    class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg">
                                    <option value="">-- Выберите клиента --</option>
                                    @foreach($this->customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="$set('showNewCustomerForm', true)"
                                    class="text-xs text-blue-600 hover:underline mt-1">
                                    + Создать нового клиента
                                </button>
                            @endif
                            @error('customer_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Процесс ремонта</label>
                            <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 text-sm text-gray-700">
                                {{ $this->pipelines->firstWhere('id', $selectedPipelineId)?->name ?? 'Ремонт электроники' }}
                            </div>
                            <input type="hidden" wire:model="pipeline_id" value="{{ $selectedPipelineId }}">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Приоритет</label>
                            <select wire:model="priority"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg">
                                <option value="low">Низкий</option>
                                <option value="normal">Нормальный</option>
                                <option value="urgent">Срочный</option>
                            </select>
                            @error('priority') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 bg-gray-50 rounded-lg p-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Тип устройства</label>
                            <select wire:model.live="device_type"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg">
                                <option value="">-- Выберите --</option>
                                @foreach(array_keys($this->deviceDict) as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('device_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Бренд</label>
                            <select wire:model.live="device_brand"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg" {{ empty($device_type) ? 'disabled' : '' }}>
                                <option value="">-- Выберите --</option>
                                @if(!empty($device_type) && isset($this->deviceDict[$device_type]))
                                    @foreach(array_keys($this->deviceDict[$device_type]) as $brand)
                                        <option value="{{ $brand }}">{{ $brand }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('device_brand') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Модель</label>
                            <select wire:model="device_model"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg" {{ empty($device_brand) ? 'disabled' : '' }}>
                                <option value="">-- Выберите --</option>
                                @if(!empty($device_type) && !empty($device_brand) && isset($this->deviceDict[$device_type][$device_brand]))
                                    @foreach($this->deviceDict[$device_type][$device_brand] as $model)
                                        <option value="{{ $model }}">{{ $model }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('device_model') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Серийный номер</label>
                        <input type="text" wire:model="serial_number"
                            class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg"
                            placeholder="S/N устройства" />
                        @error('serial_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Описание дефекта</label>
                        <textarea wire:model="defect_description" rows="3"
                            class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg resize-none"
                            placeholder="Опишите проблему устройства..."></textarea>
                        @error('defect_description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="$set('showCreateForm', false)"
                            class="btn-secondary">Отмена</button>
                        <button type="submit"
                            class="btn-primary px-5 py-3 flex items-center bg-gray-900 text-gray-50 rounded-lg cursor-pointer">
                            <span wire:loading.remove wire:target="save">Создать заявку</span>
                            <span wire:loading wire:target="save">Создание...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="card p-4">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Отображать:</span>
            <button
                wire:click="$set('visibleStageIds', {{ $this->stages->pluck('id')->map(fn($id) => (string) $id)->toJson() }})"
                class="text-xs font-medium text-blue-600 hover:underline">
                Показать все
            </button>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-2">
            @foreach($this->stages as $stage)
                <label
                    class="flex items-center space-x-2 p-2.5 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors bg-white">
                    <input type="checkbox" wire:model.live="visibleStageIds" value="{{ $stage->id }}"
                        class="filter-checkbox" />
                    <span class="text-xs font-medium text-gray-700">{{ $stage->name }}</span>
                </label>
            @endforeach
        </div>
    </div>

    @if(auth()->user()->hasRole('Admin'))
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 rounded-xl p-5 shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-lg">filter_list</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-white">Фильтры</h3>
                        <p class="text-xs text-gray-400">Отбор заявок по филиалам, техникам и клиентам</p>
                    </div>
                </div>
                @if($filterTechnicianId || $filterCustomerId || $filterBranchId)
                    <button wire:click="$set('filterTechnicianId', null); $set('filterCustomerId', null); $set('filterBranchId', null)"
                        class="flex items-center space-x-1.5 px-3 py-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-300 rounded-lg transition text-xs font-medium">
                        <span class="material-symbols-outlined text-sm">close</span>
                        <span>Сбросить</span>
                    </button>
                @endif
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="relative group">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center pointer-events-none transition group-focus-within:bg-white/20">
                        <span class="material-symbols-outlined text-gray-400 text-lg">store</span>
                    </div>
                    <select wire:model.live="filterBranchId"
                        class="w-full pl-11 pr-4 py-3 bg-white/10 border border-white/10 rounded-lg text-white text-sm appearance-none cursor-pointer hover:bg-white/15 focus:bg-white/20 focus:border-white/30 focus:outline-none transition">
                        <option value="" class="bg-gray-900">Все филиалы</option>
                        @foreach($this->branches as $branch)
                            <option value="{{ $branch->id }}" class="bg-gray-900">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div class="relative group">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center pointer-events-none transition group-focus-within:bg-white/20">
                        <span class="material-symbols-outlined text-gray-400 text-lg">engineering</span>
                    </div>
                    <select wire:model.live="filterTechnicianId"
                        class="w-full pl-11 pr-4 py-3 bg-white/10 border border-white/10 rounded-lg text-white text-sm appearance-none cursor-pointer hover:bg-white/15 focus:bg-white/20 focus:border-white/30 focus:outline-none transition">
                        <option value="" class="bg-gray-900">Все техники</option>
                        @foreach($this->technicians as $tech)
                            <option value="{{ $tech->id }}" class="bg-gray-900">{{ $tech->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div class="relative group">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center pointer-events-none transition group-focus-within:bg-white/20">
                        <span class="material-symbols-outlined text-gray-400 text-lg">person</span>
                    </div>
                    <select wire:model.live="filterCustomerId"
                        class="w-full pl-11 pr-4 py-3 bg-white/10 border border-white/10 rounded-lg text-white text-sm appearance-none cursor-pointer hover:bg-white/15 focus:bg-white/20 focus:border-white/30 focus:outline-none transition">
                        <option value="" class="bg-gray-900">Все клиенты</option>
                        @foreach($this->customers as $customer)
                            <option value="{{ $customer->id }}" class="bg-gray-900">{{ $customer->name }} ({{ $customer->phone }})</option>
                        @endforeach
                    </select>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>
            @if($filterTechnicianId || $filterCustomerId || $filterBranchId)
                <div class="mt-4 flex flex-wrap items-center gap-2 pt-4 border-t border-white/10">
                    <span class="text-xs text-gray-400">Активные фильтры:</span>
                    @if($filterBranchId)
                        @php $branch = $this->branches->find($filterBranchId) @endphp
                        <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 bg-purple-500/20 text-purple-300 rounded-md text-xs">
                            <span class="material-symbols-outlined text-xs">store</span>
                            <span>{{ $branch?->name ?? 'Филиал' }}</span>
                        </span>
                    @endif
                    @if($filterTechnicianId)
                        @php $tech = $this->technicians->find($filterTechnicianId) @endphp
                        <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 bg-blue-500/20 text-blue-300 rounded-md text-xs">
                            <span class="material-symbols-outlined text-xs">engineering</span>
                            <span>{{ $tech?->name ?? 'Техник' }}</span>
                        </span>
                    @endif
                    @if($filterCustomerId)
                        @php $customer = $this->customers->find($filterCustomerId) @endphp
                        <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 bg-green-500/20 text-green-300 rounded-md text-xs">
                            <span class="material-symbols-outlined text-xs">person</span>
                            <span>{{ $customer?->name ?? 'Клиент' }}</span>
                        </span>
                    @endif
                </div>
            @endif
        </div>
    @endif

    <!-- Kanban Board -->
    <div class="grid grid-cols-[repeat(auto-fit,minmax(320px,1fr))] gap-4 items-start">
        @php $stagesList = $this->stages->values(); @endphp

        @foreach($this->stages as $index => $stage)
            @if(in_array((string) $stage->id, $this->visibleStageIds))
                <div class="card flex flex-col">
                    <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center bg-gray-50 rounded-t-xl">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase">{{ $stage->name }}</h3>
                        <span
                            class="px-2 py-0.5 bg-gray-900 text-white rounded-full text-xs font-bold">{{ $stage->tickets->count() }}</span>
                    </div>

                    <div class="p-3 space-y-2 flex-1">
                        @foreach($stage->tickets as $ticket)
                            <div class="relative group">
                                <a href="{{ route('tickets.show', $ticket->ulid) }}" wire:navigate
                                    class="block bg-white p-3 rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-sm transition-all {{ is_null($ticket->assigned_technician_id) ? 'border-dashed border-indigo-300 bg-indigo-50/30' : '' }}">

                                    <div class="flex justify-between items-start mb-2">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $ticket->device_brand }} {{ $ticket->device_model }}
                                        </div>
                                        @if(is_null($ticket->assigned_technician_id))
                                            <span class="px-1.5 py-0.5 bg-indigo-100 text-indigo-700 rounded text-[9px] font-bold uppercase">Не назначен</span>
                                        @endif
                                    </div>

                                    <p class="text-xs text-gray-600 line-clamp-2 mb-2 italic">
                                        «{{ Str::limit($ticket->defect_description, 50) }}»
                                    </p>

                                    <div class="flex items-center justify-between mt-auto pt-2 border-t border-gray-100">
                                        <div class="flex items-center text-[10px] font-medium text-gray-500 uppercase">
                                            <span class="material-symbols-outlined text-xs mr-1">person</span>
                                            {{ $ticket->assignedTechnician?->name ? Str::limit($ticket->assignedTechnician->name, 12) : '—' }}
                                        </div>

                                        @if($ticket->sla_deadline_at)
                                            <div
                                                class="flex items-center text-[10px] font-medium {{ $ticket->sla_deadline_at < now() ? 'text-red-600' : 'text-green-600' }}">
                                                <span class="material-symbols-outlined text-xs mr-1">schedule</span>
                                                {{ $ticket->sla_deadline_at->format('H:i') }}
                                            </div>
                                        @endif
                                    </div>
                                </a>

                                <!-- Кнопка "Взять в работу" для техников -->
                                @if(is_null($ticket->assigned_technician_id) && auth()->user()->hasRole('Technician'))
                                    <button wire:click="claimTicket('{{ $ticket->id }}')"
                                        class="absolute hidden group-hover:flex right-2 top-2 bg-indigo-600 text-white hover:bg-indigo-700 px-2 py-1.5 rounded-lg transition z-20 shadow-sm text-[10px] font-bold uppercase">
                                        <span class="material-symbols-outlined text-xs mr-1">how_to_reg</span>
                                        Взять
                                    </button>
                                @elseif(isset($stagesList[$index + 1]))
                                    @php $nextStage = $stagesList[$index + 1]; @endphp
                                    <button wire:click="moveTicket('{{ $ticket->id }}', {{ $nextStage->id }})"
                                        class="absolute hidden group-hover:flex right-2 top-2 bg-white text-gray-600 hover:bg-gray-900 hover:text-white p-1.5 rounded-lg transition z-20 shadow-sm border border-gray-200">
                                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                    </button>
                                @endif
                            </div>
                        @endforeach

                        @if($stage->tickets->isEmpty())
                            <div class="h-full flex items-center justify-center py-8">
                                <div class="text-xs text-gray-400 font-medium italic">Пусто</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>