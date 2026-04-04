<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Клиенты</h1>
            <p class="text-sm text-gray-500 mt-0.5">База клиентов</p>
        </div>
        <button wire:click="openCreate"
            class="btn-primary px-5 py-3 flex items-center bg-gray-900 text-gray-50 rounded-lg cursor-pointer">
            Добавить клиента
        </button>
    </div>

    <!-- Modal -->
    @if($showCreateForm)
        <div class="fixed inset-0 modal-backdrop flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl max-w-2xl w-full p-6 shadow-xl border border-gray-200">
                <div class="flex justify-between items-start mb-5">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center">
                            <span class="material-symbols-outlined text-gray-600">person_add</span>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Новый клиент</h3>
                            <p class="text-xs text-gray-500">Заполните данные</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <span class="material-symbols-outlined text-gray-400">close</span>
                    </button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Тип</label>
                            <select wire:model.live="type"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg">
                                <option value="individual">Физическое лицо</option>
                                <option value="legal">Юридическое лицо</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">ФИО / Название</label>
                            <input px-4 w-full min-h-12 border border-gray-300 rounded-lg type="text" wire:model="name"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg"
                                placeholder="Иванов И.И." />
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Телефон</label>
                            <input px-4 w-full min-h-12 border border-gray-300 rounded-lg type="tel" wire:model="phone"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg"
                                placeholder="+7 (999) 000-00-00" />
                            @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Email</label>
                            <input px-4 w-full min-h-12 border border-gray-300 rounded-lg type="email" wire:model="email"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg"
                                placeholder="email@example.com" />
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @if($type === 'legal')
                        <div class="p-4 bg-gray-50 rounded-lg space-y-3">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">ИНН</label>
                                    <input px-4 w-full min-h-12 border border-gray-300 rounded-lg type="text" wire:model="inn"
                                        class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg"
                                        placeholder="1234567890" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1.5">КПП</label>
                                    <input px-4 w-full min-h-12 border border-gray-300 rounded-lg type="text" wire:model="kpp"
                                        class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg"
                                        placeholder="123456789" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Юр. адрес</label>
                                <input px-4 w-full min-h-12 border border-gray-300 rounded-lg type="text"
                                    wire:model="legal_address"
                                    class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg"
                                    placeholder="г. Москва, ул..." />
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="closeModal" class="btn-secondary cursor-pointer">Отмена</button>
                        <button type="submit"
                            class="btn-primary px-5 py-3 flex items-center bg-gray-900 text-gray-50 rounded-lg cursor-pointer">
                            <span wire:loading.remove wire:target="save">Создать</span>
                            <span wire:loading wire:target="save">Обработка...</span>
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
                    <span class="material-symbols-outlined text-gray-600 text-sm">people</span>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Клиенты</h3>
                    <p class="text-xs text-gray-500">{{ $this->customers->count() }} всего</p>
                </div>
            </div>
            <div class="relative">
                <span
                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">search</span>
                <input px-4 w-full min-h-12 border border-gray-300 rounded-lg type="text"
                    wire:model.live.debounce.300ms="search" placeholder="Поиск..."
                    class="pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 w-64" />
            </div>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($this->customers as $customer)
                <div class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors group">
                    <div class="flex items-center space-x-4">
                        <div
                            class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-600 text-sm font-medium">
                            {{ mb_substr($customer->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <p class="text-sm font-medium text-gray-900">{{ $customer->name }}</p>
                                @if($customer->type === 'legal')
                                    <span class="badge badge-gray">Юр. лицо</span>
                                @else
                                    <span class="badge badge-info">Физ. лицо</span>
                                @endif
                            </div>
                            <div class="flex items-center space-x-3 mt-1 text-xs text-gray-500">
                                <span>{{ $customer->phone }}</span>
                                @if($customer->email)
                                    <span>{{ $customer->email }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <a href="{{ route('customers.show', $customer->id) }}" wire:navigate
                            class="px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50 border border-transparent hover:border-indigo-100 rounded-lg transition-colors">
                            Профиль и история
                        </a>
                        <button wire:click="delete({{ $customer->id }})" wire:confirm="Вы уверены?"
                            class="px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            Удалить
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-gray-400 text-sm">people</span>
                    </div>
                    <p class="text-sm text-gray-500">Клиентов пока нет</p>
                </div>
            @endforelse
        </div>
    </div>
</div>