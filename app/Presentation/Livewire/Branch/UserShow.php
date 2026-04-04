<?php

namespace App\Presentation\Livewire\Branch;

use App\Domain\Branch\Models\User;
use App\Domain\Ticketing\Models\Ticket;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Профиль сотрудника')]
class UserShow extends Component
{
    public User $user;
    public string $period = 'this_month';

    public function mount(User $user)
    {
        $this->user = $user->load(['branch', 'roles']);
    }

    #[Computed]
    public function dateRange()
    {
        return match ($this->period) {
            'this_month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
            default => [Carbon::createFromTimestamp(0), Carbon::now()],
        };
    }

    #[Computed]
    public function stats()
    {
        // Ищем закрытые заявки, где этот сотрудник был мастером
        $tickets = Ticket::where('assigned_technician_id', $this->user->id)
            ->whereBetween('updated_at', $this->dateRange)
            ->whereHas('currentStage', fn($q) => $q->where('order_column', '>=', 7)) // Выдано или Закрыто
            ->with('usedParts.inventoryItem')
            ->get();

        $completedCount = $tickets->count();
        $totalRevenue = (float) $tickets->sum('estimated_cost');

        // Считаем себестоимость запчастей в его ремонтах
        // Убрали слово clone!
        $totalPartsCost = (float) $tickets->flatMap(function($ticket) {
            return $ticket->usedParts->map(fn($part) => $part->inventoryItem->purchase_price ?? 0);
        })->sum();

        // Грязная маржа (Доход минус Запчасти)
        $grossMargin = $totalRevenue - $totalPartsCost;

        // ЗАРПЛАТА МАСТЕРА (Его процент от маржи)
        $percent = $this->user->commission_percent ?? 0;

        // Если наторговали в минус, зарплата мастера просто 0 (он не платит из своего кармана)
        $technicianEarnings = $grossMargin > 0 ? $grossMargin * ($percent / 100) : 0;

        // ЧИСТАЯ ПРИБЫЛЬ КОМПАНИИ с этого мастера
        $companyProfit = $grossMargin - $technicianEarnings;

        return compact('completedCount', 'totalRevenue', 'totalPartsCost', 'grossMargin', 'technicianEarnings', 'companyProfit');
    }

    public function render()
    {
        return view('livewire.branch.user-show');
    }
}
