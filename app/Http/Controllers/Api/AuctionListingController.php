<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuctionParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuctionListingController extends Controller
{
    protected $parserService;

    public function __construct(AuctionParserService $parserService)
    {
        $this->parserService = $parserService;
    }

    /**
     * Получить данные автомобиля с аукциона по URL
     */
    public function fetchFromUrl(Request $request)
    {
        $request->validate(['url' => 'required|url']);
        $url = $request->input('url');

        try {
            // Используем готовый сервис парсинга
            $vehicleData = $this->parserService->parseFromUrl($url);

            if (!$vehicleData) {
                return response()->json([
                    'success' => false,
                    'fallback' => true,
                    'message' => 'Не удалось автоматически извлечь данные',
                    'data' => ['source_auction_url' => $url]
                ]);
            }

            // Формируем описание только с заполненными полями
            $descriptionParts = ["Автомобиль с аукциона", "", "Характеристики:"];

            if (!empty($vehicleData['make'])) {
                $descriptionParts[] = "• Марка: " . $vehicleData['make'];
            }

            if (!empty($vehicleData['model'])) {
                $descriptionParts[] = "• Модель: " . $vehicleData['model'];
            }

            if (!empty($vehicleData['year'])) {
                $descriptionParts[] = "• Год: " . $vehicleData['year'];
            }

            if (!empty($vehicleData['mileage'])) {
                $descriptionParts[] = "• Пробег: " . number_format($vehicleData['mileage'], 0, '', ' ') . " км";
            }

            if (!empty($vehicleData['exterior_color']) && $vehicleData['exterior_color'] !== 'Неизвестно') {
                $descriptionParts[] = "• Цвет: " . $vehicleData['exterior_color'];
            }

            if (!empty($vehicleData['engine_displacement_cc'])) {
                $engineLiters = number_format($vehicleData['engine_displacement_cc'] / 1000, 1);
                $descriptionParts[] = "• Объем двигателя: " . $engineLiters . "L (" . number_format($vehicleData['engine_displacement_cc'], 0, '', ' ') . " куб. см)";
            }

            // Формируем заголовок
            $titleParts = array_filter([
                $vehicleData['year'] ?? null,
                $vehicleData['make'] ?? null,
                $vehicleData['model'] ?? null
            ]);

            $auctionData = [
                'title' => implode(' ', $titleParts),
                'description' => implode("\n", $descriptionParts),
                'price' => null, // Цену указывает дилер
                'category_id' => 1, // Транспорт
                'auction_url' => $url,
                'vehicle' => $vehicleData,
                'photos' => $vehicleData['photos'] ?? [],
            ];

            // Сохраняем в сессию для передачи на страницу создания
            session(['auction_vehicle_data' => $auctionData]);

            return response()->json([
                'success' => true,
                'data' => $auctionData,
                'message' => 'Данные успешно получены'
            ]);

        } catch (\Exception $e) {
            Log::error('AuctionListingController error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении данных: ' . $e->getMessage()
            ], 500);
        }
    }
}

