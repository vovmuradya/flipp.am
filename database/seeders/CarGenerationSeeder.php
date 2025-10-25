<?php

namespace Database\Seeders;

use App\Models\CarGeneration;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CarGenerationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Очистка
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('car_generations')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $generationsFile = database_path('seeders/data/generations.csv');

        if (!file_exists($generationsFile)) {
            $this->command->warn('Файл generations.csv не найден! Пропуск.');
            return;
        }

        $this->command->info('Импорт Поколений...');
        $file = fopen($generationsFile, 'r');
        fgetcsv($file); // Skip header
        $count = 0; $skipped = 0;
        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file)) !== FALSE) {
                if (count($row) < 7) { $skipped++; continue; }

                $modelIdValue = $row[1];
                if (!is_numeric($modelIdValue)) { $skipped++; continue; }
                $model_id = (int)$modelIdValue;

                $name = $row[2];
                $body_code = trim($row[3] . ' ' . $row[4]);
                $years_range = $row[6];

                $year_start = null; $year_end = null;
                if (preg_match('/(\d{4})\s*-\s*(\d{4}|\w+\.?\w*\.?)/u', $years_range, $matches)) {
                    $year_start = (int)$matches[1];
                    if (is_numeric($matches[2])) { $year_end = (int)$matches[2]; }
                } elseif (preg_match('/(\d{4})/u', $years_range, $matches)) {
                    $year_start = (int)$matches[1];
                }

                CarGeneration::create([ // <-- Используем Eloquent
                    'car_model_id' => $model_id,
                    'name' => $name, // <-- ИСПРАВЛЕНО
                    'body_code' => !empty($body_code) ? $body_code : null,
                    'year_start' => $year_start,
                    'year_end' => $year_end,
                ]);
                $count++;
            }
            fclose($file); DB::commit();
            $this->command->info("✅ Поколения: {$count} записей.");
            if ($skipped > 0) { $this->command->warn("   -> Пропущено строк: {$skipped}"); }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Ошибка импорта Поколений: ' . $e->getMessage());
            if (isset($row)) {
                $rowData = implode(', ', $row);
                $this->command->error("Последняя строка CSV: [{$rowData}]");
            }
        }
    }
}
