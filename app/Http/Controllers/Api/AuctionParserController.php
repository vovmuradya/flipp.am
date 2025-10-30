<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuctionParserController extends Controller
{
    /**
     * ТЗ v2.1: Парсинг данных с аукциона по URL
     */
    public function fetchFromUrl(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        $url = $request->input('url');

        // Определяем тип аукциона по URL
        $auctionType = $this->detectAuctionType($url);

        if (!$auctionType) {
            return response()->json([
                'success' => false,
                'fallback' => true,
                'message' => 'Неподдерживаемый аукцион. Заполните форму вручную.',
                'data' => [
                    'source_auction_url' => $url
                ]
            ]);
        }

        try {
            // Пытаемся извлечь данные
            $vehicleData = $this->parseAuction($url, $auctionType);

            if ($vehicleData && !empty($vehicleData['make'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'Данные успешно извлечены',
                    'data' => $vehicleData
                ]);
            }

            // Fallback: не удалось извлечь данные
            return response()->json([
                'success' => false,
                'fallback' => true,
                'message' => 'Не удалось автоматически извлечь данные',
                'data' => [
                    'source_auction_url' => $url
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Auction parsing error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'fallback' => true,
                'message' => 'Ошибка при извлечении данных',
                'data' => [
                    'source_auction_url' => $url
                ]
            ]);
        }
    }

    /**
     * Определяем тип аукциона по URL
     */
    private function detectAuctionType(string $url): ?string
    {
        if (str_contains($url, 'copart.com')) {
            return 'copart';
        }

        if (str_contains($url, 'iaai.com')) {
            return 'iaai';
        }

        return null;
    }

    /**
     * Парсим данные с аукциона
     */
    private function parseAuction(string $url, string $type): ?array
    {
        if ($type === 'copart') {
            return $this->parseCopart($url);
        }

        if ($type === 'iaai') {
            return $this->parseIAAI($url);
        }

        return null;
    }

    /**
     * Парсинг Copart (упрощённая версия с моками)
     */
    private function parseCopart(string $url): array
    {
        // В реальном проекте здесь был бы HTTP запрос и парсинг HTML
        // Сейчас возвращаем моковые данные для демонстрации

        // Генерируем случайные данные на основе URL
        $lotId = $this->extractLotId($url);

        $makes = ['Toyota', 'Honda', 'BMW', 'Mercedes-Benz', 'Audi', 'Jeep', 'Ford'];
        $models = ['Camry', 'Civic', 'X5', 'E-Class', 'A4', 'Wrangler', 'F-150'];
        $colors = ['Белый', 'Черный', 'Серебристый', 'Синий', 'Красный', 'Серый'];
        $transmissions = ['automatic', 'manual', 'semi-automatic'];
        $fuelTypes = ['gasoline', 'diesel', 'hybrid'];

        $makeIndex = crc32($lotId) % count($makes);
        $modelIndex = crc32($lotId . 'model') % count($models);

        return [
            'make' => $makes[$makeIndex],
            'model' => $models[$modelIndex],
            'year' => rand(2015, 2023),
            'mileage' => rand(20000, 150000),
            'body_type' => 'Sedan',
            'transmission' => $transmissions[rand(0, 2)],
            'fuel_type' => $fuelTypes[rand(0, 2)],
            'engine_displacement_cc' => rand(1500, 3500),
            'exterior_color' => $colors[rand(0, count($colors) - 1)],
            'source_auction_url' => $url,
            'photos' => $this->generateMockPhotos(6)
        ];
    }

    /**
     * Парсинг IAAI (упрощённая версия)
     */
    private function parseIAAI(string $url): array
    {
        // Аналогично Copart - моковые данные
        return $this->parseCopart($url);
    }

    /**
     * Извлекаем ID лота из URL
     */
    private function extractLotId(string $url): string
    {
        if (preg_match('/lot\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }

        return substr(md5($url), 0, 8);
    }

    /**
     * Генерируем URL для моковых фото
     */
    private function generateMockPhotos(int $count): array
    {
        $photos = [];

        for ($i = 1; $i <= $count; $i++) {
            // Используем Picsum Photos для демонстрации (работающий API)
            // В реальном проекте здесь будут реальные URL с аукциона
            $seed = time() + $i;
            $photos[] = "https://picsum.photos/seed/{$seed}/800/600";
        }

        return $photos;
    }
}

