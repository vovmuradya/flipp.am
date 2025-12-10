<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ParseAuctionJob;
use App\Models\AuctionParseJob as AuctionParseJobModel;
use App\Services\AuctionParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuctionParserController extends Controller
{
    private const ALLOWED_AUCTION_DOMAINS = [
        'copart.com',
        'list.am',
    ];

    /**
     * ТЗ v2.1: Парсинг данных с аукциона по URL (асинхронный)
     */
    public function fetchFromUrl(Request $request)
    {
        $request->validate([
            'url' => 'required|url'
        ]);

        $url = $request->input('url');

        if (!$this->isAllowedAuctionUrl($url)) {
            return response()->json([
                'success' => false,
                'fallback' => true,
                'message' => 'Поддерживаются только ссылки с Copart или List.am.',
                'data' => [
                    'source_auction_url' => $url
                ]
            ], 422);
        }

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
            // Создаём уникальный ID для задачи
            $jobId = Str::uuid()->toString();
            
            // Сохраняем задачу в БД
            $jobRecord = AuctionParseJobModel::create([
                'job_id' => $jobId,
                'url' => $url,
                'status' => 'pending',
            ]);

            // Запускаем парсинг в фоне
            ParseAuctionJob::dispatch($jobId, $url, $auctionType);

            Log::info("🚀 Parse job created", ['job_id' => $jobId, 'url' => $url]);

            // Возвращаем job_id для отслеживания статуса
            return response()->json([
                'success' => true,
                'message' => 'Парсинг запущен',
                'job_id' => $jobId,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create parse job: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'fallback' => true,
                'message' => 'Ошибка при запуске парсинга',
                'data' => [
                    'source_auction_url' => $url
                ]
            ]);
        }
    }

    /**
     * Проверка статуса парсинга
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'job_id' => 'required|string'
        ]);

        $jobId = $request->input('job_id');
        $jobRecord = AuctionParseJobModel::where('job_id', $jobId)->first();

        if (!$jobRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Задача не найдена'
            ], 404);
        }

        $response = [
            'success' => true,
            'status' => $jobRecord->status,
        ];

        if ($jobRecord->status === 'completed' && $jobRecord->result) {
            $response['data'] = $jobRecord->result;
        }

        if ($jobRecord->status === 'failed') {
            $response['error'] = $jobRecord->error_message;
            $response['fallback'] = true;
            $response['data'] = [
                'source_auction_url' => $jobRecord->url
            ];
        }

        return response()->json($response);
    }

    /**
     * Определяем тип аукциона по URL
     */
    private function detectAuctionType(string $url): ?string
    {
        if (str_contains($url, 'copart.com')) {
            return 'copart';
        }

        if (str_contains($url, 'list.am')) {
            return 'listam';
        }

        return null;
    }

    private function isAllowedAuctionUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return false;
        }

        $host = strtolower($host);

        foreach (self::ALLOWED_AUCTION_DOMAINS as $domain) {
            $domain = strtolower($domain);
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Парсим данные с аукциона
     */
    private function parseAuction(string $url, string $type): ?array
    {
        if ($type === 'copart') {
            return $this->parseCopart($url);
        }

        if ($type === 'listam') {
            return $this->parseListAm($url);
        }

        return null;
    }

    /**
     * Парсинг Copart с использованием AuctionParserService
     */
    private function parseCopart(string $url): array
    {
        try {
            // ✅ Laravel сам создаст и подставит CopartCookieManager
            $service = app(\App\Services\AuctionParserService::class);
            $data = $service->parseFromUrl($url);

            if ($data && !empty($data['make'])) {
                return $data;
            }

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
                'primary_damage' => null,
                'source_auction_url' => $url,
                'photos' => [],
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
                'primary_damage' => null,
                'source_auction_url' => $url,
                'photos' => [],
            ];
        }
    }

    /**
     * Парсинг List.am с использованием AuctionParserService
     */
    private function parseListAm(string $url): array
    {
        try {
            $service = app(\App\Services\AuctionParserService::class);
            $data = $service->parseFromUrl($url, aggressive: false);

            if ($data && !empty($data['make'])) {
                return $data;
            }

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
                'photos' => [],
            ];
        } catch (\Exception $e) {
            Log::error('List.am parsing failed: ' . $e->getMessage());
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
                'photos' => [],
            ];
        }
    }



    /**
     * Парсинг IAAI с использованием AuctionParserService
     */
    private function parseIAAI(string $url): array
    {
        try {
            $service = app(\App\Services\AuctionParserService::class);
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
