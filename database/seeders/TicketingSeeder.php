<?php

namespace Database\Seeders;

use App\Domain\Branch\Models\Branch;
use App\Domain\Branch\Models\User;
use App\Domain\Customer\Models\Customer;
use App\Domain\Ticketing\Models\Checklist;
use App\Domain\Ticketing\Models\ChecklistResult;
use App\Domain\Ticketing\Models\MagicLink;
use App\Domain\Ticketing\Models\Pipeline;
use App\Domain\Ticketing\Models\Ticket;
use App\Domain\Ticketing\Models\TicketStageHistory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TicketingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Создаём пайплайн
        $pipeline = Pipeline::firstOrCreate(['name' => 'Стандартный ремонт электроники']);

        // 2. Создаём 8 стадий
        $stagesData = [
            ['name' => 'Приемка и Осмотр', 'order_column' => 1, 'sla_max_minutes' => 60],
            ['name' => 'Диагностика', 'order_column' => 2, 'sla_max_minutes' => 1440],
            ['name' => 'Согласование с клиентом', 'order_column' => 3, 'sla_max_minutes' => 2880],
            ['name' => 'Ожидание запчастей', 'order_column' => 4, 'sla_max_minutes' => 10080],
            ['name' => 'В ремонте', 'order_column' => 5, 'sla_max_minutes' => 2880],
            ['name' => 'Контроль качества (QC)', 'order_column' => 6, 'sla_max_minutes' => 120],
            ['name' => 'Готово к выдаче', 'order_column' => 7, 'sla_max_minutes' => 10080],
            ['name' => 'Выдано / Закрыто', 'order_column' => 8, 'sla_max_minutes' => null],
        ];

        $stages = [];
        foreach ($stagesData as $stageData) {
            $stage = $pipeline->stages()->firstOrCreate(
                ['name' => $stageData['name']],
                $stageData
            );
            $stages[$stageData['order_column']] = $stage;
        }

        // Данные
        $branchMoscow = Branch::where('name', 'Филиал Москва')->first();
        $branchPiter = Branch::where('name', 'Филиал Питер')->first();
        $customers = Customer::all();
        $technicians = User::role('Technician')->get();

        if (! $branchMoscow || $customers->isEmpty() || $technicians->isEmpty()) {
            $this->command->error('Не хватает данных: проверьте что Branch, Customer и Technician сидеры отработали.');

            return;
        }

        // Вспомогательный метод для создания заявки
        $createTicket = function (int $stageOrder, string $branch, Customer $customer, ?User $technician, string $deviceType, string $deviceBrand, string $deviceModel, string $serialNumber, string $defect, string $priority, ?string $deadline, array $historyMinutes, float $estimatedCost = 0, float $laborCost = 0) use ($pipeline, $stages) {
            $now = Carbon::now();
            $stage = $stages[$stageOrder];

            // Рассчитываем когда заявка пришла на текущий этап (отнимаем суммарное время на предыдущих)
            $enteredAt = $now->copy()->subMinutes(array_sum($historyMinutes));
            $durationInCurrentStage = end($historyMinutes);

            $ticket = Ticket::create([
                'ulid' => Str::ulid(),
                'branch_id' => $branch,
                'customer_id' => $customer->id,
                'pipeline_id' => $pipeline->id,
                'current_stage_id' => $stage->id,
                'assigned_technician_id' => $technician?->id,
                'device_type' => $deviceType,
                'device_brand' => $deviceBrand,
                'device_model' => $deviceModel,
                'serial_number' => $serialNumber,
                'defect_description' => $defect,
                'priority' => $priority,
                'estimated_cost' => $estimatedCost,
                'labor_cost' => $laborCost,
                'sla_deadline_at' => $deadline ? Carbon::parse($deadline) : null,
                'created_at' => $enteredAt,
                'updated_at' => $enteredAt,
            ]);

            // Заполняем историю перемещений по этапам
            $cursor = $enteredAt->copy();
            foreach ($historyMinutes as $index => $minutes) {
                $historyStageOrder = $index + 1;
                if (! isset($stages[$historyStageOrder])) {
                    break;
                }
                $historyStage = $stages[$historyStageOrder];
                $enterTime = $cursor->copy();
                $exitTime = $index === count($historyMinutes) - 1 ? null : $cursor->copy()->addMinutes($minutes);

                TicketStageHistory::create([
                    'ticket_id' => $ticket->id,
                    'stage_id' => $historyStage->id,
                    'user_id' => $technician?->id,
                    'entered_at' => $enterTime,
                    'exited_at' => $exitTime,
                    'duration_minutes' => $minutes,
                ]);

                $cursor->addMinutes($minutes);
            }

            return $ticket;
        };

        // ================================================================
        // СТАДИЯ 1: Приемка и Осмотр (2 заявки)
        // ================================================================
        $createTicket(
            stageOrder: 1,
            branch: $branchMoscow->id,
            customer: $customers->first(),
            technician: null,
            deviceType: 'Смартфон',
            deviceBrand: 'Apple',
            deviceModel: 'iPhone 15 Pro',
            serialNumber: 'SN-APL-0001',
            defect: 'Не включается после падения. При подключении к зарядному устройству индикатор не реагирует.',
            priority: 'high',
            deadline: null,
            historyMinutes: [15] // только что поступила
        );

        $createTicket(
            stageOrder: 1,
            branch: $branchMoscow->id,
            customer: $customers->skip(1)->first(),
            technician: null,
            deviceType: 'Ноутбук',
            deviceBrand: 'ASUS',
            deviceModel: 'VivoBook 15',
            serialNumber: 'SN-ASU-0002',
            defect: 'Сильно греется при работе и самопроизвольно выключается через 15-20 минут.',
            priority: 'normal',
            deadline: null,
            historyMinutes: [10]
        );

        // ================================================================
        // СТАДИЯ 2: Диагностика (3 заявки)
        // ================================================================
        $techMoscow = $technicians->first();
        $createTicket(
            stageOrder: 2,
            branch: $branchMoscow->id,
            customer: $customers->first(),
            technician: $techMoscow,
            deviceType: 'Смартфон',
            deviceBrand: 'Samsung',
            deviceModel: 'Galaxy S23',
            serialNumber: 'SN-SMS-0003',
            defect: 'Не работает сенсорная поверхность экрана — тач не реагирует на нажатия.',
            priority: 'high',
            deadline: now()->addHours(12)->toDateTimeString(),
            historyMinutes: [20, 45]
        );

        $createTicket(
            stageOrder: 2,
            branch: $branchPiter->id,
            customer: $customers->skip(1)->first(),
            technician: $technicians->skip(1)->first(),
            deviceType: 'Планшет',
            deviceBrand: 'Apple',
            deviceModel: 'iPad Pro 11',
            serialNumber: 'SN-IPD-0004',
            defect: 'Быстро разряжается батарея, не держит заряд. Выключается при 20%.',
            priority: 'normal',
            deadline: now()->addHours(24)->toDateTimeString(),
            historyMinutes: [25, 90]
        );

        $createTicket(
            stageOrder: 2,
            branch: $branchMoscow->id,
            customer: $customers->first(),
            technician: $techMoscow,
            deviceType: 'Ноутбук',
            deviceBrand: 'Lenovo',
            deviceModel: 'ThinkPad E14',
            serialNumber: 'SN-LNV-0005',
            defect: 'Не определяется SSD диск в BIOS. Система не загружается.',
            priority: 'normal',
            deadline: now()->addHours(24)->toDateTimeString(),
            historyMinutes: [30, 60]
        );

        // ================================================================
        // СТАДИЯ 3: Согласование с клиентом (2 заявки)
        // ================================================================
        $ticket6 = $createTicket(
            stageOrder: 3,
            branch: $branchMoscow->id,
            customer: $customers->first(),
            technician: $techMoscow,
            deviceType: 'Смартфон',
            deviceBrand: 'Apple',
            deviceModel: 'iPhone 14 Pro',
            serialNumber: 'SN-APL-0006',
            defect: 'Разбито заднее стекло, не работает камера. Требуется замена корпуса и модуля камеры.',
            priority: 'normal',
            estimatedCost: 18500,
            laborCost: 4500,
            deadline: now()->addHours(36)->toDateTimeString(),
            historyMinutes: [20, 60, 15]
        );

        // Magic Link для заявки на этапе согласования
        MagicLink::create([
            'ticket_id' => $ticket6->id,
            'token' => Str::random(32),
            'expires_at' => now()->addDays(3),
        ]);

        $createTicket(
            stageOrder: 3,
            branch: $branchPiter->id,
            customer: $customers->skip(1)->first(),
            technician: $technicians->skip(1)->first(),
            deviceType: 'Ноутбук',
            deviceBrand: 'HP',
            deviceModel: 'Pavilion 15',
            serialNumber: 'SN-HP-0007',
            defect: 'Вышедшая из строя матрица — полосы на экране. Замена матрицы 15.6" IPS.',
            priority: 'normal',
            estimatedCost: 12000,
            laborCost: 3000,
            deadline: now()->addHours(48)->toDateTimeString(),
            historyMinutes: [15, 45, 30]
        );

        // ================================================================
        // СТАДИЯ 4: Ожидание запчастей (2 заявки)
        // ================================================================
        $createTicket(
            stageOrder: 4,
            branch: $branchMoscow->id,
            customer: $customers->first(),
            technician: $techMoscow,
            deviceType: 'Смартфон',
            deviceBrand: 'Xiaomi',
            deviceModel: 'Redmi Note 13',
            serialNumber: 'SN-XIA-0008',
            defect: 'Разбит дисплейный модуль. Согласовано, ожидается доставка дисплея.',
            priority: 'low',
            estimatedCost: 6500,
            laborCost: 2000,
            deadline: now()->addDays(5)->toDateTimeString(),
            historyMinutes: [20, 60, 15, 2880] // ждёт уже 2 дня
        );

        $createTicket(
            stageOrder: 4,
            branch: $branchPiter->id,
            customer: $customers->skip(1)->first(),
            technician: $technicians->skip(1)->first(),
            deviceType: 'Смартфон',
            deviceBrand: 'Apple',
            deviceModel: 'iPhone 13',
            serialNumber: 'SN-APL-0009',
            defect: 'Замена аккумулятора. Клиент согласовал, аккумулятор ожидается со склада.',
            priority: 'normal',
            estimatedCost: 5500,
            laborCost: 2000,
            deadline: now()->addDays(3)->toDateTimeString(),
            historyMinutes: [15, 45, 20, 1440] // ждёт 1 день
        );

        // ================================================================
        // СТАДИЯ 5: В ремонте (3 заявки)
        // ================================================================
        $createTicket(
            stageOrder: 5,
            branch: $branchMoscow->id,
            customer: $customers->first(),
            technician: $techMoscow,
            deviceType: 'Смартфон',
            deviceBrand: 'Apple',
            deviceModel: 'iPhone 12',
            serialNumber: 'SN-APL-0010',
            defect: 'Замена разъёма Lightning. Запчасти получены, идёт ремонт.',
            priority: 'normal',
            estimatedCost: 4500,
            laborCost: 2500,
            deadline: now()->addHours(24)->toDateTimeString(),
            historyMinutes: [20, 60, 15, 1440, 90]
        );

        $createTicket(
            stageOrder: 5,
            branch: $branchMoscow->id,
            customer: $customers->skip(1)->first(),
            technician: $techMoscow,
            deviceType: 'Ноутбук',
            deviceBrand: 'Acer',
            deviceModel: 'Nitro 5',
            serialNumber: 'SN-ACR-0011',
            defect: 'Чистка системы охлаждения, замена термопасты. Ремонт в процессе.',
            priority: 'low',
            estimatedCost: 3500,
            laborCost: 2500,
            deadline: now()->addHours(48)->toDateTimeString(),
            historyMinutes: [15, 90, 30, 2880, 120]
        );

        $createTicket(
            stageOrder: 5,
            branch: $branchPiter->id,
            customer: $customers->first(),
            technician: $technicians->skip(1)->first(),
            deviceType: 'Планшет',
            deviceBrand: 'Samsung',
            deviceModel: 'Galaxy Tab S9',
            serialNumber: 'SN-SMT-0012',
            defect: 'Замена разъёма USB-C. Пайка разъёма на плате.',
            priority: 'high',
            estimatedCost: 5000,
            laborCost: 3000,
            deadline: now()->addHours(12)->toDateTimeString(),
            historyMinutes: [20, 45, 20, 1440, 60]
        );

        // ================================================================
        // СТАДИЯ 6: Контроль качества (QC) (2 заявки)
        // ================================================================
        $ticket13 = $createTicket(
            stageOrder: 6,
            branch: $branchMoscow->id,
            customer: $customers->first(),
            technician: $techMoscow,
            deviceType: 'Смартфон',
            deviceBrand: 'Apple',
            deviceModel: 'iPhone 15',
            serialNumber: 'SN-APL-0013',
            defect: 'Замена дисплейного модуля. Ремонт завершён, на проверке QC.',
            priority: 'normal',
            estimatedCost: 15000,
            laborCost: 3500,
            deadline: now()->addHours(6)->toDateTimeString(),
            historyMinutes: [20, 60, 15, 0, 120, 30]
        );

        // Результат QC чек-листа
        $qcChecklist = Checklist::where('type', 'qc')->first();
        if ($qcChecklist) {
            ChecklistResult::create([
                'ticket_id' => $ticket13->id,
                'checklist_id' => $qcChecklist->id,
                'user_id' => $techMoscow->id,
                'answers_json' => json_encode([
                    'Все неисправности устранены' => true,
                    'Замененные детали соответствуют заявленным' => true,
                    'Устройство собрано без зазоров и люфтов' => true,
                    'Все винты на месте' => true,
                    'Экран чистый, без отслоений, пузырей и пыли' => true,
                    'Все кнопки нажимаются с правильным кликом' => true,
                    'Все разъемы работают корректно' => true,
                    'Камеры делают четкие снимки, фокус работает' => true,
                ]),
            ]);
        }

        $createTicket(
            stageOrder: 6,
            branch: $branchPiter->id,
            customer: $customers->skip(1)->first(),
            technician: $technicians->skip(1)->first(),
            deviceType: 'Ноутбук',
            deviceBrand: 'ASUS',
            deviceModel: 'ROG Strix G16',
            serialNumber: 'SN-ASR-0014',
            defect: 'Замена клавиатуры после залития. Ремонт завершён, на проверке QC.',
            priority: 'high',
            estimatedCost: 8000,
            laborCost: 3000,
            deadline: now()->addHours(4)->toDateTimeString(),
            historyMinutes: [15, 90, 20, 1440, 180, 15]
        );

        // ================================================================
        // СТАДИЯ 7: Готово к выдаче (2 заявки)
        // ================================================================
        $createTicket(
            stageOrder: 7,
            branch: $branchMoscow->id,
            customer: $customers->first(),
            technician: $techMoscow,
            deviceType: 'Смартфон',
            deviceBrand: 'Samsung',
            deviceModel: 'Galaxy A54',
            serialNumber: 'SN-SMA-0015',
            defect: 'Замена аккумулятора. QC пройден, готово к выдаче клиенту.',
            priority: 'normal',
            estimatedCost: 4000,
            laborCost: 2000,
            deadline: now()->addDays(2)->toDateTimeString(),
            historyMinutes: [20, 60, 15, 0, 90, 30, 10]
        );

        $createTicket(
            stageOrder: 7,
            branch: $branchPiter->id,
            customer: $customers->skip(1)->first(),
            technician: $technicians->skip(1)->first(),
            deviceType: 'Ноутбук',
            deviceBrand: 'Lenovo',
            deviceModel: 'IdeaPad 3',
            serialNumber: 'SN-LNI-0016',
            defect: 'Чистка от пыли, замена термопасты. QC пройден, готово к выдаче.',
            priority: 'low',
            estimatedCost: 3000,
            laborCost: 2000,
            deadline: now()->addDays(2)->toDateTimeString(),
            historyMinutes: [15, 45, 20, 0, 60, 20, 5]
        );

        // ================================================================
        // СТАДИЯ 8: Выдано / Закрыто (3 заявки)
        // ================================================================
        $createTicket(
            stageOrder: 8,
            branch: $branchMoscow->id,
            customer: $customers->first(),
            technician: $techMoscow,
            deviceType: 'Смартфон',
            deviceBrand: 'Apple',
            deviceModel: 'iPhone 11',
            serialNumber: 'SN-APL-0017',
            defect: 'Замена стекла (переклейка). Успешно отремонтировано и выдано клиенту.',
            priority: 'normal',
            estimatedCost: 5000,
            laborCost: 3000,
            deadline: null,
            historyMinutes: [20, 60, 15, 0, 90, 20, 10, 0]
        );

        $createTicket(
            stageOrder: 8,
            branch: $branchMoscow->id,
            customer: $customers->skip(1)->first(),
            technician: $techMoscow,
            deviceType: 'Ноутбук',
            deviceBrand: 'HP',
            deviceModel: 'ENVY x360',
            serialNumber: 'SN-HPE-0018',
            defect: 'Замена петель экрана. Отремонтировано и выдано.',
            priority: 'normal',
            estimatedCost: 6000,
            laborCost: 3500,
            deadline: null,
            historyMinutes: [15, 120, 30, 1440, 180, 25, 15, 0]
        );

        $createTicket(
            stageOrder: 8,
            branch: $branchPiter->id,
            customer: $customers->first(),
            technician: $technicians->skip(1)->first(),
            deviceType: 'Планшет',
            deviceBrand: 'Apple',
            deviceModel: 'iPad Air',
            serialNumber: 'SN-IPA-0019',
            defect: 'Замена разъёма зарядки. Отремонтировано и выдано клиенту.',
            priority: 'high',
            estimatedCost: 4500,
            laborCost: 2500,
            deadline: null,
            historyMinutes: [10, 45, 20, 0, 60, 15, 10, 0]
        );

        $this->command->info('✅ Заявки успешно созданы:');
        $this->command->info('   Приемка и Осмотр: 2 заявки');
        $this->command->info('   Диагностика: 3 заявки');
        $this->command->info('   Согласование с клиентом: 2 заявки (1 с Magic Link)');
        $this->command->info('   Ожидание запчастей: 2 заявки');
        $this->command->info('   В ремонте: 3 заявки');
        $this->command->info('   Контроль качества (QC): 2 заявки (1 с результатом QC)');
        $this->command->info('   Готово к выдаче: 2 заявки');
        $this->command->info('   Выдано / Закрыто: 3 заявки');
        $this->command->info('   ИТОГО: 19 заявок');
    }
}
