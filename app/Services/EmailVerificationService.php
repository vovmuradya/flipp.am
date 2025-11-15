<?php

namespace App\Services;

use App\Mail\EmailVerificationCodeMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class EmailVerificationService
{
    private const CACHE_PREFIX = 'email_verify_';
    private const TTL_MINUTES = 10;

    public function sendCode(string $email): void
    {
        $normalized = $this->normalize($email);
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

        try {
            Mail::to($normalized)->send(new EmailVerificationCodeMail((string) $code));
        } catch (Throwable $exception) {
            Log::error('Failed to deliver email verification code', [
                'email' => $normalized,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        if (app()->environment('local', 'testing')) {
            Log::info("Email verification code for {$normalized}: {$code}");
        }
    }

    public function verify(string $email, string $code): bool
    {
        $normalized = $this->normalize($email);
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

    public function normalize(string $email): string
    {
        return Str::of($email)->trim()->lower()->value();
    }

    private function cacheKey(string $normalizedEmail): string
    {
        return self::CACHE_PREFIX . $normalizedEmail;
    }
}
