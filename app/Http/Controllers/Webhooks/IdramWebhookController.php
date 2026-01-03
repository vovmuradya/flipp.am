<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\IdramImIDService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IdramWebhookController extends Controller
{
    protected IdramImIDService $idramService;

    public function __construct(IdramImIDService $idramService)
    {
        $this->idramService = $idramService;
    }

    /**
     * Обрабатывает вебхук от Idram
     */
    public function handle(Request $request): Response
    {
        // Проверяем подпись (в реальном приложении)
        if (!$this->verifySignature($request)) {
            return response('Unauthorized', 401);
        }

        try {
            $data = $request->all();
            
            // Обрабатываем вебхук
            $result = $this->idramService->handleIdramWebhook($data);
            
            if ($result) {
                return response('OK', 200);
            } else {
                return response('Error processing webhook', 500);
            }
        } catch (\Exception $e) {
            \Log::error('Idram webhook error: ' . $e->getMessage());
            return response('Error', 500);
        }
    }

    /**
     * Проверяет подпись запроса
     */
    protected function verifySignature(Request $request): bool
    {
        // В реальном приложении здесь будет проверка подписи
        // В зависимости от требований безопасности Idram
        return true; // Пока для тестирования
    }
}