<?php

namespace App\Presentation\Livewire\Ticketing;

use App\Application\Services\GSMArenaService;
use App\Domain\Branch\Models\User;
use App\Domain\Finance\Actions\CreateTransactionAction;
use App\Domain\Inventory\Models\InventoryItem;
use App\Domain\Ticketing\Mails\MagicLinkMail;
use App\Domain\Ticketing\Mails\TicketReadyForPickupMail;
use App\Domain\Ticketing\Models\Checklist;
use App\Domain\Ticketing\Models\ChecklistResult;
use App\Domain\Ticketing\Models\DeviceType;
use App\Domain\Ticketing\Models\MagicLink;
use App\Domain\Ticketing\Models\PipelineStage;
use App\Domain\Ticketing\Models\Ticket;
use App\Domain\Ticketing\Models\TicketComment;
use App\Domain\Ticketing\Models\TicketInventory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Детали заявки')]
class TicketShow extends Component
{
    public Ticket $ticket;

    public array $specs = [];

    public array $checklistAnswers = [];

    public string $successMessage = '';

    // Свойство для хранения доступных запчастей
    public array $availablePartsList = [];

    // Свойства для модуля запчастей
    public string $newComment = '';

    public bool $showPartForm = false;

    public string $searchPartSku = '';

    // Разрешаем PHP принимать строки из HTML формы
    public int|string|null $selectedPartId = null;

    public float|string $partSellingPrice = 0;

    public float $laborCost = 0;

    public int|string $partWarrantyDays = 30;

    // Свойства для назначения мастера
    public string|int|null $selectedTechnicianId = null;

    // Свойства для редактирования запчасти
    public ?string $editingPartId = null;

    public float $editPartSellingPrice = 0;

    public int $editPartWarrantyDays = 0;

    // Список ID запчастей для отслеживания обновлений
    public int $availablePartsVersion = 0;

    #[Computed]
    public function nextStage(): ?PipelineStage
    {
        return PipelineStage::where('pipeline_id', $this->ticket->pipeline_id)
            ->where('order_column', '>', $this->ticket->currentStage->order_column)
            ->orderBy('order_column')
            ->first();
    }

    public function mount(Ticket $ticket, GSMArenaService $specsService)
    {
        // Подгружаем заявку + историю + уже добавленные запчасти (с их названиями из склада) + результаты чек-листов
        $this->ticket = $ticket->load([
            'customer',
            'currentStage',
            'histories.stage',
            'usedParts.inventoryItem.product',
            'comments.user',
            'technician',
            'checklistResults.user',
            'checklistResults.checklist.stage',
        ]);
        $this->laborCost = $this->ticket->labor_cost ?? 0;
        $this->specs = $specsService->getSpecs($ticket->device_brand, $ticket->device_model) ?? [];
    }

    // ... (код методов)

    public function moveToNextStage()
    {
        // 1. ПРОВЕРКА ЧЕК-ЛИСТА
        $currentChecklist = $this->currentChecklist;
        if ($currentChecklist) {
            $hasResult = $this->ticket->checklistResults()
                ->where('checklist_id', $currentChecklist->id)
                ->exists();

            if (! $hasResult) {
                $this->addError('stage_error', 'Необходимо заполнить чек-лист перед переходом на следующий этап!');

                return;
            }
        }

        // ЖЕСТКАЯ БЛОКИРОВКА: Если стадия = 3 (Согласование с клиентом)
        if ($this->ticket->currentStage->order_column == 3) {
            $this->addError('stage_error', 'Ожидайте ответа! Клиент должен согласовать ремонт по ссылке.');

            return;
        }

        $next = $this->nextStage;
        if (! $next) {
            return;
        }

        // ... дальше код метода moveToNextStage остается без изменений (последовательность закрытия/открытия этапов)
        // 1. Закрываем текущую историю
        $currentHistory = $this->ticket->histories()->whereNull('exited_at')->latest()->first();
        if ($currentHistory) {
            $currentHistory->update([
                'exited_at' => now(),
                'duration_minutes' => now()->diffInMinutes($currentHistory->entered_at),
            ]);
        }

        // 2. Обновляем заявку
        $this->ticket->update(['current_stage_id' => $next->id]);

        // 3. Открываем новую историю
        $this->ticket->histories()->create([
            'stage_id' => $next->id,
            'user_id' => auth()->id() ?? 1,
            'entered_at' => now(),
        ]);

        $this->successMessage = 'Заявка переведена на этап: '.$next->name;
        $this->ticket->refresh();
        $this->ticket->load(['currentStage', 'histories.stage']);
    }

