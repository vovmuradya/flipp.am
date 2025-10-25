<?php
namespace Database\Seeders;
use App\Models\CarBrand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CarBrandSeeder extends Seeder {
    public function run(): void {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('car_generations')->truncate();
        DB::table('car_models')->truncate();
        DB::table('car_brands')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $brandsFile = database_path('seeders/data/brands.csv');
        if (!file_exists($brandsFile)) { $this->command->error('Файл brands.csv не найден!'); return; }
        $this->command->info('Импорт Марок...');
        $file = fopen($brandsFile, 'r');
        fgetcsv($file); // Skip header
        DB::beginTransaction();
        try {
            while (($row = fgetcsv($file)) !== FALSE) {
                if (count($row) < 2) continue;
                $id = (int)$row[0]; $name = $row[1];
                $isRussian = preg_match('/[А-Яа-яЁё]/u', $name);
                DB::table('car_brands')->insert([
                    'id' => $id, 'nhtsa_id' => $id,
                    'name_ru' => $isRussian ? $name : null,
                    'name_en' => $isRussian ? Str::slug($name, '-', 'ru') : $name,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            fclose($file); DB::commit(); $this->command->info('✅ Марки успешно импортированы.');
        } catch (\Exception $e) { DB::rollBack(); $this->command->error('Ошибка импорта Марок: ' . $e->getMessage()); }
    }
}
