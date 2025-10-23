<?php

namespace App\Console\Commands;

use App\Models\CarBrand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SyncCarBrands extends Command
{
    protected $signature = 'car:sync-brands';
    protected $description = 'Synchronize car brands from NHTSA API to the local database.';

    // URL бесплатного API
    protected const API_URL = 'https://vpic.nhtsa.dot.gov/api/vehicles/GetAllMakes?format=json';

    public function handle()
    {
        $this->info('Запуск синхронизации марок автомобилей с NHTSA API...');

        // Проверка подключения к API
        $response = Http::withoutVerifying()->timeout(30)->get(self::API_URL);

        if ($response->failed()) {
            $this->error('Ошибка при получении данных с NHTSA API. Проверьте DNS и сетевое подключение контейнера.');
            // Показываем конкретную причину ошибки
            $this->error('Сообщение cURL: ' . $response->toException()->getMessage());
            return Command::FAILURE;
        }

        $results = $response->json('Results');
        $brandsToInsert = [];

        foreach ($results as $brand) {
            $makeId = $brand['Make_ID'];
            $makeName = Str::title($brand['Make_Name']);

            $brandsToInsert[] = [
                'nhtsa_id' => $makeId,
                'name_en' => $makeName,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Удаляем возможные дубликаты перед вставкой
        $uniqueBrandsToInsert = array_unique($brandsToInsert, SORT_REGULAR);

        // Вставляем или обновляем данные в базу (upsert)
        CarBrand::upsert(
            $uniqueBrandsToInsert,
            ['nhtsa_id'], // Уникальный ключ для проверки
            ['name_en', 'updated_at'] // Поля для обновления
        );

        $this->info("✅ Синхронизировано " . count($uniqueBrandsToInsert) . " марок.");
        $this->warn('⚠️ ВНИМАНИЕ: Для отображения на русском языке заполните столбец name_ru в базе данных CarBrands вручную.');

        return Command::SUCCESS;
    }
}
