<div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden relative p-8">
    <div class="h-2 w-full bg-gradient-to-r from-gray-900 to-indigo-600 absolute top-0 left-0"></div>

    <div class="text-center mb-8 mt-4">
        <h2 class="text-2xl font-black italic tracking-tighter text-gray-900 uppercase">Вход в ERP</h2>
        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Авторизация сотрудника</p>
    </div>

    <form wire:submit="login" class="space-y-6">
        <div>
            <label class="block text-[10px] font-black uppercase text-gray-500 mb-1">Email</label>
            <input type="email" wire:model="email" class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 bg-gray-50" placeholder="admin@example.com">
            @error('email') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-[10px] font-black uppercase text-gray-500 mb-1">Пароль</label>
            <input type="password" wire:model="password" class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 bg-gray-50">
        </div>

        <div class="flex items-center">
            <input type="checkbox" wire:model="remember" id="remember" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <label for="remember" class="ml-2 block text-xs font-bold text-gray-700">Запомнить меня</label>
        </div>

        <button type="submit" class="w-full py-4 bg-gray-900 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg hover:bg-black transition-all">
            <span wire:loading.remove wire:target="login">Войти в систему</span>
            <span wire:loading wire:target="login">Проверка...</span>
        </button>
    </form>
</div>
