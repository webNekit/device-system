<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Справочник устройств</h1>
            <p class="text-sm text-gray-500 mt-0.5">Импорт и управление базой</p>
        </div>
    </div>

    <!-- Import Section -->
    <div class="card p-6 w-full">
        <div class="flex items-center space-x-3 mb-5">
            <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center">
                <span class="material-symbols-outlined text-gray-600">upload_file</span>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Массовый импорт из CSV</h3>
                <p class="text-xs text-gray-500">Загрузка базы устройств</p>
            </div>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 mb-5">
            <div class="flex items-start space-x-3">
                <span class="material-symbols-outlined text-blue-600 text-sm mt-0.5">info</span>
                <div class="text-sm text-gray-600">
                    <p class="font-medium text-gray-900 mb-1">Формат файла (CSV с запятой):</p>
                    <p>В файле должно быть 3 колонки без заголовков: <code
                            class="bg-white px-2 py-0.5 rounded text-xs">Тип, Бренд, Модель</code></p>
                    <p class="mt-1 text-xs"><em>Пример: <code
                                class="bg-white px-2 py-0.5 rounded">Смартфон, Apple, iPhone 15 Pro</code></em></p>
                </div>
            </div>
        </div>

        @if($successMessage)
            <div class="mb-5 p-3 bg-green-50 text-green-700 rounded-lg text-sm flex items-center gap-2">
                <span class="material-symbols-outlined fill text-sm">check_circle</span>
                <span>{{ $successMessage }}</span>
            </div>
        @endif

        @if($errorMessage)
            <div class="mb-5 p-3 bg-red-50 text-red-700 rounded-lg text-sm flex items-center gap-2">
                <span class="material-symbols-outlined fill text-sm">error</span>
                <span>{{ $errorMessage }}</span>
            </div>
        @endif

        <div class="mb-5">
            <label
                class="flex items-center space-x-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer transition-colors">
                <input type="checkbox" wire:model="clearBeforeImport"
                    class="w-4 h-4 text-gray-900 rounded border-gray-300 focus:ring-gray-900" />
                <div class="flex items-center space-x-2">
                    <span class="material-symbols-outlined text-gray-400 text-sm">delete</span>
                    <span class="text-sm font-medium text-gray-700">Очистить базу перед импортом</span>
                </div>
            </label>
        </div>

        <form wire:submit="import" class="flex items-center space-x-4">
            <input type="file" wire:model="file" accept=".csv,.txt"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-gray-900 file:text-white hover:file:bg-gray-800 transition-colors" />
            <button type="submit" class="btn-primary whitespace-nowrap">
                <span wire:loading.remove wire:target="import">
                    <span class="material-symbols-outlined text-sm">upload</span>
                    Загрузить
                </span>
                <span wire:loading wire:target="import">
                    <span class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                    Загрузка...
                </span>
            </button>
        </form>
    </div>

    <!-- Database List -->
    <div class="table-container">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center space-x-3">
            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center">
                <span class="material-symbols-outlined text-gray-600 text-sm">database</span>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-900">Текущая база данных</h3>
                <p class="text-xs text-gray-500">Импортированные устройства</p>
            </div>
        </div>

        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
            @forelse($types as $type)
                <div class="border border-gray-200 rounded-xl p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center space-x-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center">
                            <span class="material-symbols-outlined text-gray-600 text-sm">devices</span>
                        </div>
                        <h4 class="text-sm font-semibold text-gray-900 uppercase">{{ $type->name }}</h4>
                    </div>
                    <div class="space-y-2">
                        @foreach($type->brands as $brand)
                            <div class="bg-white rounded-lg p-2 border border-gray-100">
                                <div class="flex items-center space-x-1.5 mb-1.5">
                                    <span class="material-symbols-outlined text-xs text-gray-400">business</span>
                                    <div class="text-xs font-medium text-gray-700 uppercase">{{ $brand->name }}</div>
                                </div>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($brand->models as $model)
                                        <span
                                            class="bg-gray-50 text-gray-600 text-[10px] font-medium px-2 py-1 rounded border border-gray-100">{{ $model->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-12">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                        <span class="material-symbols-outlined text-gray-400 text-sm">inventory_2</span>
                    </div>
                    <p class="text-sm text-gray-500">База устройств пуста</p>
                    <p class="text-xs text-gray-400 mt-1">Загрузите CSV файл для добавления устройств</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
