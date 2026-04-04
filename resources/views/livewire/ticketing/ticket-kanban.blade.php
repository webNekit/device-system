<div class="h-full flex flex-col space-y-4">
    <div class="flex justify-between items-center px-4">
        <h1 class="text-2xl font-black text-gray-900 italic uppercase tracking-tighter">Канбан заявок</h1>
        <div class="flex items-center space-x-2">
             @if(!$isCreating)
                <button wire:click="openCreateForm" class="px-4 py-2 text-sm font-bold text-white bg-gray-900 rounded-md shadow-sm hover:bg-black transition-all">+ Новая заявка</button>
             @else
                <button wire:click="$set('isCreating', false)" class="px-4 py-2 text-sm font-bold text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 transition-all underline decoration-indigo-500 underline-offset-4">Вернуться к доске</button>
             @endif

             <div class="flex items-center space-x-2 bg-white rounded-lg border p-1 shadow-sm ml-2">
                  <button class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md">Ремонт электроники</button>
             </div>
        </div>
    </div>

    @if($successMessage)
        <div class="mx-4 p-4 bg-blue-50 text-blue-700 rounded-md border border-blue-200 text-sm font-bold animate-pulse">
            {{ $successMessage }}
        </div>
    @endif

    @if($isCreating)
        <!-- Форма создания тикета -->
        <div class="flex-1 bg-white mx-4 rounded-xl border border-gray-200 p-8 shadow-sm max-w-2xl overflow-y-auto">
            <h2 class="text-xl font-black uppercase mb-6 italic tracking-tight">Оформление новой заявки</h2>
            
            <form wire:submit="createTicket" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black uppercase text-gray-500 mb-1">Клиент</label>
                        <select wire:model="customer_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2 bg-gray-50">
                            <option value="">Выберите клиента...</option>
                            @foreach($this->customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                            @endforeach
                        </select>
                        @error('customer_id') <span class="text-red-500 text-[10px] uppercase font-bold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase text-gray-500 mb-1">Приоритет</label>
                        <select wire:model="priority" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2 bg-gray-50">
                            <option value="low">Низкий</option>
                            <option value="normal">Нормальный</option>
                            <option value="urgent">Срочный (!)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black uppercase text-gray-500 mb-1">Бренд</label>
                        <input type="text" wire:model="device_brand" placeholder="Напр: Apple" class="w-full rounded-md border-gray-300 shadow-sm border p-2 sm:text-sm">
                        @error('device_brand') <span class="text-red-500 text-[10px] uppercase font-bold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase text-gray-500 mb-1">Модель</label>
                        <input type="text" wire:model="device_model" placeholder="Напр: iPhone 15 Pro" class="w-full rounded-md border-gray-300 shadow-sm border p-2 sm:text-sm">
                        @error('device_model') <span class="text-red-500 text-[10px] uppercase font-bold">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black uppercase text-gray-500 mb-1">Описание неисправности</label>
                    <textarea wire:model="defect_description" rows="3" placeholder="Что именно сломалось?" class="w-full rounded-md border-gray-300 shadow-sm border p-2 sm:text-sm"></textarea>
                    @error('defect_description') <span class="text-red-500 text-[10px] uppercase font-bold">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-black uppercase text-gray-500 mb-1">Ориентировочная стоимость</label>
                    <input type="number" wire:model="estimated_cost" class="w-full rounded-md border-gray-300 shadow-sm border p-2 sm:text-sm font-bold">
                </div>

                <div class="pt-4 pb-8">
                    <button type="submit" class="w-full py-3 bg-indigo-600 text-white font-black uppercase tracking-widest rounded-md hover:bg-indigo-700 transition-all shadow-lg">Создать и отправить в работу</button>
                </div>
            </form>
        </div>
    @else
        <!-- Kanban Board -->
        <div class="flex flex-1 space-x-4 overflow-x-auto pb-8 min-h-[calc(100vh-180px)] px-4">
            @foreach($this->stages as $stage)
                <div class="flex-shrink-0 w-80 flex flex-col bg-gray-100 rounded-xl border border-gray-200 shadow-inner">
                    <!-- Stage Header -->
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-white rounded-t-xl shadow-sm">
                        <div>
                            <h3 class="font-bold text-gray-800 uppercase text-[10px] tracking-widest">{{ $stage->name }}</h3>
                            @if($stage->sla_max_minutes)
                                <div class="text-[9px] text-gray-400 font-medium tracking-tight uppercase">SLA: {{ $stage->sla_max_minutes }} мин.</div>
                            @endif
                        </div>
                        <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded text-[10px] font-black italic">
                            {{ isset($this->tickets[$stage->id]) ? count($this->tickets[$stage->id]) : 0 }}
                        </span>
                    </div>

                    <!-- Tickets List -->
                    <div class="p-3 space-y-3 flex-1 overflow-y-auto">
                        @forelse($this->tickets[$stage->id] ?? [] as $ticket)
                            <a 
                                href="{{ route('tickets.show', $ticket->ulid) }}"
                                wire:navigate
                                wire:key="ticket-{{ $ticket->id }}"
                                class="group relative block w-full bg-white p-4 rounded-lg shadow-sm border {{ $ticket->is_sla_breached ? 'border-red-400 ring-2 ring-red-400 ring-opacity-20' : 'border-gray-200 hover:border-indigo-400 shadow-sm hover:shadow-md' }} transition-all decoration-transparent no-underline z-10"
                            >
                                <div class="flex justify-between items-start mb-2 pointer-events-none">
                                    <div class="flex flex-col">
                                        <span class="px-2 py-0.5 {{ $ticket->priority === 'urgent' ? 'bg-red-600 text-white' : 'bg-indigo-100 text-indigo-700' }} rounded text-[9px] font-black uppercase italic w-fit mb-1 tracking-tighter">
                                            {{ $ticket->priority }}
                                        </span>
                                        @if($ticket->sla_spent !== null)
                                            <div class="flex items-center space-x-1 {{ $ticket->is_sla_breached ? 'text-red-600 animate-pulse' : 'text-green-600' }} text-[9px] font-black uppercase tracking-tighter">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>{{ $ticket->sla_spent }}М</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-[9px] font-mono text-gray-400 font-bold tracking-tighter">ID: {{ substr($ticket->ulid, -6) }}</div>
                                </div>

                                <div class="text-sm font-black text-gray-900 mb-1 leading-tight uppercase tracking-tight pointer-events-none">
                                    {{ $ticket->device_brand }} {{ $ticket->device_model }}
                                </div>

                                <div class="text-[10px] text-gray-500 line-clamp-2 mb-3 h-6 leading-3 pointer-events-none">
                                    {{ $ticket->defect_description }}
                                </div>

                                <div class="pt-3 border-t border-gray-100 flex items-center justify-between pointer-events-none">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-6 h-6 rounded-full bg-gray-900 flex items-center justify-center text-[9px] font-black text-white">
                                            {{ mb_substr($customerName = $ticket->customer?->name ?? '?', 0, 1) }}
                                        </div>
                                        <span class="text-[10px] font-bold text-gray-700 uppercase tracking-tighter">{{ $customerName }}</span>
                                    </div>
                                    <div class="text-[10px] font-black text-gray-900 italic">
                                        {{ number_format($ticket->estimated_cost, 0, '.', ' ') }} ₽
                                    </div>
                                </div>

                                <!-- Кнопка переноса -->
                                @if(!$loop->parent->last)
                                    <button 
                                        wire:click.prevent.stop="moveToNextStage('{{ $ticket->id }}')"
                                        class="opacity-0 group-hover:opacity-100 flex absolute bottom-3 right-3 w-8 h-8 bg-indigo-600 text-white rounded-full items-center justify-center shadow-lg hover:bg-indigo-700 transform hover:scale-125 transition-all z-20 pointer-events-auto"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                    </button>
                                @endif
                            </a>
                        @empty
                            <div class="p-4 rounded-lg border border-dashed border-gray-300 text-center text-[10px] text-gray-400 uppercase font-bold italic">
                                Пусто
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
