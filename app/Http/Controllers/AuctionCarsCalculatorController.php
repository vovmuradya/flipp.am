<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\TariffService;

class AuctionCarsCalculatorController extends Controller
{
    /**
     * Проксирует расчёт в скрытый AJAX API auctioncars.am.
     */
    public function calculate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'price' => 'required',
            'auction' => 'required|string',
            'state' => 'required|string',
            'engine' => 'required|string',
            'year' => 'required',
            'engine_volume' => 'required',
            'type-of-transport' => 'required|string',
            'body_type' => 'required|string',
        ]);

        $transFee = TariffService::getPrice($data['state']);
        if ($transFee === null) {
            return response()->json([
                'error' => 'Tariff not found for state '.$data['state'],
            ], 422);
        }

        // обязательные фиксированные поля
        $payload = [
            'action' => 'calc_import_cost_action',
            'price' => $data['price'],
            'auction' => $data['auction'],
            'state' => $data['state'],
            'trans_fee' => $transFee,
            'engine' => $data['engine'],
            'year' => $data['year'],
            'engine_volume' => $data['engine_volume'],
            'type-of-transport' => $data['type-of-transport'],
            'calc_type' => 'irav',
            'body_type' => $data['body_type'],
            'page_id' => 27,
        ];

        try {
            $response = Http::asForm()
                ->withHeaders([
                    'Accept' => 'application/json, text/javascript, */*; q=0.01',
                    'Origin' => 'https://auctioncars.am',
                    'Referer' => 'https://auctioncars.am/calculators',
                    'User-Agent' => 'Mozilla/5.0',
                    'X-Requested-With' => 'XMLHttpRequest',
                ])
                ->post('https://auctioncars.am/wp-admin/admin-ajax.php', $payload);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Failed to connect to auctioncars.am',
                'detail' => $e->getMessage(),
            ], 502);
        }

        if (!$response->ok()) {
            Log::warning('auctioncars.am calc request failed', [
                'payload' => $payload,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return response()->json([
                'error' => 'Calculation service error',
                'status' => $response->status(),
                'body' => $response->body(),
            ], $response->status() ?: 502);
        }

        $json = $response->json();
        if (!is_array($json)) {
            Log::warning('auctioncars.am invalid JSON', ['payload' => $payload, 'body' => $response->body()]);
            return response()->json([
                'error' => 'Invalid JSON response',
                'body' => $response->body(),
            ], 502);
        }

        Log::info('auctioncars.am calc ok', [
            'payload' => $payload,
            'status' => $response->status(),
            'success' => $json['success'] ?? null,
            'has_data' => array_key_exists('data', $json),
        ]);

        return response()->json($json, $response->status());
    }
}
