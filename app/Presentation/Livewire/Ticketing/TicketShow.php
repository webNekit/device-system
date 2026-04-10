<?php

namespace App\Presentation\Livewire\Ticketing;

use App\Application\Services\GSMArenaService;
use App\Domain\Branch\Models\User;
use App\Domain\Finance\Actions\CreateTransactionAction;
use App\Domain\Inventory\Models\InventoryItem;
use App\Domain\Ticketing\Actions\AttachPartToTicketAction;
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
use Illuminate\Validation\Rule;
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

    public function mount(Ticket $ticket, GSMArenaService $specsService)
    {
        // Подгружаем заявку + историю + уже добавленные запчасти (с их названиями из склада)
        $this->ticket = $ticket->load([
            'customer',
            'currentStage',
            'histories.stage',
            'usedParts.inventoryItem.product',
            'comments.user',
            'technician',
        ]);
        $this->laborCost = $this->ticket->labor_cost ?? 0;
        $this->specs = $specsService->getSpecs($ticket->device_brand, $ticket->device_model) ?? [];
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

    #[Computed]
    public function currentChecklist()
    {
        // Ориентируемся на реальные номера твоих стадий
        $type = match ($this->ticket->currentStage->order_column) {
            1 => 'intake', // 1: Приемка и Осмотр
            6 => 'qc',     // 6: Контроль качества (QC)
            7 => 'output', // 7: Готово к выдаче
            8 => 'output', // 8: Выдано / Закрыто
            default => null
        };

        if (! $type) {
            return null;
        }

        // Сначала ищем чек-лист, привязанный к конкретной стадии и типу устройства
        $checklist = Checklist::with('items')
            ->where('type', $type)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('stage_id')
                    ->orWhere('stage_id', $this->ticket->current_stage_id);
            })
            ->where(function ($q) {
                // Получаем device_type из названия типа устройства
                $deviceType = DeviceType::where('name', $this->ticket->device_type)->first();
                if ($deviceType) {
                    $q->whereNull('device_type_id')
                        ->orWhere('device_type_id', $deviceType->id);
                } else {
                    $q->whereNull('device_type_id');
                }
            })
            ->orderBy('sort_order')
            ->first();

        return $checklist;
    }

    // --- МОДУЛЬ СКЛАДА (Поиск и добавление запчасти) ---

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

    public function attachPart(AttachPartToTicketAction $action)
    {
        $this->validate([
            // Добавляем строгую проверку прямо в БД перед запуском Action
            'selectedPartId' => [
                'required',
                Rule::exists('inventory_items', 'id')->where('status', 'available'),
            ],
            'partSellingPrice' => 'required|numeric|min:0',
            'partWarrantyDays' => 'required|integer|min:0',
        ], [
            // Кастомное сообщение об ошибке, если деталь уже забрал другой мастер
            'selectedPartId.exists' => 'Эта деталь уже зарезервирована или списана.',
        ]);

        // ... дальше код остается без изменений
        $action->execute(
            $this->ticket,
            $this->selectedPartId, // Теперь передается правильная строка ULID
            (float) $this->partSellingPrice,
            (int) $this->partWarrantyDays
        );

        // Сбрасываем форму
        $this->reset(['showPartForm', 'searchPartSku', 'selectedPartId', 'partSellingPrice']);
        $this->partWarrantyDays = 30;
        $this->refreshAvailableParts();

        // Обновляем данные заявки
        $this->ticket->refresh();
        $this->ticket->load(['usedParts.inventoryItem.product']);
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

        // Очищаем галочки после сохранения
        $this->checklistAnswers = [];
    }

    public function addComment()
    {
        $this->validate(['newComment' => 'required|string|max:2000']);

        $this->ticket->comments()->create([
            'user_id' => auth()->id() ?? 1, // Если не авторизован, ставим админа для теста
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

    #[Computed]
    public function nextStage()
    {
        // Вычисляем, какая стадия будет следующей в воронке
        return PipelineStage::where('pipeline_id', $this->ticket->pipeline_id)
            ->where('order_column', '>', $this->ticket->currentStage->order_column)
            ->orderBy('order_column', 'asc')
            ->first();
    }

    public function moveToNextStage()
    {
        // ЖЕСТКАЯ БЛОКИРОВКА: Если стадия = 3 (Согласование с клиентом)
        if ($this->ticket->currentStage->order_column == 3) {
            $this->addError('stage_error', 'Ожидайте ответа! Клиент должен согласовать ремонт по ссылке.');

            return;
        }

        $next = $this->nextStage;
        if (! $next) {
            return;
        }

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

    public function render()
    {
        // Инициализируем список запчастей при первом рендере
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
