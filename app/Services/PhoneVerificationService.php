<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class PhoneVerificationService
{
    private const CACHE_PREFIX = 'phone_verify_';
    private const TTL_MINUTES = 10;

    public function __construct(
        private readonly MsgrushSmsClient $msgrushClient,
    ) {
    }

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

        $this->sendViaMsgrush($normalized, (string) $code);

        if (app()->environment('local', 'testing')) {
            Log::info("SMS verification code for {$normalized}: {$code}");
        }
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

    public function normalizeArmenian(?string $phone): ?string
    {
        $digits = preg_replace('/[^0-9]/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        if (Str::startsWith($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (Str::startsWith($digits, '374')) {
            $local = substr($digits, 3);
        } elseif (Str::startsWith($digits, '0') && strlen($digits) >= 9) {
            $local = substr($digits, -8);
        } elseif (strlen($digits) === 8) {
            $local = $digits;
        } else {
            return null;
        }

        if (!preg_match('/^\d{8}$/', $local)) {
            return null;
        }

        return '+374' . $local;
    }

    private function cacheKey(string $normalizedPhone): string
    {
        return self::CACHE_PREFIX.$normalizedPhone;
    }

    private function sendViaMsgrush(string $phone, string $code): void
    {
        if (! $this->msgrushClient->isEnabled()) {
            Log::warning('Msgrush SMS skipped: configuration missing.');
            return;
        }

        try {
            $this->msgrushClient->send($phone, __('Ваш код подтверждения: :code', ['code' => $code]));
        } catch (Throwable $exception) {
            Log::error('Failed to deliver OTP via Msgrush', [
                'phone' => $phone,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
