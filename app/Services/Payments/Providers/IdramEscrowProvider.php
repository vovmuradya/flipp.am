<?php

namespace App\Services\Payments\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Заглушка под Idram escrow (инвойсы/hold).
 * TODO: заменить URL/параметры на реальные, добавить подпись/верификацию.
 */
class IdramEscrowProvider implements EscrowProviderInterface
{
    private string $baseUrl;
    private ?string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.escrow.idram_base_url', 'https://api.idram.am'), '/');
        $this->apiKey = config('services.escrow.idram_api_key');
    }

    public function createHold(array $payload): array
    {
        $holdId = Str::uuid()->toString();

        if (!$this->apiKey) {
            Log::warning('IdramEscrowProvider: missing API key, returning stub response');
            return [
                'hold_id' => $holdId,
                'status' => 'pending',
                'provider' => 'idram',
            ];
        }

        // TODO: реализовать реальный запрос к Idram API
        return [
            'hold_id' => $holdId,
            'status' => 'pending',
            'provider' => 'idram',
        ];
    }

    public function capture(string $holdId): bool
    {
        // TODO: запрос на подтверждение в Idram
        return true;
    }

    public function release(string $holdId): bool
    {
        // TODO: запрос на разблокировку в Idram
        return true;
    }
}
