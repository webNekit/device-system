<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Чек-листы</h1>
            <p class="text-sm text-gray-500 mt-0.5">Шаблоны для этапов ремонта</p>
        </div>
        <button wire:click="openCreate"
            class="btn-primary px-5 py-3 flex items-center bg-gray-900 text-gray-50 rounded-lg cursor-pointer">
            Создать чек-лист
        </button>
    </div>

    <!-- Modal -->
    @if($showCreateForm || $showEditForm || $showItemsForm)
        <!-- Фон модалки -->
        <div class="fixed inset-0 modal-backdrop bg-gray-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">

            <!-- Само окно: Добавлено overflow-hidden и увеличена высота до 90vh -->
            <div
                class="bg-white rounded-xl max-w-4xl w-full max-h-[90vh] shadow-xl border border-gray-200 flex flex-col overflow-hidden">

                @if($showItemsForm)
                    <!-- ================= ITEMS MANAGER ================= -->

                    <!-- Header (Фиксированный) -->
                    <div class="flex justify-between items-center p-6 border-b border-gray-100 flex-shrink-0 bg-white">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center">
                                <span class="material-symbols-outlined text-gray-600">fact_check</span>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Пункты чек-листа</h3>
                                <p class="text-xs text-gray-500">Редактирование</p>
                            </div>
                        </div>
                        <button wire:click="cancel" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                            <span class="material-symbols-outlined text-gray-400">close</span>
                        </button>
                    </div>

                    <!-- Body (Скроллируемый) -->
                    <div class="flex-1 overflow-y-auto p-6 bg-gray-50/30">
                        <div class="space-y-2 mb-6">
                            @foreach($this->items as $index => $item)
                                <div class="flex items-start space-x-3 p-3 bg-white rounded-lg border border-gray-200 shadow-sm">
                                    <span class="material-symbols-outlined text-gray-400 text-sm mt-0.5">drag_indicator</span>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $item['question'] }}</p>
                                        <div class="flex items-center space-x-2 mt-1">
                                            <span
                                                class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-[10px] font-medium uppercase">{{ $item['field_type'] }}</span>
                                        </div>
                                    </div>
                                    <button wire:click="removeItem({{ $index }})"
                                        class="p-2 hover:bg-red-50 rounded-lg transition-colors text-red-600">
                                        <span class="material-symbols-outlined text-sm">delete</span>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <!-- Форма добавления пункта (Скроллится вместе со списком) -->
                        <div class="bg-gray-100/50 rounded-xl p-5 border border-gray-200">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Добавить пункт</h4>
                            <div class="grid grid-cols-4 gap-3 mb-3">
                                <input type="text" wire:model="newItemQuestion" placeholder="Вопрос"
                                    class="col-span-2 input px-4 w-full min-h-12 border border-gray-300 rounded-lg bg-white" />
                                <select wire:model.live="newItemType"
                                    class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg bg-white">
                                    <option value="checkbox">Чекбокс</option>
                                    <option value="text">Текст</option>
                                    <option value="select">Выбор</option>
                                </select>
                                <button wire:click="addNewItem"
                                    class="btn-primary justify-center px-5 py-3 flex items-center bg-gray-900 text-gray-50 rounded-lg cursor-pointer">
                                    Добавить
                                </button>
                            </div>
                            @if($newItemType === 'select')
                                <input type="text" wire:model="newItemOptions" placeholder="Варианты через запятую"
                                    class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg bg-white" />
                            @endif
                        </div>
                    </div>

                    <!-- Footer (Фиксированный) -->
                    <div class="flex justify-end space-x-3 p-5 border-t border-gray-200 flex-shrink-0 bg-gray-50">
                        <button wire:click="cancel" class="btn-secondary bg-white">Отмена</button>
                        <button wire:click="saveItems"
                            class="btn-primary px-5 py-3 flex items-center bg-gray-900 text-gray-50 rounded-lg cursor-pointer">Сохранить
                            пункты</button>
                    </div>

                @else
                    <!-- ================= CREATE/EDIT FORM ================= -->

                    <!-- Header (Фиксированный) -->
                    <div class="flex justify-between items-center p-6 border-b border-gray-100 flex-shrink-0 bg-white">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center">
                                <span
                                    class="material-symbols-outlined text-gray-600">{{ $showEditForm ? 'edit' : 'add' }}</span>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">
                                    {{ $showEditForm ? 'Редактирование' : 'Создание' }} чек-листа
                                </h3>
                                <p class="text-xs text-gray-500">Настройка шаблона</p>
                            </div>
                        </div>
                        <button wire:click="cancel" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                            <span class="material-symbols-outlined text-gray-400">close</span>
                        </button>
                    </div>

                    <!-- Body (Скроллируемый) -->
                    <div class="flex-1 overflow-y-auto p-6 bg-gray-50/30">
                        <div class="space-y-5">
                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Название</label>
                                <input type="text" wire:model="templateName"
                                    class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg bg-white" />
                                @error('templateName') <p class="text-red-500 text-xs mt-1 font-semibold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-5">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Тип</label>
                                    <select wire:model="templateType"
                                        class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg bg-white">
                                        <option value="diagnostics">Диагностика</option>
                                        <option value="qc">Контроль качества</option>
                                        <option value="intake">Приемка</option>
                                        <option value="output">Выдача</option>
                                    </select>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Этап</label>
                                    <select wire:model="templateStageId"
                                        class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg bg-white">
                                        <option value="">-- Не выбрано --</option>
                                        @foreach($stages as $stage)
                                            <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Тип
                                    устройства</label>
                                <select wire:model="templateDeviceTypeId"
                                    class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg bg-white">
                                    <option value="">-- Не выбрано --</option>
                                    @foreach($deviceTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1.5">Описание</label>
                                <textarea wire:model="templateDescription" rows="3"
                                    class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg bg-white resize-none"></textarea>
                            </div>

                            <div class="flex items-center space-x-3 p-4 bg-white border border-gray-200 rounded-xl shadow-sm">
                                <input type="checkbox" wire:model="templateIsActive" id="isActive"
                                    class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-600" />
                                <label for="isActive" class="text-sm font-semibold text-gray-700 cursor-pointer">Активный
                                    чек-лист</label>
                            </div>
                        </div>
                    </div>

                    <!-- Footer (Фиксированный) -->
                    <div class="flex justify-end space-x-3 p-5 border-t border-gray-200 flex-shrink-0 bg-gray-50">
                        <button wire:click="cancel" class="btn-secondary bg-white">Отмена</button>
                        <button wire:click="saveTemplate"
                            class="btn-primary px-5 py-3 flex items-center bg-gray-900 text-gray-50 rounded-lg cursor-pointer">Сохранить</button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- List -->
    <div class="table-container">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center space-x-3 bg-gray-50 rounded-t-xl">
            <div class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center shadow-sm">
                <span class="material-symbols-outlined text-gray-600 text-sm">fact_check</span>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Список чек-листов</h3>
                <p class="text-xs text-gray-500">Доступные шаблоны</p>
            </div>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($templates as $template)
                <div class="p-5 hover:bg-gray-50 transition-colors">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <h4 class="text-sm font-black text-gray-900 uppercase">{{ $template->name }}</h4>
                                <span
                                    class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-[10px] font-bold uppercase tracking-widest border border-blue-200">{{ $template->type }}</span>
                                @if(!$template->is_active)
                                    <span
                                        class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded text-[10px] font-bold uppercase tracking-widest border border-gray-200">Не
                                        активный</span>
                                @endif
                            </div>
                            @if($template->description)
                                <p class="text-xs text-gray-500 mb-3">{{ $template->description }}</p>
                            @endif
                            <div class="flex items-center space-x-4 text-xs font-medium text-gray-500">
                                @if($template->stage)
                                    <span class="flex items-center space-x-1">
                                        <span class="material-symbols-outlined text-[16px] text-indigo-400">flag</span>
                                        <span>{{ $template->stage->name }}</span>
                                    </span>
                                @endif
                                @if($template->deviceType)
                                    <span class="flex items-center space-x-1">
                                        <span class="material-symbols-outlined text-[16px] text-indigo-400">devices</span>
                                        <span>{{ $template->deviceType->name }}</span>
                                    </span>
                                @endif
                                <span class="flex items-center space-x-1">
                                    <span class="material-symbols-outlined text-[16px] text-indigo-400">list</span>
                                    <span>Пунктов: <b class="text-gray-900">{{ $template->items->count() }}</b></span>
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button wire:click="openItemsManager({{ $template->id }})"
                                class="px-4 py-2 text-[11px] font-bold uppercase tracking-widest text-indigo-600 bg-indigo-50 border border-indigo-100 hover:bg-indigo-100 rounded-lg transition-colors">
                                Пункты
                            </button>
                            <button wire:click="openEdit({{ $template->id }})"
                                class="px-4 py-2 text-[11px] font-bold uppercase tracking-widest text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg transition-colors">
                                Изменить
                            </button>
                            <button wire:click="deleteTemplate({{ $template->id }})"
                                class="px-4 py-2 text-[11px] font-bold uppercase tracking-widest text-red-600 bg-red-50 border border-red-100 hover:bg-red-100 rounded-lg transition-colors">
                                Удалить
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <div
                        class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-50 border border-gray-200 flex items-center justify-center">
                        <span class="material-symbols-outlined text-gray-400 text-2xl">fact_check</span>
                    </div>
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-widest">Чек-листы еще не созданы</p>
                </div>
            @endforelse
        </div>
    </div>
</div>