    public function generateInvoiceAndClose(CreateTransactionAction $financeAction)
    {
        $closedStage = PipelineStage::where('pipeline_id', $this->ticket->pipeline_id)
            ->orderBy('order_column', 'desc')
            ->first();

        if ($closedStage && $this->ticket->current_stage_id !== $closedStage->id) {

            // 1. Закрываем текущую историю
            $currentHistory = $this->ticket->histories()->whereNull('exited_at')->latest()->first();
            if ($currentHistory) {
                $currentHistory->update([
                    'exited_at' => now(),
                    'duration_minutes' => now()->diffInMinutes($currentHistory->entered_at),
                ]);
            }

            // 2. Обновляем статус заявки
            $this->ticket->update(['current_stage_id' => $closedStage->id]);

            // 3. Открываем новую историю
            $this->ticket->histories()->create([
                'stage_id' => $closedStage->id,
                'user_id' => auth()->id() ?? 1,
                'entered_at' => now(),
            ]);

            // 4. Оставляем системный комментарий
            TicketComment::create([
                'ticket_id' => $this->ticket->id,
                'user_id' => null,
                'content' => '📄 Сгенерирован Акт выполненных работ. Устройство выдано клиенту. Оплата получена.',
            ]);

            // 5. ОТПРАВКА EMAIL УВЕДОМЛЕНИЯ КЛИЕНТУ
            // Если у клиента есть email, отправляем уведомление о готовности
            if ($this->ticket->customer->email) {
                Mail::to($this->ticket->customer->email)->queue(new TicketReadyForPickupMail($this->ticket));
            }

            // 6. МАГИЯ ФИНАНСОВ: Автоматически создаем доходную транзакцию!
            // Если сумма ремонта больше нуля, пополняем кассу филиала
            if ($this->ticket->estimated_cost > 0) {
                $financeAction->execute(
                    branchId: $this->ticket->branch_id,
                    type: 'income',
                    category: 'service_payment', // Оплата за ремонт
                    amount: $this->ticket->estimated_cost,
                    paymentMethod: 'card', // По умолчанию ставим карту (в идеале тут должен быть селект, но для MVP отлично)
                    description: 'Оплата ремонта '.$this->ticket->device_brand.' '.$this->ticket->device_model,
                    ticketId: $this->ticket->id
                );
            }

            $this->ticket->refresh();
            $this->ticket->load(['currentStage', 'histories.stage', 'comments.user']);
        }

        // 8. Открываем сгенерированный PDF в новой вкладке браузера
        $this->js("window.open('/tickets/{$this->ticket->ulid}/invoice', '_blank');");
    }

    public function editPart(string $partId)
    {
        $part = TicketInventory::findOrFail($partId);

        $this->editingPartId = $partId;
        $this->editPartSellingPrice = (float) $part->selling_price;
        $this->editPartWarrantyDays = (int) $part->warranty_days;
    }

    public function savePartEdit()
    {
        $this->validate([
            'editPartSellingPrice' => 'required|numeric|min:0',
            'editPartWarrantyDays' => 'required|integer|min:0',
        ]);

        $part = TicketInventory::findOrFail($this->editingPartId);
        $part->update([
            'selling_price' => $this->editPartSellingPrice,
            'warranty_days' => $this->editPartWarrantyDays,
        ]);

        $this->editingPartId = null;
        $this->ticket->refresh();
        $this->ticket->load(['usedParts.inventoryItem.product']);

        // Пересчитываем общую стоимость
        $partsTotal = $this->ticket->usedParts()->sum('selling_price');
        $laborCost = $this->ticket->labor_cost ?? 0;
        $this->ticket->update(['estimated_cost' => $partsTotal + $laborCost]);
        $this->laborCost = $laborCost;

        $this->successMessage = 'Данные запчасти обновлены';
    }

    public function removePart(string $partId)
    {
        $part = TicketInventory::findOrFail($partId);

        // Возвращаем запчасть на склад (делаем её снова available)
        $inventoryItem = $part->inventoryItem;
        if ($inventoryItem) {
            $inventoryItem->update(['status' => 'available']);
        }

        // Сохраняем selling_price до удаления
        $sellingPrice = $part->selling_price;

        $part->delete();

        $this->ticket->refresh();
        $this->ticket->load(['usedParts.inventoryItem.product']);

        // Пересчитываем общую стоимость
        $partsTotal = $this->ticket->usedParts()->sum('selling_price');
        $laborCost = $this->ticket->labor_cost ?? 0;
        $this->ticket->update(['estimated_cost' => $partsTotal + $laborCost]);
        $this->laborCost = $laborCost;

        $this->successMessage = 'Запчасть удалена из заявки';
    }

    public function availableParts()
    {
        if (! $this->showPartForm) {
            return [];
        }

        if (empty($this->availablePartsList)) {
            return [];
        }

        return InventoryItem::with('product')
            ->whereIn('id', $this->availablePartsList)
            ->get();
    }

