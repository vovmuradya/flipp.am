<?php

namespace Database\Seeders;

use App\Models\CarGeneration; // Убедитесь, что модель импортирована
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CarGenerationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Очистка (оставляем)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('car_generations')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $generationsFile = database_path('seeders/data/generations.csv');

        if (!file_exists($generationsFile)) {
            $this->command->warn('Файл generations.csv не найден! Пропуск импорта поколений.');
            return;
        }

        $this->command->info('Импорт Поколений...');

        $file = fopen($generationsFile, 'r');
        $header = fgetcsv($file); // Пропускаем заголовок

        $count = 0;
        $skipped = 0;
        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file)) !== FALSE) {
                // Ожидаем структуру: id, model_id, name, body_code1, body_code2, ..., years_range, ...
                // Убедимся, что есть хотя бы 7 колонок для model_id, name, body_code, years_range
                if (count($row) < 7) {
                    $skipped++;
                    continue;
                }

                // 2. Чтение данных из CSV
                $modelIdValue = $row[1];
                if (!is_numeric($modelIdValue)) { $skipped++; continue; }
                $model_id = (int)$modelIdValue;

                $name = $row[2]; // Название поколения (e.g., "1 поколение") - 3-й столбец
                // Объединяем 4-й и 5-й столбцы для body_code, убираем лишние пробелы
                $body_code = trim($row[3] . ' ' . $row[4]);
                $years_range = $row[6]; // Диапазон годов (e.g., "11.2024 - н.в.") - 7-й столбец

                // 3. Парсинг годов
                $year_start = null;
                $year_end = null;
                // Ищем паттерн "ГГГГ - ГГГГ" или "ГГГГ - н.в."
                if (preg_match('/(\d{4})\s*-\s*(\d{4}|\w+\.?\w*\.?)/u', $years_range, $matches)) {
                    $year_start = (int)$matches[1];
                    if (is_numeric($matches[2])) {
                        $year_end = (int)$matches[2];
                    }
                } elseif (preg_match('/(\d{4})/u', $years_range, $matches)) { // Если только один год
                    $year_start = (int)$matches[1];
                }

                // 4. Вставка данных с правильными именами колонок
                CarGeneration::create([
                    'car_model_id' => $model_id,
                    'name' => $name, // ✅ ИСПРАВЛЕНО: Используем 'name' вместо 'name_ru'
                    'body_code' => !empty($body_code) ? $body_code : null,
                    'year_start' => $year_start,
                    'year_end' => $year_end,
                ]);
                $count++;
            }

            fclose($file);
            DB::commit();
            $this->command->info("✅ Поколения успешно импортированы: {$count} записей.");
            if ($skipped > 0) {
                $this->command->warn("   -> Пропущено строк из-за некорректных данных: {$skipped}");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Ошибка импорта Поколений: ' . $e->getMessage());
            if (isset($row)) {
                $rowData = implode(', ', $row);
                $this->command->error("Последняя обработанная строка CSV: [{$rowData}]");
            }
        }
    }
}
