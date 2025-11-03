<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuctionParserService;
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
     * Парсинг Copart с использованием AuctionParserService
     */
    private function parseCopart(string $url): array
    {
        try {
            $service = new AuctionParserService();
            $data = $service->parseFromUrl($url);

            if ($data && !empty($data['make'])) {
                return $data;
            }

            // Fallback если парсер вернул пусто
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
                'photos' => []
            ];
        } catch (\Exception $e) {
            Log::error('Copart parsing failed: ' . $e->getMessage());
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
                'photos' => []
            ];
        }
    }

    /**
     * Парсинг IAAI с использованием AuctionParserService
     */
    private function parseIAAI(string $url): array
    {
        try {
            $service = new AuctionParserService();
            $data = $service->parseFromUrl($url);

            if ($data && !empty($data['make'])) {
                return $data;
            }

            // Fallback если парсер вернул пусто
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
                'photos' => []
            ];
        } catch (\Exception $e) {
            Log::error('IAAI parsing failed: ' . $e->getMessage());
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
                'photos' => []
            ];
        }
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

}
