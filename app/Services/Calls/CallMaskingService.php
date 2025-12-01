<?php

namespace App\Services\Calls;

use App\Models\CallSession;
use Illuminate\Support\Str;

/**
 * Каркас call masking: выделяет прокси-номер и сохраняет сессию.
 * TODO: интегрировать с провайдером телефонии (sticky-сессии, запись).
 */
class CallMaskingService
{
    public function start(array $payload): CallSession
    {
        // payload: listing_id, buyer_id, seller_id, seller_number (real)
        $proxyNumber = $this->allocateProxy();

        return CallSession::create([
            'listing_id' => $payload['listing_id'],
            'buyer_id' => $payload['buyer_id'],
            'seller_id' => $payload['seller_id'],
            'seller_number' => $payload['seller_number'] ?? null,
            'proxy_number' => $proxyNumber,
            'status' => 'active',
            'started_at' => now(),
            'meta' => ['note' => 'stub'],
        ]);
    }

    public function end(CallSession $session, ?int $durationSeconds = null): CallSession
    {
        $session->update([
            'status' => 'ended',
            'ended_at' => now(),
            'duration_seconds' => $durationSeconds,
        ]);

        return $session;
    }

    private function allocateProxy(): string
    {
        // TODO: запросить у провайдера прокси-номер; пока заглушка
        return '+374' . random_int(10000000, 99999999);
    }
}
