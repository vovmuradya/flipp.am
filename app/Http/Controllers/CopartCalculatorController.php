<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class CopartCalculatorController extends Controller
{
    /**
        * Принимает payload с фронта и проксирует его в скрытый калькулятор iaa.am.
        */
    public function calculate(Request $request): JsonResponse
    {
        // Валидация входа и приведение типов (минимально, без трансформации структуры).
        $data = $request->validate([
            'price' => 'required',
            'auction_location_id' => 'required',
            // делаем поле опциональным, но ниже выставляем дефолт "copart"
            'auction' => 'sometimes|string',
            'motor' => 'required',
            'auto_type' => 'required|string',
            'year' => 'required',
            'year2' => 'required',
            'auction_fee' => 'nullable',
            'engine_volume' => 'required',
            'insurance' => 'required',
            'auction_location' => 'nullable',
        ]);

        // подстраховываем: если не пришёл auction, отправляем "copart"
        $data['auction'] = $data['auction'] ?? 'copart';

        // Формируем тело запроса к внешнему API
        $payload = $data;

        try {
            // 1) Получаем страницу калькулятора, чтобы взять сессию + CSRF токен
            $pageResponse = Http::withHeaders([
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'User-Agent' => 'Mozilla/5.0',
            ])->get('https://www.iaa.am/calculators');

            $csrfToken = null;
            if ($pageResponse->ok()) {
                $body = $pageResponse->body();
                if (preg_match('/name="_token"\\s+value="([^"]+)"/', $body, $m)) {
                    $csrfToken = $m[1];
                } elseif (preg_match('/meta\\s+name="csrf-token"\\s+content="([^"]+)"/i', $body, $m)) {
                    $csrfToken = $m[1];
                }
            }

            // Сбор cookies из первого ответа
            $cookieJar = $pageResponse->cookies();
            $cookies = [];
            foreach ($cookieJar->toArray() as $cookie) {
                if (isset($cookie['Name'], $cookie['Value'])) {
                    $cookies[$cookie['Name']] = $cookie['Value'];
                }
            }

            // 2) Отправляем форму с CSRF и cookies
            $requestBuilder = Http::asForm()
                ->withCookies($cookies, 'www.iaa.am')
                ->withHeaders([
                    'Accept' => 'application/json, text/plain, */*',
                    'User-Agent' => 'Mozilla/5.0',
                    'Referer' => 'https://www.iaa.am/calculators',
                    'X-Requested-With' => 'XMLHttpRequest',
                ]);

            if ($csrfToken) {
                $payload['_token'] = $csrfToken;
                $requestBuilder = $requestBuilder->withHeaders([
                    'X-CSRF-TOKEN' => $csrfToken,
                ]);
            }

            $response = $requestBuilder->post('https://www.iaa.am/calculator-store-new', $payload);
        } catch (\Throwable $e) {
            // Любые сетевые ошибки возвращаем как 502 с текстом
            return response()->json([
                'error' => 'Failed to connect to calculator service',
                'detail' => $e->getMessage(),
            ], 502);
        }

        // Если ответ не OK — пробрасываем код и тело
        if (!$response->ok()) {
            return response()->json([
                'error' => 'Calculation service error',
                'status' => $response->status(),
                'body' => $response->body(),
            ], $response->status() ?: 502);
        }

        // Пытаемся вернуть JSON как есть; если не JSON — вернем как текст
        $json = $response->json();
        if (is_array($json)) {
            return response()->json($json, $response->status());
        }

        return response()->json([
            'error' => 'Invalid JSON response',
            'body' => $response->body(),
        ], 502);
    }
}
