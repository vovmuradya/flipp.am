<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\IdramImIDService;
use App\Models\ImIDUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ImIDWebhookController extends Controller
{
    protected IdramImIDService $imIdService;

    public function __construct(IdramImIDService $imIdService)
    {
        $this->imIdService = $imIdService;
    }

    /**
     * Обрабатывает вебхук от imID
     */
    public function handle(Request $request): Response
    {
        // Проверяем подпись (в реальном приложении)
        if (!$this->verifySignature($request)) {
            return response('Unauthorized', 401);
        }

        try {
            $data = $request->all();
            
            // Обрабатываем вебхук imID
            $result = $this->processImIDWebhook($data);
            
            if ($result) {
                return response('OK', 200);
            } else {
                return response('Error processing webhook', 500);
            }
        } catch (\Exception $e) {
            \Log::error('ImID webhook error: ' . $e->getMessage());
            return response('Error', 500);
        }
    }

    /**
     * Обрабатывает данные вебхука imID
     */
    protected function processImIDWebhook(array $data): bool
    {
        $userId = $data['user_id'] ?? null;
        $status = $data['status'] ?? null;
        $verificationData = $data['verification_data'] ?? [];

        if (!$userId || !$status) {
            return false;
        }

        // Находим пользователя imID в нашей системе
        $imIdUser = ImIDUser::where('imid_user_id', $userId)->first();

        if (!$imIdUser) {
            return false;
        }

        // Обновляем статус верификации
        $imIdUser->update([
            'status' => $status,
            'verification_data' => $verificationData,
            'verified_at' => $status === 'verified' ? now() : null,
        ]);

        return true;
    }

    /**
     * Проверяет подпись запроса
     */
    protected function verifySignature(Request $request): bool
    {
        // В реальном приложении здесь будет проверка подписи
        // В зависимости от требований безопасности imID
        return true; // Пока для тестирования
    }
}