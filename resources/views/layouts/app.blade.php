<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'ERP System' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@300;400;500;600&display=swap"
        rel="stylesheet" />

    <!-- Tailwind & Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            font-size: 20px;
        }

        .material-symbols-outlined.fill {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        body {
            background-color: #f9fafb;
        }

        /* Sidebar */
        .sidebar {
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
        }

        .sidebar-category {
            @apply px-3 py-3 text-sm font-medium text-gray-900;
        }

        .sidebar-item {
            @apply flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-colors mb-0.5;
        }

        .sidebar-item.active {
            @apply bg-gray-900 text-white;
        }

        .sidebar-item:not(.active) {
            @apply text-gray-600 hover:bg-gray-100;
        }

        .sidebar-item .material-symbols-outlined {
            @apply w-5 h-5 mr-3;
        }

        /* Cards */
        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        /* Buttons */
        .btn-primary {
            @apply bg-gray-900 text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors flex items-center gap-2;
        }

        .btn-secondary {
            @apply bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors;
        }

        /* Inputs */
        .input {
            @apply py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent transition-all px-4 w-full min-h-12 border border-gray-300 rounded-lg;
        }

        /* Tables */
        .table-container {
            @apply bg-white border border-gray-200 rounded-xl overflow-hidden;
        }

        .table-header {
            @apply bg-gray-50 border-b border-gray-200 px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider;
        }

        .table-row {
            @apply border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors;
        }

        /* Badges */
        .badge {
            @apply inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium;
        }

        .badge-success {
            @apply bg-green-50 text-green-700;
        }

        .badge-warning {
            @apply bg-amber-50 text-amber-700;
        }

        .badge-danger {
            @apply bg-red-50 text-red-700;
        }

        .badge-info {
            @apply bg-blue-50 text-blue-700;
        }

        .badge-gray {
            @apply bg-gray-100 text-gray-700;
        }

        /* Modal backdrop */
        .modal-backdrop {
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
        }

        /* Checkbox styling */
        .filter-checkbox {
            @apply w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="antialiased min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        @php
            // Выносим классы стилей в переменные, чтобы не дублировать код и легко менять дизайн
            $baseItemClass = 'flex items-center space-x-3 px-3 py-2 rounded-xl text-sm transition-all duration-200 group outline-none';
            $activeClass = 'bg-white shadow-[0_1px_3px_0_rgba(0,0,0,0.05)] border border-gray-200 text-gray-900 font-semibold';
            $inactiveClass = 'text-gray-600 border border-transparent hover:bg-gray-200/50 hover:text-gray-900 font-medium';
            $iconClass = 'material-symbols-outlined text-[20px] transition-colors';
        @endphp

        <aside
            class="sidebar w-[260px] fixed h-screen left-0 top-0 flex flex-col bg-[#FAFAFA] border-r border-gray-200 z-50 font-sans">

            <!-- Logo & Workspace Selector (В стиле Stripe/Horizon) -->
            <div
                class="px-5 py-6 flex items-center justify-between cursor-pointer hover:bg-gray-100/50 transition-colors">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center shadow-sm">
                        <!-- Иконка логотипа -->
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-sm font-bold text-gray-900 leading-tight tracking-tight">Новые Решения</h1>
                        <p class="text-xs text-gray-500 font-medium leading-tight">ERP Repair Team</p>
                    </div>
                </div>
                <!-- Стрелочки -->
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4">
                    </path>
                </svg>
            </div>
            <!-- Navigation -->
            <!-- Navigation -->
            <nav class="flex-1 px-4 py-3 overflow-y-auto space-y-1 no-scrollbar">

                @hasanyrole(['Admin', 'Branch Manager'])
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" wire:navigate
                   class="{{ $baseItemClass }} {{ request()->routeIs('dashboard') ? $activeClass : $inactiveClass }}">
                    <span class="{{ $iconClass }}">home</span>
                    <span>Главная</span>
                </a>

                <!-- Разделитель -->
                <div class="pt-4 pb-2">
                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider px-3">Управление</div>
                </div>

                <a href="{{ route('branches.index') }}" wire:navigate
                   class="{{ $baseItemClass }} {{ request()->routeIs('branches.*') ? $activeClass : $inactiveClass }}">
                    <span class="{{ $iconClass }}">business</span>
                    <span>Филиалы</span>
                </a>
                <a href="{{ route('users.index') }}" wire:navigate
                   class="{{ $baseItemClass }} {{ request()->routeIs('users.*') ? $activeClass : $inactiveClass }}">
                    <span class="{{ $iconClass }}">people</span>
                    <span>Сотрудники</span>
                </a>
                <a href="{{ route('finance.index') }}" wire:navigate
                   class="{{ $baseItemClass }} {{ request()->routeIs('finance.*') ? $activeClass : $inactiveClass }}">
                    <span class="{{ $iconClass }}">payments</span>
                    <span>Финансы</span>
                </a>

                <!-- Клиенты -->
                <div class="pt-4 pb-2">
                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider px-3">CRM</div>
                </div>
                <a href="{{ route('customers.index') }}" wire:navigate
                   class="{{ $baseItemClass }} {{ request()->routeIs('customers.*') ? $activeClass : $inactiveClass }}">
                    <span class="{{ $iconClass }}">person</span>
                    <span>Клиенты</span>
                </a>

                <!-- Справочники -->
                <div class="pt-4 pb-2">
                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider px-3">Настройки</div>
                </div>
                <a href="{{ route('settings.devices') }}" wire:navigate
                   class="{{ $baseItemClass }} {{ request()->routeIs('settings.devices') ? $activeClass : $inactiveClass }}">
                    <span class="{{ $iconClass }}">devices</span>
                    <span>Устройства</span>
                </a>
                <a href="{{ route('settings.checklists') }}" wire:navigate
                   class="{{ $baseItemClass }} {{ request()->routeIs('settings.checklists') ? $activeClass : $inactiveClass }}">
                    <span class="{{ $iconClass }}">fact_check</span>
                    <span>Чек-листы</span>
                </a>
                @endhasanyrole

                @hasanyrole(['Admin', 'Storekeeper'])
                <!-- Склад -->
                <div class="pt-4 pb-2">
                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider px-3">WMS Склад</div>
                </div>
                <a href="{{ route('inventory.index') }}" wire:navigate
                   class="{{ $baseItemClass }} {{ request()->routeIs('inventory.index') ? $activeClass : $inactiveClass }}">
                    <span class="{{ $iconClass }}">warehouse</span>
                    <span>Склады и ячейки</span>
                </a>
                <a href="{{ route('inventory.products') }}" wire:navigate
                   class="{{ $baseItemClass }} {{ request()->routeIs('inventory.products') ? $activeClass : $inactiveClass }}">
                    <span class="{{ $iconClass }}">inventory_2</span>
                    <span>Номенклатура</span>
                </a>
                @endhasanyrole

                @hasanyrole(['Admin', 'Branch Manager', 'Technician'])
                <!-- Сервис -->
                <div class="pt-4 pb-2">
                    <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider px-3">Сервис</div>
                </div>
                <a href="{{ route('tickets.index') }}" wire:navigate
                   class="{{ $baseItemClass }} {{ request()->routeIs('tickets.*') ? $activeClass : $inactiveClass }}">
                    <span class="{{ $iconClass }}">confirmation_number</span>
                    <span>Заявки на ремонт</span>
                </a>
                @endhasanyrole

                <!-- Отступ внизу чтобы скролл был красивым -->
                <div class="pb-4"></div>
            </nav>

            <!-- User Profile & Logout (В стиле нижних кнопок) -->
            <div class="p-4 border-t border-gray-200/60 bg-[#FAFAFA]">
                <div
                    class="flex items-center justify-between p-2 rounded-xl hover:bg-gray-200/50 transition-colors cursor-pointer group">
                    <div class="flex items-center space-x-3 overflow-hidden">
                        <!-- Аватарка -->
                        <div
                            class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0 border border-indigo-200">
                            <span
                                class="text-indigo-700 text-xs font-bold">{{ mb_substr(auth()->user()?->name ?? 'U', 0, 1) }}</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">
                                {{ auth()->user()?->name ?? 'User' }}
                            </p>
                            <p class="text-[11px] font-medium text-gray-500 truncate">
                                {{ auth()->user()?->roles->first()?->name ?? 'Гость' }}
                            </p>
                        </div>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors flex items-center justify-center"
                            title="Выйти">
                            <span class="material-symbols-outlined text-[18px]">logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="ml-64 flex-1 min-h-screen">
            <!-- Top Bar -->
            <header class="bg-white border-b border-gray-200 px-6 py-4 sticky top-0 z-40">
                <div class="flex items-center justify-between">
                    <!-- Breadcrumb -->
                    <nav class="flex items-center space-x-2 text-sm">
                        <span class="text-gray-400">Home</span>
                        <span class="material-symbols-outlined text-xs text-gray-300">chevron_right</span>
                        <span class="text-gray-900 font-medium">{{ $title ?? 'Dashboard' }}</span>
                    </nav>

                    <!-- Right side empty for spacing -->
                    <div></div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-6">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>

</html>
