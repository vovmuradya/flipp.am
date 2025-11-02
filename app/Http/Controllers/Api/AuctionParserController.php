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
     * Парсинг Copart (возвращает пустые значения, ожидая реальной реализации)
     */
    private function parseCopart(string $url): array
    {
        // ВНИМАНИЕ: Здесь должна быть ваша реальная логика парсинга с помощью Http::get() и DOM-парсера.
        // Сейчас возвращаем пустую структуру, чтобы не использовать фиксированные моковые данные.

        return [
            'make' => null,
            'model' => null,
            'year' => null,
            'mileage' => null,
            'body_type' => null,
            'transmission' => null,
            'fuel_type' => null,
            'engine_displacement_cc' => null,
            'exterior_color' => null,
            'source_auction_url' => $url,
            'photos' => $this->generateMockPhotos(0) // Вернет пустой массив
        ];
    }

    /**
     * Парсинг IAAI (упрощённая версия)
     */
    private function parseIAAI(string $url): array
    {
        // Аналогично Copart - возвращаем пустую структуру
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
     * Генерируем URL для моковых фото (теперь возвращает пустой массив)
     */
    private function generateMockPhotos(int $count): array
    {
        // В реальном проекте здесь будут реальные URL с аукциона.
        return [];
    }
}