    #[Computed]
    public function currentChecklist(): ?Checklist
    {
        $type = match ($this->ticket->currentStage->order_column) {
            1 => 'intake',
            2 => 'diagnostics',
            6 => 'qc',
            7 => 'output',
            8 => 'output',
            default => null,
        };

        if (! $type) {
            return null;
        }

        $deviceType = DeviceType::where('name', $this->ticket->device_type)->first();

        return Checklist::with('items')
            ->where('type', $type)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('stage_id')->orWhere('stage_id', $this->ticket->current_stage_id))
            ->where(fn ($q) => $q->whereNull('device_type_id')->when($deviceType, fn ($q) => $q->orWhere('device_type_id', $deviceType->id)))
            ->first();
    }

    public function saveChecklist()
    {
        $checklist = $this->currentChecklist;
        if (! $checklist) {
            return;
        }

        $userId = auth()->id() ?? User::first()->id ?? 1;

        ChecklistResult::create([
            'ticket_id' => $this->ticket->id,
            'checklist_id' => $checklist->id,
            'user_id' => $userId,
            'answers_json' => $this->checklistAnswers,
        ]);

        $this->successMessage = 'Чек-лист успешно заполнен и сохранен в базе!';
        $this->checklistAnswers = [];
    }

    public function updateLaborCost()
    {
        $this->validate(['laborCost' => 'required|numeric|min:0']);

        $this->ticket->update(['labor_cost' => $this->laborCost]);

        $partsTotal = $this->ticket->usedParts()->sum('selling_price');
        $this->ticket->update(['estimated_cost' => $partsTotal + $this->laborCost]);

        $this->ticket->refresh();
        $this->successMessage = 'Стоимость работы мастера обновлена!';
    }

    public function assignToMe()
    {
        $this->ticket->update([
            'assigned_technician_id' => auth()->id(),
        ]);

        $this->ticket->refresh();
        $this->successMessage = 'Вы назначены ответственным мастером по этой заявке!';
    }

    #[Computed]
    public function technicians()
    {
        $query = User::role('Technician');
        if (! auth()->user()->hasRole('Admin')) {
            $query->where('branch_id', auth()->user()->branch_id);
        }

        return $query->get();
    }

    public function assignTechnician()
    {
        if (empty($this->selectedTechnicianId)) {
            $this->addError('selectedTechnicianId', 'Выберите мастера');

            return;
        }

        $this->ticket->update([
            'assigned_technician_id' => $this->selectedTechnicianId,
        ]);

        $this->ticket->refresh();
        $this->successMessage = 'Мастер успешно назначен на заявку!';
    }

    public function addComment()
    {
        $this->validate(['newComment' => 'required|string|max:2000']);

        $this->ticket->comments()->create([
            'user_id' => auth()->id() ?? 1,
            'content' => $this->newComment,
        ]);

        $this->reset('newComment');
        $this->ticket->refresh();
        $this->ticket->load(['comments.user']);
    }

    public function generateMagicLink()
    {
        $link = MagicLink::create([
            'ticket_id' => $this->ticket->id,
            'token' => Str::random(32),
        ]);
        if ($this->ticket->customer->email) {
            Mail::to($this->ticket->customer->email)->queue(new MagicLinkMail($this->ticket, $link));
            $this->successMessage = 'Ссылка создана и письмо УЛЕТЕЛО клиенту на: '.$this->ticket->customer->email;
        } else {
            $this->successMessage = 'Ссылка создана (Email клиента не указан): '.route('client.portal', $link->token);
        }
    }

    public function updatedShowPartForm(bool $value)
    {
        if ($value) {
            $this->searchPartSku = '';
            $this->refreshAvailableParts();
        }
    }

    public function updatedSearchPartSku(string $value)
    {
        if ($this->showPartForm) {
            $this->refreshAvailableParts();
        }
    }

    public function refreshAvailableParts()
    {
        $parts = $this->loadAvailableParts();
        $this->availablePartsList = $parts->pluck('id')->toArray();
        $this->availablePartsVersion++;
    }

    public function render()
    {
        if ($this->showPartForm && empty($this->availablePartsList)) {
            $this->refreshAvailableParts();
        }

        return view('livewire.ticketing.ticket-show');
    }

    private function loadAvailableParts(): Collection
    {
        $query = InventoryItem::with('product')->where('status', 'available');

        if (! empty($this->searchPartSku)) {
            $query->where(function ($q) {
                $q->whereHas('product', function ($subQ) {
                    $subQ->where('name', 'like', '%'.$this->searchPartSku.'%')
                        ->orWhere('sku', 'like', '%'.$this->searchPartSku.'%');
                })->orWhere('serial_number', 'like', '%'.$this->searchPartSku.'%');
            });
        }

        return $query->take(15)->get();
    }
}
