<?php
namespace Database\Seeders;
use App\Models\CarModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CarModelSeeder extends Seeder {
    public function run(): void {
        $modelsFile = database_path('seeders/data/models.csv');
        if (!file_exists($modelsFile)) { $this->command->error('Файл models.csv не найден!'); return; }
        $this->command->info('Импорт Моделей...');
        $file = fopen($modelsFile, 'r');
        fgetcsv($file); // Skip header
        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file)) !== FALSE) {
                if (count($row) < 3) continue;
                $id = (int)$row[0]; $brand_id = (int)$row[1]; $name = $row[2];
                $isRussian = preg_match('/[А-Яа-яЁё]/u', $name);
                // Используем create чтобы ID присваивался автоматически MySQL
                CarModel::create([
                    'car_brand_id' => $brand_id,
                    'nhtsa_id' => $id, // Используем ID из CSV как nhtsa_id
                    'name_ru' => $isRussian ? $name : null,
                    'name_en' => $isRussian ? Str::slug($name, '-', 'ru') : $name,
                ]);
            }
            fclose($file); DB::commit(); $this->command->info('✅ Модели успешно импортированы.');
        } catch (\Exception $e) { DB::rollBack(); $this->command->error('Ошибка импорта Моделей: ' . $e->getMessage()); }
    }
}
