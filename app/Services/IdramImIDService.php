<?php

namespace App\Services;

use App\Models\IdramTransaction;
use App\Models\ImIDUser;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IdramImIDService
{
    protected string $idramApiUrl;
    protected string $idramMerchantId;
    protected string $idramApiKey;
    protected string $imidApiUrl;
    protected string $imidApiKey;

    public function __construct()
    {
        $this->idramApiUrl = config('services.idram.api_url', 'https://api.idram.ee');
        $this->idramMerchantId = config('services.idram.merchant_id');
        $this->idramApiKey = config('services.idram.api_key');
        $this->imidApiUrl = config('services.imid.api_url', 'https://api.imid.am');
        $this->imidApiKey = config('services.imid.api_key');
    }

    /**
     * Создает платеж через Idram
     */
    public function createIdramPayment(float $amount, string $orderId, string $description, ?User $user = null): array
    {
        // Создаем запись о транзакции
        $transaction = IdramTransaction::create([
            'user_id' => $user?->id,
            'order_id' => $orderId,
            'amount' => $amount,
            'currency' => 'AMD',
            'description' => $description,
            'status' => 'pending',
        ]);

        // В реальном приложении здесь будет вызов API Idram для создания платежа
        // Пока возвращаем тестовые данные
        return [
            'success' => true,
            'transaction_id' => $transaction->id,
            'redirect_url' => 'https://test.idram.am/payment/' . $transaction->id,
            'transaction' => $transaction
        ];
    }

    /**
     * Обрабатывает вебхук от Idram
     */
    public function handleIdramWebhook(array $data): bool
    {
        $transactionId = $data['transaction_id'] ?? null;
        $status = $data['status'] ?? null;
        $orderId = $data['order_id'] ?? null;

        if (!$transactionId || !$status) {
            return false;
        }

        $transaction = IdramTransaction::where('transaction_id', $transactionId)->first();

        if (!$transaction) {
            return false;
        }

        // Обновляем статус транзакции
        $transaction->update([
            'status' => $status,
            'response_data' => $data,
            'processed_at' => now(),
        ]);

        // Если транзакция успешна, можно выполнить дополнительные действия
        if ($status === 'completed') {
            $transaction->update([
                'completed_at' => now(),
            ]);

            // Здесь можно выполнить действия после успешной оплаты
            $this->handleSuccessfulPayment($transaction);
        }

        return true;
    }

    /**
     * Обработка успешного платежа
     */
    protected function handleSuccessfulPayment(IdramTransaction $transaction): void
    {
        // В реальном приложении здесь будут действия после успешной оплаты
        // Например, активация подписки, обновление баланса и т.д.
        Log::info('Успешный платеж через Idram', [
            'transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'amount' => $transaction->amount,
            'order_id' => $transaction->order_id
        ]);
    }

    /**
     * Получает информацию о пользователе imID
     */
    public function getImIDUserInfo(string $tokenId): array
    {
        // В реальном приложении здесь будет вызов API imID
        // Пока возвращаем тестовые данные
        return [
            'success' => true,
            'user_data' => [
                'id' => 'imid_' . rand(1000, 9999),
                'first_name' => 'Тестовое Имя',
                'last_name' => 'Тестовая Фамилия',
                'email' => 'test@example.com',
                'phone' => '+37412345678',
                'country' => 'Armenia',
                'city' => 'Yerevan'
            ]
        ];
    }

    /**
     * Проверяет статус верификации пользователя imID
     */
    public function checkImIDVerification(string $tokenId): array
    {
        // В реальном приложении здесь будет вызов API imID для проверки статуса верификации
        // Пока возвращаем тестовые данные
        return [
            'success' => true,
            'status' => 'verified', // pending, verified, rejected
            'verification_data' => [
                'document_type' => 'passport',
                'document_number' => 'AB1234567',
                'verified_at' => now()->toISOString()
            ]
        ];
    }

    /**
     * Создает или обновляет пользователя imID в нашей системе
     */
    public function syncImIDUser(array $userData, ?User $user = null): ?ImIDUser
    {
        $imidUser = ImIDUser::updateOrCreate(
            ['imid_user_id' => $userData['id']],
            [
                'user_id' => $user?->id,
                'first_name' => $userData['first_name'] ?? null,
                'last_name' => $userData['last_name'] ?? null,
                'email' => $userData['email'] ?? null,
                'phone' => $userData['phone'] ?? null,
                'country' => $userData['country'] ?? null,
                'city' => $userData['city'] ?? null,
                'last_synced_at' => now(),
            ]
        );

        return $imidUser;
    }

    /**
     * Проверяет, верифицирован ли пользователь через imID
     */
    public function isUserVerified(User $user): bool
    {
        $imidUser = ImIDUser::where('user_id', $user->id)->first();
        
        return $imidUser && $imidUser->isVerified();
    }
}