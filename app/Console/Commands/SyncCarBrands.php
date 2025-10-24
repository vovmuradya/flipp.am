<?php

namespace App\Console\Commands;

use App\Models\CarBrand;
use App\Models\CarModel;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException; // <-- ДОБАВЛЕНО
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // <-- ДОБАВЛЕНО
use Illuminate\Support\Str;

class SyncCarBrands extends Command
{
    protected $signature = 'car:sync-brands';
    protected $description = 'Synchronize car brands AND models from NHTSA API.';

    protected const API_URL_MAKES = 'https://vpic.nhtsa.dot.gov/api/vehicles/GetAllMakes?format=json';
    protected const API_URL_MODELS = 'https://vpic.nhtsa.dot.gov/api/vehicles/GetModelsForMakeId/';

    public function handle()
    {
        $this->info('Запуск синхронизации марок автомобилей...');
        // ... (код для загрузки марок остается без изменений) ...
        $this->info("✅ Синхронизировано марок: " . CarBrand::count());


        $this->info('Запуск синхронизации моделей для каждой марки...');
        $allBrands = CarBrand::cursor(); // Используем курсор для экономии памяти
        $totalModelsCount = 0;
        $brandsProcessed = 0;
        $totalBrands = CarBrand::count(); // Получаем общее количество марок

        $progressBar = $this->output->createProgressBar($totalBrands);
        $progressBar->start();

        foreach ($allBrands as $brand) {
            $brandsProcessed++;
            $maxRetries = 3; // Максимум 3 попытки
            $retryDelay = 2; // Пауза 2 секунды перед повтором

            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    $modelsResponse = Http::withoutVerifying()
                        ->timeout(45) // Увеличим таймаут
                        ->get(self::API_URL_MODELS . $brand->nhtsa_id . '?format=json');

                    // Если сервер ответил пустой строкой (ошибка 52), бросаем исключение
                    if ($modelsResponse->body() === '') {
                        throw new ConnectionException('Empty reply from server for brand NHTSA ID: ' . $brand->nhtsa_id);
                    }

                    // Если ответ не успешный (не 2xx), бросаем исключение
                    $modelsResponse->throw(); // Бросит исключение если статус не 2xx

                    $modelsResults = $modelsResponse->json('Results');
                    $modelsToInsert = [];

                    foreach ($modelsResults as $modelData) {
                        $modelsToInsert[] = [
                            'car_brand_id' => $brand->id,
                            'nhtsa_id' => $modelData['Model_ID'],
                            'name_en' => $modelData['Model_Name'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    if (!empty($modelsToInsert)) {
                        CarModel::upsert($modelsToInsert, ['nhtsa_id'], ['name_en', 'car_brand_id', 'updated_at']);
                        $totalModelsCount += count($modelsToInsert);
                    }

                    // Если успешно, выходим из цикла попыток
                    break;

                } catch (\Illuminate\Http\Client\RequestException | ConnectionException $e) {
                    // Ловим ошибки сети или статуса (включая пустой ответ)
                    Log::warning("Ошибка синхронизации моделей для марки {$brand->name_en} (ID: {$brand->nhtsa_id}), попытка {$attempt}/{$maxRetries}: " . $e->getMessage());

                    if ($attempt < $maxRetries) {
                        $this->warn(" Повтор через {$retryDelay} сек...");
                        sleep($retryDelay); // Ждем перед повтором
                    } else {
                        $this->error(" Не удалось загрузить модели для марки {$brand->name_en} после {$maxRetries} попыток.");
                        // Можно добавить логику пропуска этой марки или остановки команды
                    }
                }
            } // Конец цикла попыток

            $progressBar->advance();
            usleep(100000); // Добавляем паузу 100 мс между марками
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("✅ Синхронизировано моделей (всего): " . $totalModelsCount); // Используем уже посчитанное
        $this->warn('⚠️ ВНИМАНИЕ: Для отображения на русском языке заполните столбцы name_ru вручную.');

        return Command::SUCCESS;
    }
}
