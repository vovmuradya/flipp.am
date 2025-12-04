<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class ImportCalculatorController extends Controller
{
    /**
     * Проксирует расчёт растаможки через официальный API src.am.
     */
    public function calculateImportCost(Request $request): JsonResponse
    {
        $data = $request->validate([
            'productionDate' => ['required', 'date'], // ISO дата
            'value' => ['required', 'numeric', 'min:0'],
            'volume' => ['required', 'integer', 'min:0'],
            'selectedEngineType' => ['required', 'integer', 'in:1,2,3'],
            'isLegal' => ['required', 'boolean'],
            'offRoad' => ['required', 'boolean'],
            'ICEpower' => ['required', 'integer', 'min:0'],
        ]);

        // Приводим флаги к 0/1, как ждёт внешний сервис.
        $flags = [
            'isLegal' => $data['isLegal'] ? 1 : 0,
            'offRoad' => $data['offRoad'] ? 1 : 0,
        ];

        $query = [
            'productionDate' => $data['productionDate'],
            'value' => $data['value'],
            'volume' => $data['volume'],
            'selectedEngineType' => $data['selectedEngineType'],
            'isLegal' => $flags['isLegal'],
            'offRoad' => $flags['offRoad'],
            'ICEpower' => $data['ICEpower'],
        ];

        try {
            $response = Http::acceptJson()
                ->timeout(15)
                ->get('https://www.src.am/am/showVehiclesSearchResult', $query)
                ->throw();

            return response()->json([
                'ok' => true,
                'data' => $response->json(),
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (RequestException $e) {
            $status = $e->response ? $e->response->status() : 500;
            $body = $e->response ? $e->response->body() : null;
            return response()->json([
                'ok' => false,
                'error' => 'SRC request failed',
                'status' => $status,
                'body' => $body,
            ], 502);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
