<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Филиалы</h1>
            <p class="text-sm text-gray-500 mt-0.5">Управление филиалами компании</p>
        </div>
        <button wire:click="openCreate" class="btn-primary">
            <span class="material-symbols-outlined text-sm">add_business</span>
            Добавить филиал
        </button>
    </div>

    <!-- Modal -->
    @if($showCreateForm || $showEditForm)
        <div class="fixed inset-0 modal-backdrop flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl max-w-2xl w-full p-6 shadow-xl border border-gray-200">
                <div class="flex justify-between items-start mb-5">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center">
                            <span
                                class="material-symbols-outlined text-gray-600">{{ $showEditForm ? 'edit' : 'add_business' }}</span>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">
                                {{ $showEditForm ? 'Редактирование' : 'Новый филиал' }}
                            </h3>
                            <p class="text-xs text-gray-500">Заполните информацию</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <span class="material-symbols-outlined text-gray-400">close</span>
                    </button>
                </div>

                @if($successMessage)
                    <div class="mb-5 p-3 bg-green-50 text-green-700 rounded-lg text-sm flex items-center gap-2">
                        <span class="material-symbols-outlined fill text-sm">check_circle</span>
                        <span>{{ $successMessage }}</span>
                    </div>
                @endif

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Название</label>
                        <input type="text" wire:model="name"
                            class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg"
                            placeholder="СЦ на Ленина" />
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Адрес</label>
                        <div class="relative">
                            <span
                                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">location_on</span>
                            <input type="text" wire:model="address"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg"
                                placeholder="г. Москва, ул. Ленина 1" />
                        </div>
                        @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Часовой пояс</label>
                        <select wire:model="timezone" class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg">
                            <option value="Europe/Moscow">Москва (UTC+3)</option>
                            <option value="Asia/Yekaterinburg">Екатеринбург (UTC+5)</option>
                            <option value="Asia/Vladivostok">Владивосток (UTC+10)</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="closeModal" class="btn-secondary cursor-pointer">Отмена</button>
                        <button type="submit"
                            class="btn-primary px-5 py-3 flex items-center bg-gray-900 text-gray-50 rounded-lg cursor-pointer">
                            <span wire:loading.remove wire:target="save">
                                <span>{{ $showEditForm ? 'Обновить' : 'Создать' }}</span>
                            </span>
                            <span wire:loading wire:target="save">
                                <span class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                                <span>Обработка...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Table -->
    <div class="table-container">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center">
                    <span class="material-symbols-outlined text-gray-600 text-sm">business</span>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Филиалы</h3>
                    <p class="text-xs text-gray-500">{{ $this->branches->count() }} всего</p>
                </div>
            </div>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($this->branches as $branch)
                <div
                    class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors group {{ $editingBranchId === $branch->id ? 'bg-blue-50' : '' }}">
                    <div class="flex items-center space-x-4">
                        <div
                            class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center border border-gray-200">
                            <span class="material-symbols-outlined text-gray-600 text-sm">business</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $branch->name }}</p>
                            <div class="flex items-center space-x-3 mt-1 text-xs text-gray-500">
                                <span class="flex items-center space-x-1">
                                    <span class="material-symbols-outlined text-xs">location_on</span>
                                    <span>{{ $branch->address }}</span>
                                </span>
                                <span class="flex items-center space-x-1">
                                    <span class="material-symbols-outlined text-xs">schedule</span>
                                    <span>{{ $branch->timezone }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button wire:click="edit({{ $branch->id }})"
                            class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            {{ $editingBranchId === $branch->id ? 'Редактируется' : 'Изменить' }}
                        </button>
                        <button wire:click="delete({{ $branch->id }})" wire:confirm="Вы уверены?"
                            class="px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            Удалить
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-gray-400 text-sm">business</span>
                    </div>
                    <p class="text-sm text-gray-500">Филиалов пока нет</p>
                    <p class="text-xs text-gray-400 mt-1">Добавьте первый филиал</p>
                </div>
            @endforelse
        </div>
    </div>
</div>