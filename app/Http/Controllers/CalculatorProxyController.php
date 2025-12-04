<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\AuctionParserService;

class CalculatorProxyController extends Controller
{
    public function __construct(
        private readonly AuctionParserService $auctionParser
    ) {}

    /**
     * Прокси до внешнего калькулятора доставки/растаможки.
     */
    public function calculate(Request $request)
    {
        $copartLink = $request->string('copart_link')->trim();

        $bool = static function ($value, $default = true): bool {
            if ($value === null) {
                return (bool) $default;
            }
            if (is_bool($value)) {
                return $value;
            }
            $v = strtolower((string) $value);
            return in_array($v, ['1', 'true', 'yes', 'on'], true);
        };

        $int = static function ($value, $default = 0): int {
            return is_numeric($value) ? (int) $value : (int) $default;
        };

        $float = static function ($value, $default = 0): float {
            return is_numeric($value) ? (float) $value : (float) $default;
        };

        // Базовые значения по умолчанию
        $payload = [
            'price' => $float($request->input('price'), null),
            'auction_location_id' => $int($request->input('auction_location_id'), 4),
            'auction' => $request->input('auction', 'copart'),
            'motor' => $int($request->input('motor'), 1), // 0/1
            'auto_type' => $request->input('auto_type', 'sedan'),
            'year' => $int($request->input('year'), 3), // возраст
            'year2' => $int($request->input('year2'), (int)date('Y') - 3),
            'auction_fee' => $float($request->input('auction_fee'), 0),
            'engine_volume' => $float($request->input('engine_volume'), 2000),
            'insurance' => $bool($request->input('insurance'), true),
            'auction_location' => $float($request->input('auction_location'), 2475),
        ];

        // Парсим Copart для автозаполнения
        if ($copartLink !== '') {
            try {
                $parsed = $this->auctionParser->parseFromUrl($copartLink, aggressive: false);
            } catch (\Throwable $e) {
                $parsed = null;
            }

            if ($parsed) {
                if (!empty($parsed['buy_now_price']) && empty($payload['price'])) {
                    $payload['price'] = (float) $parsed['buy_now_price'];
                }
                if (!empty($parsed['engine_displacement_cc'])) {
                    $payload['engine_volume'] = (int) $parsed['engine_displacement_cc'];
                }
                if (!empty($parsed['fuel_type'])) {
                    $fuel = strtolower((string) $parsed['fuel_type']);
                    $payload['motor'] = in_array($fuel, ['diesel', 'electric', 'hybrid'], true) ? 1 : 0;
                }
                if (!empty($parsed['year']) && is_numeric($parsed['year'])) {
                    $payload['year2'] = (int) $parsed['year'];
                    $payload['year'] = max(0, (int)date('Y') - (int)$parsed['year']);
                }
                if (!empty($parsed['body_type'])) {
                    $bt = strtolower((string) $parsed['body_type']);
                    $mapBody = [
                        'suv' => 'big_suv',
                        'crossover' => 'big_suv',
                        'truck' => 'truck',
                        'pickup' => 'truck',
                        'sedan' => 'sedan',
                        'coupe' => 'sedan',
                        'hatchback' => 'sedan',
                        'wagon' => 'sedan',
                        'motorcycle' => 'motorcycle',
                    ];
                    $payload['auto_type'] = $mapBody[$bt] ?? $payload['auto_type'];
                }
            }
        }

        // Фолбэки, чтобы не отдавать в API пустые значения
        if (empty($payload['engine_volume']) || $payload['engine_volume'] <= 0) {
            $payload['engine_volume'] = 2000;
        }
        if (empty($payload['year2']) || $payload['year2'] < 1900) {
            $payload['year2'] = (int) date('Y') - 3;
        }
        if ($payload['year'] < 0) {
            $payload['year'] = 0;
        }
        if ($payload['price'] === null || $payload['price'] === '' || !is_numeric($payload['price'])) {
            return response()->json(['error' => __('Цена не задана')], 422);
        }
        if ($payload['auction_location'] === null) {
            $payload['auction_location'] = 2475;
        }

        // Валидация итогового payload (ключи как в ТЗ)
        $validator = validator($payload, [
            'price' => ['required', 'numeric'],
            'auction_location_id' => ['required', 'integer'],
            'auction' => ['required', 'string'],
            'motor' => ['required', 'integer'],
            'auto_type' => ['required', 'string'],
            'year' => ['required', 'integer'],
            'year2' => ['required', 'integer'],
            'auction_fee' => ['nullable', 'numeric'],
            'engine_volume' => ['required', 'numeric'],
            'insurance' => ['required', 'boolean'],
            'auction_location' => ['nullable', 'numeric'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $payload = $validator->validated();

        try {
            $response = Http::asForm()
                ->withHeaders([
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Accept' => 'application/json',
                    'Referer' => 'https://www.iaa.am/calculators',
                    'Origin' => 'https://www.iaa.am',
                ])
                ->post('https://www.iaa.am/calculator-store-new', $payload);
        } catch (\Throwable $e) {
            \Log::error('CalculatorProxyController: external call failed', [
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => __('Failed to connect to calculator service'), 'detail' => $e->getMessage()], 500);
        }

        if (!$response->ok()) {
            return response()->json([
                'error' => __('Calculation service error'),
                'status' => $response->status(),
                'body' => $response->body(),
            ], 502);
        }
        if (stripos($response->header('content-type', ''), 'application/json') === false) {
            return response()->json(['error' => __('Invalid response from calculator service'), 'body' => $response->body()], 500);
        }

        $data = $response->json();
        if (!is_array($data)) {
            return response()->json(['error' => __('Unexpected data format from calculator')], 500);
        }

        return response()->json($data);
    }
}
