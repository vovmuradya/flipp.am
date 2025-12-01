<?php

namespace App\Services\Payments\Providers;

use Illuminate\Support\Str;

class StubEscrowProvider implements EscrowProviderInterface
{
    public function createHold(array $payload): array
    {
        return [
            'hold_id' => Str::uuid()->toString(),
            'status' => 'pending',
            'provider' => 'stub',
            'amount' => $payload['amount'],
            'currency' => $payload['currency'] ?? 'USD',
        ];
    }

    public function capture(string $holdId): bool
    {
        return true;
    }

    public function release(string $holdId): bool
    {
        return true;
    }
}
