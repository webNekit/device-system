<div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="flex items-center space-x-4 mb-8">
        <a href="{{ route('users.index') }}" wire:navigate class="p-2 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 shadow-sm">
            <span class="material-symbols-outlined text-gray-500 text-sm">arrow_back</span>
        </a>
        <div>
            <h1 class="text-2xl font-black italic uppercase tracking-tighter text-gray-900">{{ $user->name }}</h1>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                {{ $user->roles->first()?->name ?? 'Сотрудник' }} • {{ $user->branch->name ?? 'Без филиала' }}
            </p>
        </div>
    </div>

    <!-- Фильтр -->
    <div class="flex justify-end mb-4">
        <select wire:model.live="period" class="input w-48">
            <option value="this_month">Текущий месяц</option>
            <option value="last_month">Прошлый месяц</option>
            <option value="all_time">За всё время</option>
        </select>
    </div>

    @php $data = $this->stats; @endphp

        <!-- Зарплата и Выручка -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="card p-6 bg-gradient-to-br from-indigo-600 to-purple-700 text-white border-0 shadow-lg">
            <div class="text-[10px] font-bold uppercase tracking-widest text-indigo-200 mb-1">Зарплата мастера ({{ $user->commission_percent }}% от маржи)</div>
            <div class="text-4xl text-gray-700 font-black italic">{{ number_format($data['technicianEarnings'], 0, '.', ' ') }} ₽</div>
            <div class="mt-4 text-xs text-indigo-600 flex items-center">
                <span class="material-symbols-outlined text-sm mr-1">check_circle</span>
                Закрыто заявок: <b class="ml-1 text-gray-900">{{ $data['completedCount'] }} шт.</b>
            </div>
        </div>

        <div class="card p-6 bg-gradient-to-br from-gray-900 to-black text-white border-0 shadow-lg">
            <div class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Прибыль компании с мастера</div>

            <!-- Динамический цвет и знак -->
            <div class="text-4xl font-black italic {{ $data['companyProfit'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                {{ $data['companyProfit'] > 0 ? '+' : '' }}{{ number_format($data['companyProfit'], 0, '.', ' ') }} ₽
            </div>

            <div class="mt-4 text-xs text-gray-400 flex justify-between">
                <span>Выручка: {{ number_format($data['totalRevenue'], 0, '.', ' ') }} ₽</span>
                <span>Запчасти: {{ number_format($data['totalPartsCost'], 0, '.', ' ') }} ₽</span>
            </div>
        </div>
    </div>
</div>
