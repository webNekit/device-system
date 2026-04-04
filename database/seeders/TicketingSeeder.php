<?php

namespace Database\Seeders;

use App\Domain\Branch\Models\Branch;
use App\Domain\Customer\Models\Customer;
use App\Domain\Ticketing\Models\Pipeline;
use App\Domain\Ticketing\Models\Ticket;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TicketingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Создаем правильный пайплайн
        $pipeline = Pipeline::firstOrCreate(['name' => 'Стандартный ремонт электроники']);

        // 2. Создаем ПРАВИЛЬНЫЕ 8 стадий (Критично для работы бизнес-логики!)
        $stages = [
            ['name' => 'Приемка и Осмотр', 'order_column' => 1, 'sla_max_minutes' => 60],
            ['name' => 'Диагностика', 'order_column' => 2, 'sla_max_minutes' => 1440],
            ['name' => 'Согласование с клиентом', 'order_column' => 3, 'sla_max_minutes' => 2880], // Тут работает Magic Link
            ['name' => 'Ожидание запчастей', 'order_column' => 4, 'sla_max_minutes' => 10080],
            ['name' => 'В ремонте', 'order_column' => 5, 'sla_max_minutes' => 2880],
            ['name' => 'Контроль качества (QC)', 'order_column' => 6, 'sla_max_minutes' => 120], // Тут работает Чек-лист
            ['name' => 'Готово к выдаче', 'order_column' => 7, 'sla_max_minutes' => 10080],      // Тут генерация PDF
            ['name' => 'Выдано / Закрыто', 'order_column' => 8, 'sla_max_minutes' => null],
        ];

        foreach ($stages as $stageData) {
            $pipeline->stages()->firstOrCreate(['name' => $stageData['name']], $stageData);
        }

        // 3. Создаем тестовую заявку (Привязываем к Москве)
        $branch = Branch::where('name', 'Филиал Москва')->first() ?? Branch::first();
        $customer = Customer::first();

        if (! $branch || ! $customer) {
            return;
        }

        $firstStage = $pipeline->stages()->where('order_column', 1)->first();

        Ticket::firstOrCreate(
            ['serial_number' => 'TEST-12345'], // Уникальный признак для предотвращения дублей
            [
                'ulid' => Str::ulid(),
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'pipeline_id' => $pipeline->id,
                'current_stage_id' => $firstStage->id,
                'device_type' => 'Смартфон',
                'device_brand' => 'Apple',
                'device_model' => 'iPhone 15 Pro',
                'defect_description' => 'Не включается после падения (Тестовая заявка)',
                'priority' => 'normal',
                'estimated_cost' => 0, // Изначально 0, пока не добавили запчасть
            ]
        );
    }
}
