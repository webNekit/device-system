<?php

namespace Database\Seeders;

use App\Domain\Ticketing\Models\Checklist;
use App\Domain\Ticketing\Models\ChecklistItem;
use App\Domain\Ticketing\Models\DeviceType;
use App\Domain\Ticketing\Models\PipelineStage;
use Illuminate\Database\Seeder;

class ChecklistSeeder extends Seeder
{
    public function run(): void
    {
        // Получаем этапы
        $intakeStage = PipelineStage::where('order_column', 1)->first(); // Приемка
        $diagnosticsStage = PipelineStage::where('order_column', 2)->first(); // Диагностика
        $qcStage = PipelineStage::where('order_column', 6)->first(); // Контроль качества
        $outputStage = PipelineStage::where('order_column', 7)->first(); // Готово к выдаче

        // Получаем типы устройств
        $smartphoneType = DeviceType::where('name', 'Смартфон')->first();
        $tabletType = DeviceType::where('name', 'Планшет')->first();
        $laptopType = DeviceType::where('name', 'Ноутбук')->first();

        // ============================================
        // ЧЕК-ЛИСТ: ПРИЕМКА (INTAKE)
        // ============================================
        $intakeChecklist = Checklist::updateOrCreate(
            ['type' => 'intake', 'stage_id' => $intakeStage?->id],
            [
                'name' => 'Осмотр при приемке',
                'is_active' => true,
            ]
        );

        $intakeItems = [
            ['question' => 'Внешний осмотр: зафиксировать все повреждения корпуса и экрана', 'sort_order' => 0],
            ['question' => 'Проверка комплектности (зарядное устройство, коробка, документы)', 'sort_order' => 1],
            ['question' => 'Проверка IMEI/серийного номера (совпадение с документами)', 'sort_order' => 2],
            ['question' => 'Проверка функции включения устройства', 'sort_order' => 3],
            ['question' => 'Проверка наличия следов влаги (индикаторы)', 'sort_order' => 4],
            ['question' => 'Проверка работы кнопок (питание, громкость, home)', 'sort_order' => 5],
            ['question' => 'Фотофиксация внешнего вида устройства', 'sort_order' => 6],
            ['question' => 'Заполнение акта приемки с клиентом', 'sort_order' => 7],
        ];

        foreach ($intakeItems as $itemData) {
            ChecklistItem::firstOrCreate(
                ['checklist_id' => $intakeChecklist->id, 'question' => $itemData['question']],
                ['sort_order' => $itemData['sort_order'], 'field_type' => 'checkbox']
            );
        }

        // ============================================
        // ЧЕК-ЛИСТ: ДИАГНОСТИКА (DIAGNOSTICS)
        // ============================================
        $diagnosticsChecklist = Checklist::updateOrCreate(
            ['type' => 'diagnostics', 'stage_id' => $diagnosticsStage?->id],
            [
                'name' => 'Полная диагностика устройства',
                'is_active' => true,
            ]
        );

        $diagnosticsItems = [
            ['question' => 'Внешний осмотр на наличие скрытых повреждений', 'sort_order' => 0],
            ['question' => 'Разборка устройства (при необходимости)', 'sort_order' => 1],
            ['question' => 'Проверка дисплея: битые пиксели, сенсор, шлейфы', 'sort_order' => 2],
            ['question' => 'Диагностика аккумулятора (емкость, износ)', 'sort_order' => 3],
            ['question' => 'Проверка разъемов (зарядка, наушники, SIM)', 'sort_order' => 4],
            ['question' => 'Тест камер (основная, фронтальная, фокус)', 'sort_order' => 5],
            ['question' => 'Проверка динамиков и микрофона (запись/воспроизведение)', 'sort_order' => 6],
            ['question' => 'Тест Wi-Fi и Bluetooth (подключение, стабильность)', 'sort_order' => 7],
            ['question' => 'Проверка сотовой связи (все SIM, сеть, звонок)', 'sort_order' => 8],
            ['question' => 'Диагностика платы на наличие коротких замыканий', 'sort_order' => 9],
            ['question' => 'Проверка FaceID / TouchID', 'sort_order' => 10],
            ['question' => 'Составление дефектной ведомости', 'sort_order' => 11],
            ['question' => 'Расчет стоимости ремонта и согласование с клиентом', 'sort_order' => 12, 'field_type' => 'text'],
        ];

        foreach ($diagnosticsItems as $itemData) {
            ChecklistItem::firstOrCreate(
                ['checklist_id' => $diagnosticsChecklist->id, 'question' => $itemData['question']],
                ['sort_order' => $itemData['sort_order'], 'field_type' => $itemData['field_type'] ?? 'checkbox']
            );
        }

        // ============================================
        // ЧЕК-ЛИСТ: КОНТРОЛЬ КАЧЕСТВА (QC)
        // ============================================
        $qcChecklist = Checklist::updateOrCreate(
            ['type' => 'qc', 'stage_id' => $qcStage?->id],
            [
                'name' => 'Контроль качества ремонта',
                'is_active' => true,
            ]
        );

        $qcItems = [
            ['question' => 'Все неисправности устранены согласно дефектной ведомости', 'sort_order' => 0],
            ['question' => 'Замененные детали соответствуют заявленным', 'sort_order' => 1],
            ['question' => 'Устройство собрано без зазоров и люфтов', 'sort_order' => 2],
            ['question' => 'Все винты на месте и закручены', 'sort_order' => 3],
            ['question' => 'Экран чистый, без отслоений, пузырей и пыли', 'sort_order' => 4],
            ['question' => 'Все кнопки нажимаются с правильным кликом', 'sort_order' => 5],
            ['question' => 'Все разъемы работают корректно', 'sort_order' => 6],
            ['question' => 'Камеры делают четкие снимки, фокус работает', 'sort_order' => 7],
            ['question' => 'Звук в динамиках чистый, без хрипов', 'sort_order' => 8],
            ['question' => 'Микрофон записывает звук четко', 'sort_order' => 9],
            ['question' => 'Wi-Fi и Bluetooth подключаются стабильно', 'sort_order' => 10],
            ['question' => 'Сотовая связь работает (есть сеть, дозвон)', 'sort_order' => 11],
            ['question' => 'Устройство заряжается корректно', 'sort_order' => 12],
            ['question' => 'FaceID / TouchID работает', 'sort_order' => 13],
            ['question' => 'Проведен тест автономности (не менее 30 минут)', 'sort_order' => 14],
            ['question' => 'Устройство протерто от пыли и отпечатков', 'sort_order' => 15],
            ['question' => 'Упаковано в защитную пленку/пакет', 'sort_order' => 16],
            ['question' => 'Заполнен гарантийный талон', 'sort_order' => 17],
        ];

        foreach ($qcItems as $itemData) {
            ChecklistItem::firstOrCreate(
                ['checklist_id' => $qcChecklist->id, 'question' => $itemData['question']],
                ['sort_order' => $itemData['sort_order'], 'field_type' => 'checkbox']
            );
        }

        // ============================================
        // ЧЕК-ЛИСТ: ВЫДАЧА (OUTPUT)
        // ============================================
        $outputChecklist = Checklist::updateOrCreate(
            ['type' => 'output', 'stage_id' => $outputStage?->id],
            [
                'name' => 'Подготовка к выдаче клиенту',
                'is_active' => true,
            ]
        );

        $outputItems = [
            ['question' => 'Акт выполненных работ сформирован и распечатан', 'sort_order' => 0],
            ['question' => 'Гарантийный талон заполнен', 'sort_order' => 1],
            ['question' => 'Устройство упаковано', 'sort_order' => 2],
            ['question' => 'Клиент уведомлен о готовности (SMS/Email/звонок)', 'sort_order' => 3],
            ['question' => 'Оплата проведена в системе', 'sort_order' => 4],
            ['question' => 'Заявка закрыта в CRM', 'sort_order' => 5],
        ];

        foreach ($outputItems as $itemData) {
            ChecklistItem::firstOrCreate(
                ['checklist_id' => $outputChecklist->id, 'question' => $itemData['question']],
                ['sort_order' => $itemData['sort_order'], 'field_type' => 'checkbox']
            );
        }

        $this->command->info('✅ Чек-листы успешно созданы/обновлены!');
        $this->command->info('   - Приемка: '.$intakeChecklist->items->count().' пунктов');
        $this->command->info('   - Диагностика: '.$diagnosticsChecklist->items->count().' пунктов');
        $this->command->info('   - Контроль качества: '.$qcChecklist->items->count().' пунктов');
        $this->command->info('   - Выдача: '.$outputChecklist->items->count().' пунктов');
    }
}
