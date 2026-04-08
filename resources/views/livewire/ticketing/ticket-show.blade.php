<div class="space-y-6">
    @if($successMessage)
        <div class="p-3 bg-green-50 text-green-700 rounded-lg text-sm flex items-center gap-2">
            <span class="material-symbols-outlined fill text-sm">check_circle</span>
            <span>{{ $successMessage }}</span>
            <button wire:click="$set('successMessage', '')" class="ml-auto">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
    @endif

    <!-- Header -->
    <div class="card p-5">
        <div class="flex justify-between items-start">
            <div class="flex items-center space-x-4">
                <a href="{{ route('tickets.index') }}" wire:navigate
                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <span class="material-symbols-outlined text-gray-600">arrow_back</span>
                </a>
                <div>
                    <div class="flex items-center space-x-2">
                        <span
                            class="px-2 py-1 bg-gray-900 text-white rounded text-xs font-medium uppercase">{{ $ticket->priority }}</span>
                        <h1 class="text-lg font-semibold text-gray-900 uppercase">{{ $ticket->device_brand }}
                            {{ $ticket->device_model }}
                        </h1>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5 font-mono">ID: {{ $ticket->ulid }}</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase">Текущий статус</p>
                    <p class="text-sm font-semibold text-gray-900 uppercase">{{ $ticket->currentStage->name }}</p>
                </div>

                @if($ticket->currentStage->order_column == 3)
                    <button wire:click="generateMagicLink" class="btn-secondary text-blue-500">
                        Согласовать с клиентом
                    </button>
                @endif

                @if($ticket->currentStage->order_column == 7)
                    <div class="flex flex-col items-end">
                        <button wire:click="generateInvoiceAndClose"
                            class="btn-primary px-5 py-3 flex items-center bg-gray-900 text-gray-50 rounded-lg cursor-pointer">
                            Выдать и печать Акта
                        </button>
                        @if($ticket->customer->email)
                            <span class="text-[10px] text-green-600 font-medium mt-1 flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">mail</span>
                                Email уведомление будет отправлено
                            </span>
                        @endif
                    </div>
                @elseif($this->nextStage)
                    <button wire:click="moveToNextStage" class="btn-primary">
                        В «{{ $this->nextStage->name }}»
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Timeline -->
        <div class="lg:col-span-3">
            <div class="card p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase mb-4">Таймлайн</h3>
                <div class="space-y-4">
                    @foreach($ticket->histories as $history)
                        <div
                            class="relative pl-6 pb-4 border-l-2 {{ $history->exited_at ? 'border-gray-300' : 'border-gray-300 border-dashed' }}">
                            <div
                                class="absolute -left-[5px] top-0 w-2.5 h-2.5 rounded-full {{ $history->exited_at ? 'bg-gray-900' : 'bg-white border-2 border-gray-900' }}">
                            </div>
                            <p class="text-xs text-gray-500">{{ $history->entered_at->format('d.m H:i') }}</p>
                            <p class="text-sm font-medium text-gray-900 uppercase">{{ $history->stage->name }}</p>
                            @if($history->exited_at)
                                <p class="text-xs text-gray-400 mt-0.5">На этапе:
                                    {{ round($history->entered_at->diffInMinutes($history->exited_at)) }} мин.
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Main Info -->
        <div class="lg:col-span-6 space-y-4">
            <!-- Specs -->
            <div class="card p-5 bg-gray-900">
                <h3 class="text-xs font-semibold text-gray-400 uppercase mb-4">Характеристики</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @forelse($specs as $key => $value)
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase mb-1">{{ $key }}</p>
                            <p class="text-sm font-medium text-white">{{ $value }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500 col-span-4">Спецификации не найдены</p>
                    @endforelse
                </div>
            </div>

            <!-- Defect -->
            <div class="card p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase mb-3">Заявленный дефект</h3>
                <p class="text-sm text-gray-900 italic">«{{ $ticket->defect_description }}»</p>
            </div>
            <div class="card p-5 bg-indigo-50 border-indigo-100">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-xs font-semibold text-indigo-700 uppercase mb-1">Ответственный мастер</h3>
                        @if($ticket->assigned_technician_id)
                            <p class="text-sm font-bold text-gray-900">{{ $ticket->technician->name ?? 'Неизвестен' }}</p>
                        @else
                            <p class="text-sm text-gray-500 italic">Мастер еще не назначен</p>
                        @endif
                    </div>

                    <div class="flex flex-col items-end space-y-2">
                        @if(!$ticket->assigned_technician_id && auth()->user()->hasRole('Technician'))
                            <button wire:click="assignToMe"
                                class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold uppercase rounded-lg hover:bg-indigo-700 transition">
                                Взять в работу
                            </button>
                        @endif

                        @if(auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Branch Manager'))
                            <div>
                                <select wire:model="selectedTechnicianId" class="input text-xs p-2 border border-gray-300 rounded-lg min-w-[200px]">
                                    <option value="">-- Назначить мастера --</option>
                                    @foreach($this->technicians as $tech)
                                        <option value="{{ $tech->id }}">{{ $tech->name }} ({{ $tech->email }})</option>
                                    @endforeach
                                </select>
                                <button wire:click="assignTechnician"
                                    class="mt-1 px-3 py-1 bg-indigo-600 text-white text-xs font-medium rounded hover:bg-indigo-700 transition">
                                    @if($ticket->assigned_technician_id)Переназначить@else Назначить@endif
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Parts -->
            <div class="card p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase">Запчасти и материалы</h3>
                    @if(!$showPartForm)
                        <button wire:click="$set('showPartForm', true)"
                            class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            + Добавить
                        </button>
                    @endif
                </div>

                @if($showPartForm)
                    <div class="bg-gray-50 rounded-lg p-4 mb-4 space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Поиск по складу</label>
                            <input type="text" wire:model.live.debounce.300ms="searchPartSku" class="input"
                                placeholder="Например: IPH13..." />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Выберите деталь</label>
                            <select wire:model="selectedPartId" class="input">
                                <option value="">-- Выберите --</option>
                                @foreach($this->availableParts as $part)
                                    <option value="{{ $part->id }}">{{ $part->product->name }} (S/N:
                                        {{ $part->serial_number ?? 'НЕТ' }}) — {{ $part->purchase_price ?? 0 }} ₽
                                    </option>
                                @endforeach
                            </select>
                            @error('selectedPartId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Цена (₽)</label>
                                <input type="number" wire:model="partSellingPrice" class="input" />
                                @error('partSellingPrice') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Гарантия (дней)</label>
                                <input type="number" wire:model="partWarrantyDays" class="input" />
                                @error('partWarrantyDays') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex space-x-3 pt-2">
                            <button wire:click="attachPart" wire:loading.attr="disabled" class="btn-primary">
                                <span wire:loading.remove wire:target="attachPart">Привязать</span>
                                <span wire:loading wire:target="attachPart">Привязка...</span>
                            </button>
                            <button wire:click="$set('showPartForm', false)" class="btn-secondary">Отмена</button>
                        </div>
                    </div>
                @endif

                @if($ticket->usedParts->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($ticket->usedParts as $usedPart)
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg border border-gray-100">
                                @if($editingPartId === $usedPart->id)
                                    <!-- Edit mode -->
                                    <div class="w-full space-y-2">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-[10px] font-medium text-gray-500 mb-1">Цена (₽)</label>
                                                <input type="number" wire:model="editPartSellingPrice"
                                                    class="input text-sm p-2 w-full border border-gray-300 rounded" />
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-medium text-gray-500 mb-1">Гарантия
                                                    (дней)</label>
                                                <input type="number" wire:model="editPartWarrantyDays"
                                                    class="input text-sm p-2 w-full border border-gray-300 rounded" />
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button wire:click="savePartEdit"
                                                class="text-xs font-medium text-green-600 hover:text-green-800">Сохранить</button>
                                            <button wire:click="$set('editingPartId', null)"
                                                class="text-xs font-medium text-gray-500 hover:text-gray-700">Отмена</button>
                                        </div>
                                    </div>
                                @else
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 uppercase">
                                            {{ $usedPart->inventoryItem->product->name ?? 'Неизвестная деталь' }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">S/N:
                                            {{ $usedPart->inventoryItem->serial_number ?? 'БЕЗ НОМЕРА' }} • Гарантия:
                                            {{ $usedPart->warranty_days }} дн.
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ number_format($usedPart->selling_price, 0, '.', ' ') }} ₽
                                        </p>
                                        <button wire:click="editPart('{{ $usedPart->id }}')"
                                            class="text-xs text-blue-600 hover:text-blue-800 p-1">
                                            <span class="material-symbols-outlined text-sm">edit</span>
                                        </button>
                                        <button wire:click="removePart('{{ $usedPart->id }}')"
                                            wire:confirm="Удалить эту запчасть из заявки?"
                                            class="text-xs text-red-600 hover:text-red-800 p-1">
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Checklist -->
            @if($this->currentChecklist)
                <div class="card p-5">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase mb-4">Чек-лист:
                        {{ $this->currentChecklist->name }}
                    </h3>
                    <form wire:submit="saveChecklist" class="space-y-2">
                        @foreach($this->currentChecklist->items as $item)
                            <label
                                class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border border-gray-100 hover:bg-gray-100 cursor-pointer">
                                <input type="checkbox" wire:model="checklistAnswers.{{ $item->id }}"
                                    class="w-4 h-4 text-gray-900 rounded border-gray-300 focus:ring-gray-900" />
                                <span class="text-sm font-medium text-gray-900">{{ $item->question }}</span>
                            </label>
                        @endforeach
                        <button type="submit" class="btn-primary w-full justify-center mt-3">Сохранить проверку</button>
                    </form>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-3 space-y-4">
            <!-- Customer -->
            <div class="card p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase mb-4">Клиент</h3>
                <div class="flex items-center space-x-3 mb-3">
                    <div
                        class="w-10 h-10 rounded-full bg-gray-900 flex items-center justify-center text-white text-sm font-medium">
                        {{ mb_substr($ticket->customer->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 uppercase">{{ $ticket->customer->name }}</p>
                        <p class="text-xs text-gray-500">{{ $ticket->customer->phone }}</p>
                    </div>
                </div>
                <a href="{{ route('customers.index') }}" wire:navigate
                    class="text-xs font-medium text-blue-600 hover:underline">Профиль CRM →</a>
            </div>

            <!-- Comments -->
            <div class="card flex flex-col" style="height: 400px;">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase">Комментарии</h3>
                </div>

                <div class="flex-1 p-4 overflow-y-auto space-y-3 bg-gray-50">
                    @forelse($ticket->comments as $comment)
                        <div class="bg-white p-3 rounded-lg border border-gray-100">
                            <div class="flex justify-between items-center mb-1">
                                <span
                                    class="text-[10px] font-medium text-gray-900 uppercase">{{ $comment->user->name ?? 'Система' }}</span>
                                <span class="text-[10px] text-gray-400">{{ $comment->created_at->format('d.m H:i') }}</span>
                            </div>
                            <p class="text-sm text-gray-900">{{ $comment->content }}</p>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500 text-center py-8">Пока нет комментариев</p>
                    @endforelse
                </div>

                <div class="p-4 border-t border-gray-200">
                    <form wire:submit="addComment" class="space-y-2">
                        <textarea wire:model="newComment" rows="2"
                            class="input resize-none p-2 w-full border border-gray-300 rounded-lg"
                            placeholder="Комментарий..."></textarea>
                        <button type="submit" class="btn-primary w-full justify-center text-xs">
                            <span wire:loading.remove wire:target="addComment">Отправить</span>
                            <span wire:loading wire:target="addComment">Отправка...</span>
                        </button>
                    </form>
                </div>
            </div>
            <!-- Стоимость работы -->
            <div class="card p-5">
                <h3 class="text-xs font-semibold text-gray-500 uppercase mb-3">Стоимость работы мастера</h3>
                <form wire:submit="updateLaborCost" class="flex space-x-2">
                    <input type="number" wire:model="laborCost" class="input flex-1" placeholder="Сумма в рублях">
                    <button type="submit" class="btn-primary">Сохранить</button>
                </form>
            </div>
            <!-- Total -->
            <div class="card p-5 border-b-4 border-b-gray-900">
                <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">Общая стоимость</h3>
                <p class="text-2xl font-semibold text-gray-900">
                    {{ number_format($ticket->estimated_cost, 0, '.', ' ') }} ₽
                </p>
            </div>
        </div>
    </div>
</div>