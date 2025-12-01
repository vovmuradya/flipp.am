<?php

namespace App\Services\Payments\Providers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Заглушка под Telcell escrow/invoice.
 * TODO: заменить на реальные вызовы Telcell Wallet/Marketplace API.
 */
class TelcellEscrowProvider implements EscrowProviderInterface
{
    public function createHold(array $payload): array
    {
        $holdId = Str::uuid()->toString();

        if (!config('services.escrow.telcell_api_key')) {
            Log::warning('TelcellEscrowProvider: missing API key, returning stub response');
            return [
                'hold_id' => $holdId,
                'status' => 'pending',
                'provider' => 'telcell',
            ];
        }

        // TODO: реализовать инвойс/hold через Telcell API
        return [
            'hold_id' => $holdId,
            'status' => 'pending',
            'provider' => 'telcell',
        ];
    }

    public function capture(string $holdId): bool
    {
        // TODO: подтверждение инвойса/hold
        return true;
    }

    public function release(string $holdId): bool
    {
        // TODO: отмена/возврат
        return true;
    }
}
