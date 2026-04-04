<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-0.5">Обзор ключевых показателей</p>
        </div>
        <button class="btn-secondary">
            <span class="material-symbols-outlined text-sm">download</span>
            Экспорт
        </button>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Clients -->
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500">Всего клиентов</span>
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-600">people</span>
                </div>
            </div>
            <p class="text-3xl font-semibold text-gray-900">{{ number_format($stats['total_customers']) }}</p>
        </div>

        <!-- Employees -->
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500">Сотрудников</span>
                <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-600">badge</span>
                </div>
            </div>
            <p class="text-3xl font-semibold text-gray-900">{{ number_format($stats['total_users']) }}</p>
        </div>

        <!-- Branches -->
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500">Филиалов</span>
                <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                    <span class="material-symbols-outlined text-amber-600">business</span>
                </div>
            </div>
            <p class="text-3xl font-semibold text-gray-900">{{ number_format($stats['total_branches']) }}</p>
        </div>

        <!-- Tickets -->
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-500">Всего заявок</span>
                <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center">
                    <span class="material-symbols-outlined text-purple-600">confirmation_number</span>
                </div>
            </div>
            <p class="text-3xl font-semibold text-gray-900">{{ number_format($stats['total_tickets']) }}</p>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- In Work -->
        <div class="card p-5 border-l-4 border-l-blue-500">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">В работе</span>
                <span class="material-symbols-outlined text-gray-400 text-sm">schedule</span>
            </div>
            <p class="text-3xl font-semibold text-gray-900">{{ number_format($stats['tickets_in_work']) }}</p>
            <p class="text-xs text-gray-500 mt-1">Активные заявки</p>
        </div>

        <!-- Completed -->
        <div class="card p-5 border-l-4 border-l-green-500">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">Выполнено</span>
                <span class="material-symbols-outlined text-gray-400 text-sm">check_circle</span>
            </div>
            <p class="text-3xl font-semibold text-gray-900">{{ number_format($stats['tickets_completed']) }}</p>
            <p class="text-xs text-gray-500 mt-1">Завершенные заявки</p>
        </div>

        <!-- Finance Today -->
        <div class="card p-5 border-l-4 border-l-gray-900">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">Финансы сегодня</span>
                <span class="material-symbols-outlined text-gray-400 text-sm">account_balance_wallet</span>
            </div>
            <div class="flex items-baseline space-x-2">
                <p class="text-2xl font-semibold text-green-600">
                    +{{ number_format($financeStats['today_income'], 0, '.', ' ') }} ₽</p>
            </div>
            <p class="text-xs text-red-500 mt-1">−{{ number_format($financeStats['today_expense'], 0, '.', ' ') }} ₽
                расход</p>
        </div>
    </div>

    <!-- Pipeline Stats -->
    <div class="card">
        <div class="p-5 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900">Заявки по этапам</h3>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach($pipelineStats as $stage)
                    <div class="text-center p-4 bg-gray-50 rounded-xl">
                        <p class="text-2xl font-semibold text-gray-900">{{ $stage['count'] }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $stage['name'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Tickets & Top Customers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Recent Tickets -->
        <div class="card">
            <div class="p-5 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Последние заявки</h3>
                <a href="{{ route('tickets.index') }}" wire:navigate
                    class="text-xs font-medium text-gray-600 hover:text-gray-900 flex items-center gap-1">
                    Все заявки
                    <span class="material-symbols-outlined text-xs">arrow_forward</span>
                </a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentTickets as $ticket)
                    <a href="{{ route('tickets.show', $ticket->ulid) }}" wire:navigate
                        class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                                <span class="material-symbols-outlined text-blue-600 text-sm">confirmation_number</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $ticket->device_brand }}
                                    {{ $ticket->device_model }}</p>
                                <p class="text-xs text-gray-500">{{ $ticket->customer->name }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-gray">{{ $ticket->currentStage->name }}</span>
                            <p class="text-xs text-gray-400 mt-1">{{ $ticket->created_at->diffForHumans() }}</p>
                        </div>
                    </a>
                @empty
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-gray-400 text-sm">inbox</span>
                        </div>
                        <p class="text-sm text-gray-500">Заявок пока нет</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Top Customers -->
        <div class="card">
            <div class="p-5 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Топ клиентов</h3>
                <a href="{{ route('customers.index') }}" wire:navigate
                    class="text-xs font-medium text-gray-600 hover:text-gray-900 flex items-center gap-1">
                    Все клиенты
                    <span class="material-symbols-outlined text-xs">arrow_forward</span>
                </a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($topCustomers as $customer)
                    <div class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center">
                                <span class="material-symbols-outlined text-green-600 text-sm">person</span>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $customer->name }}</p>
                                <p class="text-xs text-gray-500">{{ $customer->phone }}</p>
                            </div>
                        </div>
                        <span class="badge badge-success">{{ $customer->tickets_count }} заяв.</span>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                            <span class="material-symbols-outlined text-gray-400 text-sm">person_off</span>
                        </div>
                        <p class="text-sm text-gray-500">Клиентов пока нет</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Device & Priority Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Device Types -->
        <div class="card">
            <div class="p-5 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">По типам устройств</h3>
            </div>
            <div class="p-5 space-y-4">
                @forelse($deviceStats as $device)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                <span class="material-symbols-outlined text-blue-600 text-sm">devices</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $device->device_type ?? 'Не указан' }}</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-32 bg-gray-100 rounded-full h-1.5">
                                <div class="bg-blue-500 h-1.5 rounded-full"
                                    style="width: {{ $stats['total_tickets'] > 0 ? ($device->count / $stats['total_tickets'] * 100) : 0 }}%">
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 w-8 text-right">{{ $device->count }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <p class="text-sm text-gray-500">Нет данных</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Priority -->
        <div class="card">
            <div class="p-5 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">По приоритетам</h3>
            </div>
            <div class="p-5 space-y-4">
                @forelse($priorityStats as $priority)
                    @php
                        $badgeClass = match ($priority->priority) {
                            'urgent' => 'badge-danger',
                            'high' => 'badge-warning',
                            'low' => 'badge-info',
                            default => 'badge-gray'
                        };
                        $label = match ($priority->priority) {
                            'urgent' => 'Срочный',
                            'high' => 'Высокий',
                            'low' => 'Низкий',
                            default => 'Нормальный'
                        };
                    @endphp
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span
                                class="w-2 h-2 rounded-full {{ $priority->priority === 'urgent' ? 'bg-red-500' : ($priority->priority === 'high' ? 'bg-amber-500' : 'bg-blue-500') }}"></span>
                            <span class="text-sm font-medium text-gray-900">{{ $label }}</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="badge {{ $badgeClass }}">{{ $priority->count }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <p class="text-sm text-gray-500">Нет данных</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="card p-5 bg-green-50 border-green-100">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-600">trending_up</span>
                </div>
                <span class="text-sm font-medium text-green-800">Всего доходов</span>
            </div>
            <p class="text-3xl font-semibold text-green-900">
                {{ number_format($financeStats['total_income'], 0, '.', ' ') }} ₽</p>
        </div>

        <div class="card p-5 bg-red-50 border-red-100">
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-white flex items-center justify-center">
                    <span class="material-symbols-outlined text-red-600">trending_down</span>
                </div>
                <span class="text-sm font-medium text-red-800">Всего расходов</span>
            </div>
            <p class="text-3xl font-semibold text-red-900">
                {{ number_format($financeStats['total_expense'], 0, '.', ' ') }} ₽</p>
        </div>
    </div>
</div>