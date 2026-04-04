<div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden relative">
    <div class="h-2 w-full bg-gradient-to-r from-indigo-500 to-purple-600"></div>
    <div class="p-8">
        <h2 class="text-xs font-black uppercase text-gray-400 tracking-widest mb-1">Ваше устройство</h2>
        <div class="text-2xl font-black italic tracking-tighter text-gray-900 mb-6">
            {{ $ticket->device_brand }} {{ $ticket->device_model }}
        </div>
        <div class="mb-8 p-4 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center space-x-4">
            <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <div class="text-[10px] font-black uppercase text-indigo-600 tracking-widest mb-1">Статус ремонта</div>
                <div class="text-lg font-bold text-gray-900">{{ $ticket->currentStage->name }}</div>
            </div>
        </div>
        <div class="mb-8">
            <h3 class="text-xs font-black uppercase text-gray-400 tracking-widest mb-3 border-b border-gray-100 pb-2">Запчасти и работы</h3>
            <div class="space-y-3">
                @forelse($ticket->usedParts as $part)
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-medium text-gray-700">{{ $part->inventoryItem->product->name ?? 'Деталь' }}</span>
                        <span class="font-black text-gray-900">{{ number_format($part->selling_price, 0, '.', ' ') }} ₽</span>
                    </div>
                @empty
                    <div class="text-xs italic text-gray-500 text-center py-2">Детали пока не добавлены</div>
                @endforelse

                <!-- Итого -->
                <div class="flex justify-between items-center pt-4 mt-2 border-t border-gray-200">
                    <span class="text-xs font-black uppercase text-gray-900 tracking-widest">Итого к оплате</span>
                    <span class="text-2xl font-black italic text-indigo-600">{{ number_format($ticket->estimated_cost, 0, '.', ' ') }} ₽</span>
                </div>
            </div>
        </div>
        @if(!$isApproved && $ticket->currentStage->order_column == 3)
        <div class="space-y-3">
            <button wire:click="approveRepair" wire:loading.attr="disabled" class="w-full py-4 bg-indigo-600 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:shadow-none transition-all">
                <span wire:loading.remove wire:target="approveRepair">Согласовать сумму</span>
                <span wire:loading wire:target="approveRepair">Отправка...</span>
            </button>
            <button wire:click="rejectRepair" wire:loading.attr="disabled" class="w-full py-3 bg-white border-2 border-gray-200 text-gray-500 hover:text-red-600 hover:border-red-200 hover:bg-red-50 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all">
                Отказаться от ремонта
            </button>
        </div>
        <p class="text-[10px] text-gray-400 text-center mt-4">
            Нажимая "Согласовать", вы подтверждаете стоимость ремонта и запчастей.
        </p>
        @elseif($isApproved)
            <div class="text-center p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 text-sm font-bold">
                ✅ Ваш ответ успешно отправлен мастеру!
            </div>
        @else
            <div class="text-center p-4 bg-gray-50 border border-gray-200 rounded-xl text-gray-500 text-xs font-bold">
                Ожидайте изменения статуса ремонта.
            </div>
        @endif

    </div>
</div>
