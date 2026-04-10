<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Сотрудники</h1>
            <p class="text-sm text-gray-500 mt-0.5">Управление персоналом и доступом</p>
        </div>
        <button wire:click="openCreate" class="btn-primary">
            <span class="material-symbols-outlined text-sm">person_add</span>
            Добавить сотрудника
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
                                class="material-symbols-outlined text-gray-600">{{ $showEditForm ? 'edit' : 'person_add' }}</span>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">
                                {{ $showEditForm ? 'Редактирование' : 'Новый сотрудник' }}
                            </h3>
                            <p class="text-xs text-gray-500">Заполните данные</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <span class="material-symbols-outlined text-gray-400">close</span>
                    </button>
                </div>

                @if($successMessage)
                    <div class="mb-4 p-3 bg-green-50 text-green-700 rounded-lg text-sm flex items-center gap-2">
                        <span class="material-symbols-outlined fill text-sm">check_circle</span>
                        <span>{{ $successMessage }}</span>
                    </div>
                @endif

                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">ФИО</label>
                            <input type="text" wire:model="name"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg"
                                placeholder="Иванов Иван" />
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Email</label>
                            <input type="email" wire:model="email"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg"
                                placeholder="email@company.com" />
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Пароль</label>
                            <input type="password" wire:model="password"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg"
                                placeholder="{{ $showEditForm ? 'Оставьте пустым' : 'Придумайте пароль' }}" />
                            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Филиал</label>
                            <select wire:model="branch_id"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg">
                                <option value="">-- Выберите филиал --</option>
                                @foreach($this->branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Процент от прибыли (%)</label>
                            <input type="number" wire:model="commission_percent" class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg" placeholder="Например: 30" />
                            @error('commission_percent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-2">Роль</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            @foreach($this->roles as $role)
                                <label
                                    class="flex items-center p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">
                                    <input type="radio" wire:model="role_name" value="{{ $role->name }}"
                                        class="w-4 h-4 text-gray-900 focus:ring-gray-900 border-gray-300">
                                    <span
                                        class="ml-2 text-sm font-medium text-gray-700">{{ $this->getRoleNameInRussian($role->name) }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('role_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="closeModal" class="btn-secondary cursor-pointer">
                            Отмена
                        </button>
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
                    <span class="material-symbols-outlined text-gray-600 text-sm">badge</span>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Сотрудники</h3>
                    <p class="text-xs text-gray-500">{{ $this->users->count() }} всего</p>
                </div>
            </div>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($this->users as $user)
                <div class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors group">
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <div
                                class="w-10 h-10 rounded-full bg-gray-900 flex items-center justify-center text-white text-sm font-medium">
                                {{ mb_substr($user->name, 0, 1) }}
                            </div>
                            <span
                                class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-white rounded-full"></span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                            <div class="flex items-center space-x-2 mt-1">
                                @foreach($user->roles as $role)
                                    <span class="badge badge-gray">{{ $this->getRoleNameInRussian($role->name) }}</span>
                                @endforeach
                                @if($user->branch)
                                    <span class="text-xs text-gray-400 flex items-center space-x-1">
                                        <span class="material-symbols-outlined text-xs">business</span>
                                        <span>{{ $user->branch->name }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <a href="{{ route('users.show', $user->id) }}" class="px-3 py-1.5 text-xs font-medium text-green-600 hover:bg-green-50 rounded-lg transition-colors">Детали</a>
                        <button wire:click="edit({{ $user->id }})"
                            class="px-3 py-1.5 text-xs font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            Изменить
                        </button>
                        <button wire:click="delete({{ $user->id }})" wire:confirm="Вы уверены?"
                            class="px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            Удалить
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-gray-400 text-sm">badge</span>
                    </div>
                    <p class="text-sm text-gray-500">Сотрудников пока нет</p>
                    <p class="text-xs text-gray-400 mt-1">Добавьте первого сотрудника</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
