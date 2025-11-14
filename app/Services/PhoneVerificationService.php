<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PhoneVerificationService
{
    private const CACHE_PREFIX = 'phone_verify_';
    private const TTL_MINUTES = 10;

    public function sendCode(string $phone): void
    {
        $normalized = $this->normalize($phone);
        $code = random_int(100000, 999999);
        $payload = [
            'code' => Hash::make((string) $code),
            'sent_at' => now(),
        ];

        Cache::put(
            $this->cacheKey($normalized),
            $payload,
            now()->addMinutes(self::TTL_MINUTES)
        );

        Log::info("SMS verification code for {$normalized}: {$code}");
    }

    public function verify(string $phone, string $code): bool
    {
        $normalized = $this->normalize($phone);
        $cached = Cache::get($this->cacheKey($normalized));

        if (!$cached) {
            return false;
        }

        $isValid = Hash::check($code, $cached['code'] ?? '');

        if ($isValid) {
            Cache::forget($this->cacheKey($normalized));
        }

        return $isValid;
    }

    public function normalize(string $phone): string
    {
        return Str::of($phone)->replaceMatches('/[^0-9+]/', '')->value();
    }

    private function cacheKey(string $normalizedPhone): string
    {
        return self::CACHE_PREFIX.$normalizedPhone;
    }
}
