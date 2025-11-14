<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class CopartCookieManager
{
    public const CACHE_KEY = 'copart.dynamic_cookies';
    private const MIN_COOKIE_PAIRS = 7;
    private const MAX_ATTEMPTS = 4;

    private bool $browserChecked = false;

    /**
     * Возвращает строку Cookie из .env или из кэша автоматического обновления.
     */
    public function getCookieHeader(): ?string
    {
        $cached = Cache::get(self::CACHE_KEY);
        if (is_string($cached) && trim($cached) !== '') {
            return trim($cached);
        }

        $envCookie = config('services.copart.cookies') ?? env('COPART_COOKIES');
        if (is_string($envCookie) && trim($envCookie) !== '') {
            return trim($envCookie);
        }

        return null;
    }

    /**
     * Запускает Node-скрипт и пытается получить свежие cookie из Copart.
     */
    public function refreshCookies(): ?string
    {
        $script = base_path('scraper/fetch-copart-cookies.cjs');
        if (! is_file($script)) {
            Log::warning('CopartCookieManager: fetch script missing', ['path' => $script]);
            return null;
        }

        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            $process = new Process(['node', $script], base_path(), null, null, 150);
            $process->run();

            if (! $process->isSuccessful()) {
                $errorOutput = trim($process->getErrorOutput()) ?: trim($process->getOutput());

                if ($this->shouldInstallBrowser($errorOutput)) {
                    $this->installBrowserBinary();
                }

                Log::warning('CopartCookieManager: fetch script failed', [
                    'attempt' => $attempt,
                    'error' => $errorOutput,
                ]);
                continue;
            }

            $output = trim($process->getOutput());
            if ($output === '') {
                Log::warning('CopartCookieManager: fetch script returned empty output', ['attempt' => $attempt]);
                continue;
            }

            $decoded = json_decode($output, true);
            if (! is_array($decoded) || empty($decoded['cookies']) || ! is_string($decoded['cookies'])) {
                Log::warning('CopartCookieManager: invalid fetch output', [
                    'attempt' => $attempt,
                    'output' => $output,
                ]);
                continue;
            }

            $cookieString = trim($decoded['cookies']);
            if ($cookieString === '') {
                continue;
            }

            $pairCount = (int) ($decoded['count'] ?? 0);
            if ($pairCount < self::MIN_COOKIE_PAIRS && $attempt < self::MAX_ATTEMPTS) {
                Log::info('CopartCookieManager: cookie count low, retrying', [
                    'attempt' => $attempt,
                    'pairs' => $pairCount,
                ]);
                usleep(400000);
                continue;
            }

            Cache::put(self::CACHE_KEY, $cookieString, now()->addHours(6));

            return $cookieString;
        }

        Log::warning('CopartCookieManager: unable to fetch cookies after retries');

        return $this->getCookieHeader();
    }

    private function shouldInstallBrowser(?string $stderr): bool
    {
        if (!is_string($stderr) || $stderr === '') {
            return false;
        }

        $needles = [
            'Could not find Chrome',
            'Could not find Chromium',
            'browser fetcher',
        ];

        foreach ($needles as $needle) {
            if (stripos($stderr, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    private function installBrowserBinary(): void
    {
        if ($this->browserChecked) {
            return;
        }

        $this->browserChecked = true;

        $workingDir = base_path('scraper');
        $process = new Process(['npx', 'puppeteer', 'browsers', 'install', 'chrome'], $workingDir, null, null, 300);
        $process->run();

        if ($process->isSuccessful()) {
            Log::info('CopartCookieManager: installed Puppeteer Chrome browser');
            return;
        }

        Log::warning('CopartCookieManager: failed to install Puppeteer browser', [
            'error' => trim($process->getErrorOutput()) ?: trim($process->getOutput()),
        ]);
    }
}
