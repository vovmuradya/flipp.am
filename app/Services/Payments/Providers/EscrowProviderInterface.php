<?php

namespace App\Services\Payments\Providers;

interface EscrowProviderInterface
{
    public function createHold(array $payload): array;

    public function capture(string $holdId): bool;

    public function release(string $holdId): bool;
}
