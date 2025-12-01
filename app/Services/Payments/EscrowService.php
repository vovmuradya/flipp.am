<?php

namespace App\Services\Payments;

use App\Models\User;
use App\Models\Listing;
use App\Models\EscrowTransaction;
use Illuminate\Support\Str;
use App\Services\Payments\Providers\EscrowProviderInterface;
use App\Services\Payments\Providers\StubEscrowProvider;
use App\Services\Payments\Providers\IdramEscrowProvider;
use App\Services\Payments\Providers\TelcellEscrowProvider;

/**
 * Каркас Safe Deal/Эскроу: интерфейс для блокировки/освобождения средств.
 * TODO: интегрировать с выбранным банком/провайдером (Idram/Telcell/банк).
 */
class EscrowService
{
    private EscrowProviderInterface $provider;

    public function __construct()
    {
        $this->provider = $this->resolveProvider();
    }

    public function createHold(array $payload): array
    {
        // payload: buyer_id, seller_id, amount, currency, listing_id, deposit (optional)
        $providerResult = $this->provider->createHold($payload);

        $holdId = $providerResult['hold_id'] ?? Str::uuid()->toString();
        $status = $providerResult['status'] ?? 'pending';

        $record = EscrowTransaction::create([
            'hold_id' => $holdId,
            'listing_id' => $payload['listing_id'],
            'buyer_id' => $payload['buyer_id'],
            'seller_id' => $payload['seller_id'],
            'amount' => $payload['amount'],
            'currency' => $payload['currency'] ?? 'USD',
            'status' => $status,
            'provider' => $payload['provider'] ?? config('services.escrow.provider', 'stub'),
            'meta' => $providerResult ?? $payload['meta'] ?? null,
        ]);

        return $record->toArray();
    }

    public function capture(string $holdId): bool
    {
        $tx = EscrowTransaction::where('hold_id', $holdId)->first();
        if (!$tx) {
            return false;
        }

        $this->provider->capture($holdId);

        $tx->update([
            'status' => 'captured',
            'captured_at' => now(),
        ]);

        return true;
    }

    public function release(string $holdId): bool
    {
        $tx = EscrowTransaction::where('hold_id', $holdId)->first();
        if (!$tx) {
            return false;
        }

        $this->provider->release($holdId);

        $tx->update([
            'status' => 'released',
            'released_at' => now(),
        ]);

        return true;
    }

    private function resolveProvider(): EscrowProviderInterface
    {
        $provider = config('services.escrow.provider', 'stub');

        return match ($provider) {
            'idram' => new IdramEscrowProvider(),
            'telcell' => new TelcellEscrowProvider(),
            default => new StubEscrowProvider(),
        };
    }
}
