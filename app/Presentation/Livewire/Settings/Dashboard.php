<?php

namespace App\Presentation\Livewire\Settings;

use App\Domain\Branch\Models\Branch;
use App\Domain\Branch\Models\User;
use App\Domain\Customer\Models\Customer;
use App\Domain\Finance\Models\FinancialTransaction;
use App\Domain\Ticketing\Models\PipelineStage;
use App\Domain\Ticketing\Models\Ticket;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Панель управления')]
class Dashboard extends Component
{
    public function render()
    {
        // Общая статистика
        $stats = [
            'total_customers' => Customer::count(),
            'total_users' => User::count(),
            'total_branches' => Branch::count(),
            'total_tickets' => Ticket::count(),
            'tickets_in_work' => Ticket::whereHas('currentStage', function ($q) {
                $q->where('order_column', '<', 7); // Все этапы до выдачи
            })->count(),
            'tickets_completed' => Ticket::whereHas('currentStage', function ($q) {
                $q->where('order_column', '>=', 7); // Этапы выдачи и закрытия
            })->count(),
        ];

        // Статистика по этапам воронки
        $pipelineStats = PipelineStage::withCount('tickets')
            ->orderBy('order_column')
            ->get()
            ->map(fn ($stage) => [
                'name' => $stage->name,
                'count' => $stage->tickets_count,
                'order' => $stage->order_column,
            ]);

        // Финансовая статистика
        $financeStats = [
            'total_income' => FinancialTransaction::where('type', 'income')->sum('amount'),
            'total_expense' => FinancialTransaction::where('type', 'expense')->sum('amount'),
            'today_income' => FinancialTransaction::where('type', 'income')
                ->whereDate('created_at', today())
                ->sum('amount'),
            'today_expense' => FinancialTransaction::where('type', 'expense')
                ->whereDate('created_at', today())
                ->sum('amount'),
        ];

        // Последние заявки
        $recentTickets = Ticket::with(['customer', 'currentStage', 'branch'])
            ->latest()
            ->take(10)
            ->get();

        // Топ клиентов по количеству заявок
        $topCustomers = Customer::withCount('tickets')
            ->orderBy('tickets_count', 'desc')
            ->take(5)
            ->get();

        // Статистика по типам устройств
        $deviceStats = Ticket::selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get();

        // Статистика по приоритетам
        $priorityStats = Ticket::selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->orderByDesc('count')
            ->get();

        return view('livewire.settings.dashboard', compact(
            'stats',
            'pipelineStats',
            'financeStats',
            'recentTickets',
            'topCustomers',
            'deviceStats',
            'priorityStats'
        ));
    }
}
