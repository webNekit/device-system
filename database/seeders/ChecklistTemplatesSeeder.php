<?php

namespace Database\Seeders;

use App\Domain\Ticketing\Models\ChecklistTemplate;
use App\Domain\Ticketing\Models\ChecklistTemplateItem;
use App\Domain\Ticketing\Models\DeviceType;
use App\Domain\Ticketing\Models\PipelineStage;
use Illuminate\Database\Seeder;

class ChecklistTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        // Получаем этапы
        $intakeStage = PipelineStage::where('order_column', 1)->first();
        $diagnosticsStage = PipelineStage::where('order_column', 2)->first();
        $qcStage = PipelineStage::where('order_column', 6)->first();
        $outputStage = PipelineStage::where('order_column', 7)->first();

        // Получаем типы устройств
        $smartphoneType = DeviceType::where('name', 'Смартфон')->first();
        $tabletType = DeviceType::where('name', 'Планшет')->first();
        $laptopType = DeviceType::where('name', 'Ноутбук')->first();

        // ============================================
        // ШАБЛОН: ДИАГНОСТИКА СМАРТФОНОВ
        // ============================================
        $diagnosticsSmartphone = ChecklistTemplate::create([
            'name' => 'Диагностика смартфона',
            'type' => 'diagnostics',
            'stage_id' => $diagnosticsStage?->id,
            'device_type_id' => $smartphoneType?->id,
            'description' => 'Полная диагностика смартфонов',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->createTemplateItems($diagnosticsSmartphone, [
            ['question' => 'Внешний осмотр на наличие скрытых повреждений', 'field_type' => 'checkbox'],
            ['question' => 'Разборка устройства (при необходимости)', 'field_type' => 'checkbox'],
            ['question' => 'Проверка дисплея: битые пиксели, сенсор, шлейфы', 'field_type' => 'checkbox'],
            ['question' => 'Диагностика аккумулятора (емкость, износ)', 'field_type' => 'checkbox'],
            ['question' => 'Проверка разъемов (зарядка, наушники, SIM)', 'field_type' => 'checkbox'],
            ['question' => 'Тест камер (основная, фронтальная, фокус)', 'field_type' => 'checkbox'],
            ['question' => 'Проверка динамиков и микрофона', 'field_type' => 'checkbox'],
            ['question' => 'Тест Wi-Fi и Bluetooth', 'field_type' => 'checkbox'],
            ['question' => 'Проверка сотовой связи (все SIM)', 'field_type' => 'checkbox'],
            ['question' => 'Диагностика платы на КЗ', 'field_type' => 'checkbox'],
            ['question' => 'Проверка FaceID / TouchID', 'field_type' => 'checkbox'],
            ['question' => 'Комментарий инженера', 'field_type' => 'text', 'required' => false],
        ]);

        // ============================================
        // ШАБЛОН: ДИАГНОСТИКА ПЛАНШЕТОВ
        // ============================================
        $diagnosticsTablet = ChecklistTemplate::create([
            'name' => 'Диагностика планшета',
            'type' => 'diagnostics',
            'stage_id' => $diagnosticsStage?->id,
            'device_type_id' => $tabletType?->id,
            'description' => 'Полная диагностика планшетов',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $this->createTemplateItems($diagnosticsTablet, [
            ['question' => 'Внешний осмотр корпуса и экрана', 'field_type' => 'checkbox'],
            ['question' => 'Проверка дисплея: сенсор, пятна, битые пиксели', 'field_type' => 'checkbox'],
            ['question' => 'Диагностика аккумулятора', 'field_type' => 'checkbox'],
            ['question' => 'Проверка разъема зарядки', 'field_type' => 'checkbox'],
            ['question' => 'Тест Wi-Fi / Bluetooth / GPS', 'field_type' => 'checkbox'],
            ['question' => 'Проверка камер', 'field_type' => 'checkbox'],
            ['question' => 'Проверка динамиков и микрофона', 'field_type' => 'checkbox'],
            ['question' => 'Проверка кнопок управления', 'field_type' => 'checkbox'],
            ['question' => 'Комментарий инженера', 'field_type' => 'text', 'required' => false],
        ]);

        // ============================================
        // ШАБЛОН: ДИАГНОСТИКА НОУТБУКОВ
        // ============================================
        $diagnosticsLaptop = ChecklistTemplate::create([
            'name' => 'Диагностика ноутбука',
            'type' => 'diagnostics',
            'stage_id' => $diagnosticsStage?->id,
            'device_type_id' => $laptopType?->id,
            'description' => 'Полная диагностика ноутбуков',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        $this->createTemplateItems($diagnosticsLaptop, [
            ['question' => 'Внешний осмотр корпуса и матрицы', 'field_type' => 'checkbox'],
            ['question' => 'Проверка клавиатуры и тачпада', 'field_type' => 'checkbox'],
            ['question' => 'Диагностика аккумулятора и БП', 'field_type' => 'checkbox'],
            ['question' => 'Проверка всех портов (USB, HDMI, и т.д.)', 'field_type' => 'checkbox'],
            ['question' => 'Тест Wi-Fi и Bluetooth', 'field_type' => 'checkbox'],
            ['question' => 'Проверка веб-камеры и микрофона', 'field_type' => 'checkbox'],
            ['question' => 'Диагностика системы охлаждения', 'field_type' => 'checkbox'],
            ['question' => 'Проверка жесткого диска / SSD', 'field_type' => 'checkbox'],
            ['question' => 'Тест оперативной памяти', 'field_type' => 'checkbox'],
            ['question' => 'Комментарий инженера', 'field_type' => 'text', 'required' => false],
        ]);

        // ============================================
        // ШАБЛОН: КОНТРОЛЬ КАЧЕСТВА (QC) - УНИВЕРСАЛЬНЫЙ
        // ============================================
        $qcUniversal = ChecklistTemplate::create([
            'name' => 'Контроль качества ремонта',
            'type' => 'qc',
            'stage_id' => $qcStage?->id,
            'device_type_id' => null, // Для всех типов
            'description' => 'Финальная проверка качества после ремонта',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        $this->createTemplateItems($qcUniversal, [
            ['question' => 'Все неисправности устранены', 'field_type' => 'checkbox'],
            ['question' => 'Замененные детали соответствуют заявленным', 'field_type' => 'checkbox'],
            ['question' => 'Устройство собрано без зазоров и люфтов', 'field_type' => 'checkbox'],
            ['question' => 'Все винты на месте', 'field_type' => 'checkbox'],
            ['question' => 'Экран/матрица чистые, без пыли', 'field_type' => 'checkbox'],
            ['question' => 'Все кнопки/порты работают', 'field_type' => 'checkbox'],
            ['question' => 'Тест основных функций пройден', 'field_type' => 'checkbox'],
            ['question' => 'Устройство протерто и упаковано', 'field_type' => 'checkbox'],
            ['question' => 'Гарантийный талон заполнен', 'field_type' => 'checkbox'],
            ['question' => 'Комментарий контролера', 'field_type' => 'text', 'required' => false],
        ]);

        $this->command->info('✅ Шаблоны чек-листов успешно созданы!');
    }

    private function createTemplateItems(ChecklistTemplate $template, array $items): void
    {
        foreach ($items as $index => $itemData) {
            ChecklistTemplateItem::create([
                'template_id' => $template->id,
                'question' => $itemData['question'],
                'field_type' => $itemData['field_type'] ?? 'checkbox',
                'sort_order' => $index,
                'is_required' => $itemData['required'] ?? true,
            ]);
        }
    }
}
