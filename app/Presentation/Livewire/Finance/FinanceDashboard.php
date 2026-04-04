<?php

namespace App\Presentation\Livewire\Finance;

use App\Domain\Branch\Models\Branch;
use App\Domain\Finance\Actions\CreateTransactionAction;
use App\Domain\Finance\Models\FinancialTransaction;
use App\Domain\Ticketing\Models\Ticket;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Финансы и P&L')]
class FinanceDashboard extends Component
{
    // Фильтры
    public ?int $branch_id = null;

    public string $period = 'this_month'; // this_month, last_month, all_time

    // Форма добавления ручной транзакции
    public bool $showForm = false;

    public string $type = 'expense';

    public string $category = 'other';

    public string $payment_method = 'cash';

    public float $amount = 0;

    public string $description = '';

    public ?int $form_branch_id = null;

    public string $successMessage = '';

    public function mount()
    {
        if (! auth()->user()->hasRole('Admin')) {
            $this->branch_id = auth()->user()->branch_id;
            $this->form_branch_id = auth()->user()->branch_id;
        }
    }

    #[Computed]
    public function branches()
    {
        return Branch::all();
    }

    #[Computed]
    public function dateRange()
    {
        return match ($this->period) {
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
            default => [Carbon::createFromTimestamp(0), Carbon::now()], // all_time
        };
    }

    #[Computed]
    public function analytics()
    {
        $query = FinancialTransaction::whereBetween('created_at', $this->dateRange);
        if ($this->branch_id) {
            $query->where('branch_id', $this->branch_id);
        }

        $transactions = $query->get();

        // 1. Выручка (Оплаты от клиентов)
        $revenue = $transactions->where('type', 'income')->sum('amount');

        // 2. Расходы (Аренда, з/п, прочее)
        $expenses = $transactions->where('type', 'expense')->sum('amount');

        // 3. Себестоимость запчастей (Считаем по закрытым заявкам)
        $cogsQuery = Ticket::whereBetween('updated_at', $this->dateRange)
            ->whereHas('currentStage', fn ($q) => $q->where('order_column', '>=', 7)) // Стадии "Готово" или "Выдано"
            ->with(['usedParts.inventoryItem']);

        if ($this->branch_id) {
            $cogsQuery->where('branch_id', $this->branch_id);
        }

        $cogs = $cogsQuery->get()->flatMap(function ($ticket) {
            return $ticket->usedParts->map(fn ($part) => $part->inventoryItem->purchase_price ?? 0);
        })->sum();

        // 4. Валовая прибыль (Выручка - Себестоимость)
        $grossProfit = $revenue - $cogs;

        // 5. Чистая прибыль (Валовая прибыль - Расходы)
        $netProfit = $grossProfit - $expenses;

        // 6. Маржинальность (%)
        $margin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0;

        return compact('revenue', 'expenses', 'cogs', 'grossProfit', 'netProfit', 'margin');
    }

    // Последние операции (таблица)
    #[Computed]
    public function recentTransactions()
    {
        $query = FinancialTransaction::with(['user', 'ticket.customer'])->latest();
        if ($this->branch_id) {
            $query->where('branch_id', $this->branch_id);
        }

        return $query->take(20)->get();
    }

    public function saveTransaction(CreateTransactionAction $action)
    {
        // Убрали жесткую проверку категорий (in:...), так как их много, оставили просто string
        $this->validate([
            'form_branch_id' => 'required|exists:branches,id',
            'type' => 'required|in:income,expense',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,transfer',
            'description' => 'required|string|max:255',
        ]);

        $action->execute(
            (int) $this->form_branch_id,
            $this->type,
            $this->category,
            (float) $this->amount,
            $this->payment_method,
            $this->description
        );

        $this->reset(['showForm', 'type', 'category', 'amount', 'payment_method', 'description']);
        $this->type = 'expense';
        $this->category = 'other';
        $this->payment_method = 'cash';

        $this->successMessage = 'Финансовая операция проведена!';
        unset($this->analytics);
        unset($this->recentTransactions);
    }

    public function render()
    {
        return view('livewire.finance.finance-dashboard');
    }
}
