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
            @apply w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent transition-all;
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
    </style>
</head>

<body class="antialiased min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="sidebar w-64 fixed h-screen left-0 top-0 flex flex-col z-50">
            <!-- Logo -->
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gray-900 rounded flex items-center justify-center">
                        <span class="text-white text-xs font-bold">ERP</span>
                    </div>
                    <div>
                        <h1 class="text-sm font-semibold text-gray-900">ERP System</h1>
                        <p class="text-xs text-gray-500">Management Panel</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-3 overflow-y-auto">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>

                <!-- Управление -->
                <div class="mt-2">
                    <div class="sidebar-category">Управление</div>
                    <a href="{{ route('branches.index') }}" wire:navigate
                        class="sidebar-item {{ request()->routeIs('branches.*') ? 'active' : '' }}">
                        <span class="material-symbols-outlined">business</span>
                        <span>Филиалы</span>
                    </a>
                    <a href="{{ route('users.index') }}" wire:navigate
                        class="sidebar-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <span class="material-symbols-outlined">people</span>
                        <span>Сотрудники</span>
                    </a>
                    <a href="{{ route('finance.index') }}" wire:navigate
                        class="sidebar-item {{ request()->routeIs('finance.*') ? 'active' : '' }}">
                        <span class="material-symbols-outlined">account_balance_wallet</span>
                        <span>Финансы</span>
                    </a>
                </div>

                <!-- Клиенты -->
                <div class="mt-2">
                    <div class="sidebar-category">Клиенты</div>
                    <a href="{{ route('customers.index') }}" wire:navigate
                        class="sidebar-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                        <span class="material-symbols-outlined">person</span>
                        <span>Клиенты</span>
                    </a>
                </div>

                <!-- Справочники -->
                <div class="mt-2">
                    <div class="sidebar-category">Справочники</div>
                    <a href="{{ route('settings.devices') }}" wire:navigate
                        class="sidebar-item {{ request()->routeIs('settings.devices') ? 'active' : '' }}">
                        <span class="material-symbols-outlined">devices</span>
                        <span>Устройства</span>
                    </a>
                    <a href="{{ route('settings.checklists') }}" wire:navigate
                        class="sidebar-item {{ request()->routeIs('settings.checklists') ? 'active' : '' }}">
                        <span class="material-symbols-outlined">fact_check</span>
                        <span>Чек-листы</span>
                    </a>
                </div>

                @hasanyrole(['Admin', 'Storekeeper'])
                <!-- Склад -->
                <div class="mt-2">
                    <div class="sidebar-category">Склад</div>
                    <a href="{{ route('inventory.index') }}" wire:navigate
                        class="sidebar-item {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                        <span class="material-symbols-outlined">warehouse</span>
                        <span>Склады</span>
                    </a>
                    <a href="{{ route('inventory.products') }}" wire:navigate
                        class="sidebar-item {{ request()->routeIs('inventory.products') ? 'active' : '' }}">
                        <span class="material-symbols-outlined">inventory_2</span>
                        <span>Номенклатура</span>
                    </a>
                </div>
                @endhasanyrole

                @hasanyrole(['Admin', 'Branch Manager', 'Technician'])
                <!-- Сервис -->
                <div class="mt-2">
                    <div class="sidebar-category">Сервис</div>
                    <a href="{{ route('tickets.index') }}" wire:navigate
                        class="sidebar-item {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                        <span class="material-symbols-outlined">confirmation_number</span>
                        <span>Заявки</span>
                    </a>
                </div>
                @endhasanyrole

                @hasrole('Admin')
                <!-- Система -->
                <div class="mt-2">
                    <div class="sidebar-category">Система</div>
                    <a href="/pulse" class="sidebar-item">
                        <span class="material-symbols-outlined">monitoring</span>
                        <span>Pulse</span>
                    </a>
                </div>
                @endhasrole
            </nav>

            <!-- User Profile -->
            <div class="p-3 border-t border-gray-200">
                <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 border border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-8 h-8 rounded-full bg-gray-900 flex items-center justify-center text-white text-xs font-medium">
                            {{ mb_substr(auth()->user()?->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-gray-900 truncate">{{ auth()->user()?->name ?? 'User' }}
                            </p>
                            <p class="text-[10px] text-gray-500 truncate">
                                {{ auth()->user()?->roles->first()?->name ?? 'User' }}
                            </p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-200 rounded transition-colors">
                            <span class="material-symbols-outlined text-sm">logout</span>
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