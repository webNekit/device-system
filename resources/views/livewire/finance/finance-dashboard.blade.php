<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Финансы (P&L)</h1>
            <p class="text-sm text-gray-500 mt-0.5">Доходы, расходы и маржа</p>
        </div>
        <div class="flex items-center space-x-3">
            <select wire:model.live="period" class="input px-4  min-h-12 border border-gray-300 rounded-lg w-auto">
                <option value="this_month">Этот месяц</option>
                <option value="last_month">Прошлый месяц</option>
                <option value="all_time">За все время</option>
            </select>
            @if(auth()->user()->hasRole('Admin'))
                <select wire:model.live="branch_id" class="input px-4 min-h-12 border border-gray-300 rounded-lg w-auto">
                    <option value="">Все филиалы</option>
                    @foreach($this->branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif
            <button wire:click="$set('showForm', true)"
                class="btn-primary px-5 py-3 flex items-center bg-gray-900 text-gray-50 rounded-lg cursor-pointer">
                Провести операцию
            </button>
        </div>
    </div>

    @if($successMessage)
        <div class="p-3 bg-green-50 text-green-700 rounded-lg text-sm flex items-center gap-2">
            <span class="material-symbols-outlined fill text-sm">check_circle</span>
            <span>{{ $successMessage }}</span>
            <button wire:click="$set('successMessage', '')" class="ml-auto">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
    @endif

    <!-- Modal -->
    @if($showForm)
        <div class="fixed inset-0 modal-backdrop flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl max-w-2xl w-full p-6 shadow-xl border border-gray-200">
                <div class="flex justify-between items-start mb-5">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center">
                            <span class="material-symbols-outlined text-gray-600">payments</span>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Новая транзакция</h3>
                            <p class="text-xs text-gray-500">Проведение операции</p>
                        </div>
                    </div>
                    <button wire:click="$set('showForm', false)" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <span class="material-symbols-outlined text-gray-400">close</span>
                    </button>
                </div>

                <form wire:submit="saveTransaction" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        @if(auth()->user()->hasRole('Admin'))
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1.5">Филиал</label>
                                <select wire:model="form_branch_id"
                                    class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg">
                                    <option value="">-- Выберите филиал --</option>
                                    @foreach($this->branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Тип операции</label>
                            <select wire:model.live="type"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg">
                                <option value="expense">Расход</option>
                                <option value="income">Доход</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Категория</label>
                            <select wire:model="category"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg">
                                @if($type === 'expense')
                                    <option value="rent">Аренда</option>
                                    <option value="salary">Зарплата</option>
                                    <option value="marketing">Реклама</option>
                                    <option value="tools">Инструменты</option>
                                    <option value="other">Прочее</option>
                                @else
                                    <option value="service">Ремонт</option>
                                    <option value="sale">Продажа</option>
                                    <option value="other">Прочее</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Сумма (₽)</label>
                            <input px-4 w-full min-h-12 border border-gray-300 rounded-lg type="number" step="0.01"
                                wire:model="amount"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg text-lg font-semibold" />
                            @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1.5">Способ оплаты</label>
                            <select wire:model="payment_method"
                                class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg">
                                <option value="cash">Наличные</option>
                                <option value="card">Карта</option>
                                <option value="transfer">Перевод</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1.5">Описание</label>
                        <input px-4 w-full min-h-12 border border-gray-300 rounded-lg type="text" wire:model="description"
                            class="input px-4 w-full min-h-12 border border-gray-300 rounded-lg"
                            placeholder="Назначение платежа" />
                        @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="$set('showForm', false)"
                            class="btn-secondary cursor-pointer">Отмена</button>
                        <button type="submit"
                            class="btn-primary px-5 py-3 flex items-center bg-gray-900 text-gray-50 rounded-lg cursor-pointer">Провести</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Stats Cards -->
    @php $data = $this->analytics; @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500">Выручка</span>
                <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-600">trending_up</span>
                </div>
            </div>
            <p class="text-3xl font-semibold text-gray-900">{{ number_format($data['revenue'], 0, '.', ' ') }} ₽</p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500">Себестоимость</span>
                <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                    <span class="material-symbols-outlined text-amber-600">inventory</span>
                </div>
            </div>
            <p class="text-3xl font-semibold text-gray-900">{{ number_format($data['cogs'], 0, '.', ' ') }} ₽</p>
            <p class="text-xs text-gray-500 mt-1">Валовая прибыль:
                {{ number_format($data['grossProfit'], 0, '.', ' ') }} ₽
            </p>
        </div>

        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500">Чистая прибыль</span>
                <span class="px-2 py-1 bg-white/10 rounded-full text-xs font-medium text-gray-900">Маржа:
                    {{ number_format($data['margin'], 1) }}%</span>
            </div>
            <p class="text-3xl font-semibold text-gray-900">{{ number_format($data['netProfit'], 0, '.', ' ') }} ₽</p>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="table-container">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center">
                    <span class="material-symbols-outlined text-gray-600 text-sm">receipt_long</span>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Последние операции</h3>
                    <p class="text-xs text-gray-500">История транзакций</p>
                </div>
            </div>
        </div>

        <table class="w-full">
            <thead>
                <tr class="table-header">
                    <th class="text-left pl-5">Дата</th>
                    <th class="text-left">Тип / Категория</th>
                    <th class="text-left">Описание</th>
                    <th class="text-right pr-5">Сумма</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($this->recentTransactions as $tx)
                    <tr class="table-row">
                        <td class="px-5 py-4 text-sm text-gray-500">{{ $tx->created_at->format('d.m.Y H:i') }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center space-x-2">
                                @if($tx->type === 'income')
                                    <span class="badge badge-success">Доход</span>
                                @else
                                    <span class="badge badge-danger">Расход</span>
                                @endif
                                <span class="text-xs text-gray-500 uppercase">{{ $tx->category }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-900">
                            {{ $tx->description }}
                            @if($tx->ticket)
                                <a href="{{ route('tickets.show', $tx->ticket->ulid) }}" wire:navigate
                                    class="text-xs text-blue-600 hover:underline block mt-1">
                                    Заявка: {{ $tx->ticket->device_model }}
                                </a>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <p
                                class="text-lg font-semibold {{ $tx->type === 'income' ? 'text-green-600' : 'text-gray-900' }}">
                                {{ $tx->type === 'income' ? '+' : '-' }}{{ number_format($tx->amount, 0, '.', ' ') }} ₽
                            </p>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-gray-400 text-sm">receipt_long</span>
                            </div>
                            <p class="text-sm text-gray-500">Операций нет</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>