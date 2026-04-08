<?php

namespace Database\Seeders;

use App\Domain\Branch\Models\Branch;
use App\Domain\Branch\Models\User;
use App\Domain\Finance\Models\FinancialTransaction;
use App\Domain\Ticketing\Models\Ticket;
use Illuminate\Database\Seeder;

class FinancialSeeder extends Seeder
{
    public function run(): void
    {
        $branchMoscow = Branch::where('name', 'Филиал Москва')->first();
        $branchPiter = Branch::where('name', 'Филиал Питер')->first();
        $admin = User::role('Admin')->first();
        $techMoscow = User::role('Technician')->where('branch_id', $branchMoscow?->id)->first();

        if (! $branchMoscow) {
            return;
        }

        $transactions = [
            // Доходы (оплата ремонтов)
            ['branch_id' => $branchMoscow->id, 'user_id' => $admin?->id, 'type' => 'income', 'category' => 'repair_payment', 'payment_method' => 'cash', 'amount' => 8000, 'description' => 'Оплата за переклейку стекла iPhone 11'],
            ['branch_id' => $branchMoscow->id, 'user_id' => $admin?->id, 'type' => 'income', 'category' => 'repair_payment', 'payment_method' => 'card', 'amount' => 9500, 'description' => 'Оплата за замену петель HP ENVY x360'],
            ['branch_id' => $branchPiter->id, 'user_id' => null, 'type' => 'income', 'category' => 'repair_payment', 'payment_method' => 'cash', 'amount' => 7000, 'description' => 'Оплата за замену разъёма iPad Air'],
            ['branch_id' => $branchMoscow->id, 'user_id' => $admin?->id, 'type' => 'income', 'category' => 'repair_payment', 'payment_method' => 'card', 'amount' => 6000, 'description' => 'Оплата за замену аккумулятора Galaxy A54'],

            // Расходы
            ['branch_id' => $branchMoscow->id, 'user_id' => $techMoscow?->id, 'type' => 'expense', 'category' => 'parts', 'payment_method' => 'transfer', 'amount' => 3500, 'description' => 'Закупка дисплейного модуля iPhone 15'],
            ['branch_id' => $branchMoscow->id, 'user_id' => $admin?->id, 'type' => 'expense', 'category' => 'parts', 'payment_method' => 'cash', 'amount' => 1200, 'description' => 'Закупка аккумулятора iPhone 13'],
            ['branch_id' => $branchPiter->id, 'user_id' => null, 'type' => 'expense', 'category' => 'tools', 'payment_method' => 'card', 'amount' => 2500, 'description' => 'Набор отвёрток для мелкого ремонта'],
            ['branch_id' => $branchMoscow->id, 'user_id' => $admin?->id, 'type' => 'expense', 'category' => 'salary', 'payment_method' => 'transfer', 'amount' => 15000, 'description' => 'Комиссия мастера за неделю'],
        ];

        foreach ($transactions as $data) {
            // Привязываем к случайной закрытой заявке того же филиала
            $ticket = Ticket::withTrashed()
                ->where('branch_id', $data['branch_id'])
                ->whereHas('currentStage', fn ($q) => $q->where('order_column', 8))
                ->inRandomOrder()
                ->first();

            FinancialTransaction::create([
                'branch_id' => $data['branch_id'],
                'user_id' => $data['user_id'],
                'ticket_id' => $ticket?->id,
                'type' => $data['type'],
                'category' => $data['category'],
                'payment_method' => $data['payment_method'],
                'amount' => $data['amount'],
                'description' => $data['description'],
            ]);
        }

        $this->command->info('✅ Финансовые транзакции созданы.');
    }
}
