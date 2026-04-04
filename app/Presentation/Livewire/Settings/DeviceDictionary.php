<?php

namespace App\Presentation\Livewire\Settings;

use App\Domain\Ticketing\Models\DeviceBrand;
use App\Domain\Ticketing\Models\DeviceModel;
use App\Domain\Ticketing\Models\DeviceType;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Справочник: Устройства')]
class DeviceDictionary extends Component
{
    use WithFileUploads;

    public $file; // Файл для загрузки

    public bool $clearBeforeImport = false; // Очищать базу перед импортом

    public string $successMessage = '';

    public string $errorMessage = '';

    public function import()
    {
        $this->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        try {
            $path = $this->file->getRealPath();
            $rawContent = file_get_contents($path);

            // 1. Проверяем первые байты для определения кодировки
            $firstBytes = substr($rawContent, 0, 4);
            $content = $rawContent;

            // UTF-8 BOM
            if (substr($firstBytes, 0, 3) === "\xEF\xBB\xBF") {
                $content = substr($rawContent, 3);
            }
            // UTF-16 LE BOM (Excel часто сохраняет в этом формате)
            elseif (substr($firstBytes, 0, 2) === "\xFF\xFE") {
                $content = mb_convert_encoding($rawContent, 'UTF-8', 'UTF-16LE');
            }
            // UTF-16 BE BOM
            elseif (substr($firstBytes, 0, 2) === "\xFE\xFF") {
                $content = mb_convert_encoding($rawContent, 'UTF-8', 'UTF-16BE');
            }
            // UTF-32 BOM
            elseif (substr($firstBytes, 0, 4) === "\xFF\xFE\x00\x00") {
                $content = mb_convert_encoding($rawContent, 'UTF-8', 'UTF-32');
            } else {
                // Определяем кодировку без BOM
                $encoding = mb_detect_encoding($content, ['UTF-8', 'Windows-1251', 'KOI8-R', 'ISO-8859-5', 'UTF-16LE', 'UTF-16BE'], true);
                if ($encoding && $encoding !== 'UTF-8') {
                    $content = mb_convert_encoding($content, 'UTF-8', $encoding);
                } elseif (! $encoding) {
                    // Если не удалось определить, пробуем Windows-1251
                    $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1251');
                }
            }

            // Пересохраняем файл во временный с правильной кодировкой
            $tempPath = storage_path('app/temp_import.csv');
            file_put_contents($tempPath, $content);

            $fileHandle = fopen($tempPath, 'r');

            // 3. АВТООПРЕДЕЛЕНИЕ РАЗДЕЛИТЕЛЯ (Запятая или Точка с запятой)
            $delimiter = strpos($content, ';') !== false ? ';' : ',';

            DB::beginTransaction();

            // Очищаем базу перед импортом если указано
            if ($this->clearBeforeImport) {
                DeviceModel::truncate();
                DeviceBrand::truncate();
                DeviceType::truncate();
            }

            $rowCounter = 0;
            $firstRow = true;
            while (($row = fgetcsv($fileHandle, 1000, $delimiter)) !== false) {
                // Если строка пустая или это заголовок
                if (count($row) < 3 || mb_strtolower(trim($row[0])) === 'тип' || empty(trim($row[0]))) {
                    continue;
                }

                // 4. Очищаем текст от случайных запятых и пробелов, которые ты оставил в Excel
                // И удаляем любые невидимые символы в начале строки (BOM, нулевые байты)
                $typeName = trim(str_replace(',', '', $row[0]));
                $brandName = trim(str_replace(',', '', $row[1]));
                $modelName = trim(str_replace(',', '', $row[2]));

                // Удаляем невидимые символы (нулевые байты, BOM из середины данных)
                $typeName = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/u', '', $typeName);
                $brandName = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/u', '', $brandName);
                $modelName = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/u', '', $modelName);

                if (empty($typeName) || empty($brandName) || empty($modelName)) {
                    continue; // Пропускаем кривые строки
                }

                $type = DeviceType::firstOrCreate(['name' => $typeName]);
                $brand = DeviceBrand::firstOrCreate(['type_id' => $type->id, 'name' => $brandName]);
                DeviceModel::firstOrCreate(['brand_id' => $brand->id, 'name' => $modelName]);

                $rowCounter++;
            }

            fclose($fileHandle);
            @unlink($tempPath); // Удаляем временный файл
            DB::commit();

            $this->reset('file');
            $this->successMessage = "Успешно загружено/обновлено: {$rowCounter} записей!";
            $this->errorMessage = '';

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->errorMessage = 'Ошибка импорта: '.$e->getMessage();
            $this->successMessage = '';
        }
    }

    public function render()
    {
        // Подгружаем для вывода на экран текущую базу
        $types = DeviceType::with(['brands.models'])->get();

        return view('livewire.settings.device-dictionary', compact('types'));
    }
}
