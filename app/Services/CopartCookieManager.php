<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

class CopartCookieManager
{
    public const CACHE_KEY = 'copart.dynamic_cookies';
    private const MIN_COOKIE_PAIRS = 7;
    private const MAX_ATTEMPTS = 1; // keep refresh short to avoid blocking user
    private const FETCH_TIMEOUT = 20; // seconds
    private const CHECK_URL = 'https://www.copart.com/public/data/lotdetails/solr/1';
    private const LOCK_KEY = 'copart_cookie_refresh_lock';
    private const LOCK_SECONDS = 90;

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
        $lock = Cache::lock(self::LOCK_KEY, self::LOCK_SECONDS);
        if (! $lock->get()) {
            Log::info('CopartCookieManager: refresh skipped, another refresh in progress');
            return $this->getCookieHeader();
        }

        try {
            if (!config('services.copart.headless_enabled', true)) {
                Log::info('CopartCookieManager: headless disabled via config, skipping refresh');
                return $this->getCookieHeader();
            }

            // Попробуем текущие cookies без браузера
            if ($this->isCookieAlive($this->getCookieHeader())) {
                Log::info('CopartCookieManager: existing cookies still valid');
                return $this->getCookieHeader();
            }

            $script = base_path('scraper/fetch-copart-cookies-firefox.cjs');
            if (! is_file($script)) {
                Log::warning('CopartCookieManager: fetch script missing', ['path' => $script]);
                return $this->getCookieHeader();
            }

            $process = new Process(['node', $script], base_path(), null, null, 60);
            $process->run();

            if (! $process->isSuccessful()) {
                $error = trim($process->getErrorOutput()) ?: trim($process->getOutput());
                Log::warning('CopartCookieManager: fetch script failed', ['error' => $error]);
                return $this->getCookieHeader();
            }

            $output = trim($process->getOutput());
            $decoded = json_decode($output, true);
            $cookies = is_array($decoded) ? ($decoded['cookies'] ?? null) : null;
            $count = is_array($decoded) ? (int) ($decoded['count'] ?? 0) : 0;

            if (! is_string($cookies) || trim($cookies) === '') {
                Log::warning('CopartCookieManager: fetch script returned empty cookies');
                return $this->getCookieHeader();
            }

            Cache::put(self::CACHE_KEY, $cookies, now()->addHours(6));
            Log::info('CopartCookieManager: cookies refreshed via firefox', ['count' => $count]);

            return $cookies;
        } finally {
            $lock->release();
        }
    }

    private function isCookieAlive(?string $cookieHeader): bool
    {
        if (! is_string($cookieHeader) || trim($cookieHeader) === '') {
            return false;
        }

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'Cookie' => $cookieHeader,
                    'User-Agent' => config('services.copart.user_agent'),
                    'Accept' => 'application/json, text/plain, */*',
                ])
                ->get(self::CHECK_URL);

            if (! $response->successful()) {
                return false;
            }

            $payload = json_decode($response->body(), true);
            return is_array($payload) && isset($payload['data']);
        } catch (\Throwable $e) {
            Log::debug('CopartCookieManager: cookie check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Пытается определить путь до встроенного Chromium Puppeteer, если он не задан в .env.
     */
    private function detectPuppeteerExecutable(): ?string
    {
        $envPath = env('PUPPETEER_EXECUTABLE_PATH');
        if (is_string($envPath) && trim($envPath) !== '') {
            return trim($envPath);
        }

        try {
            $probe = new Process(
                ['node', '-e', "console.log(require('puppeteer').executablePath())"],
                base_path(),
                null,
                null,
                20
            );
            $probe->run();
            if ($probe->isSuccessful()) {
                $path = trim($probe->getOutput());
                return $path !== '' ? $path : null;
            }
        } catch (\Throwable $e) {
            Log::debug('CopartCookieManager: cannot detect puppeteer executable', ['error' => $e->getMessage()]);
        }

        return null;
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
