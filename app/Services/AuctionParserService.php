<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\Process\Process;
use App\Services\CopartCookieManager;
class AuctionParserService
{
    // Hard cap so пользователь не ждёт слишком долго
    private const COPART_MAX_DURATION_SECONDS = 22;
    private ?string $copartCookieString = null;
    private ?array $copartCookieArray = null;
    private bool $copartBlocked = false;
    private bool $copartBlockedDuringLastParse = false;
    private bool $copartCookiesRefreshed = false;

    public function __construct(
        private readonly CopartCookieManager $copartCookieManager,
    ) {}

    public function clearCacheForUrl(string $url): void
    {
        $trimmed = trim($url);
        if ($trimmed === '') {
            return;
        }

        foreach ([true, false] as $flag) {
            $key = 'auction_parser:' . md5(($flag ? '1' : '0') . '|' . $trimmed);
            Cache::forget($key);
        }
    }

    public function parseFromUrl(string $url, bool $aggressive = true): ?array
    {
        $url = trim($url);
        $url = preg_replace('/\s+/', '', $url);

        $this->copartBlockedDuringLastParse = false;
        $this->copartBlocked = false;
        $this->copartCookiesRefreshed = false;

        $this->ensureCacheDirectory();

        $domain = parse_url($url, PHP_URL_HOST);

        if (str_contains($domain, 'list.am')) {
            $cacheKey = 'auction_parser:listam:' . md5($url);
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $result = $this->parseListAm($url);
            if ($result !== null) {
                Cache::put($cacheKey, $result, now()->addMinutes(60));
            }

            return $result;
        }

        if (str_contains($domain, 'copart.com')) {
            $aggressiveMode = filter_var(config('services.copart.aggressive', $aggressive), FILTER_VALIDATE_BOOLEAN);
            $cacheKey = 'auction_parser:' . md5(($aggressiveMode ? '1' : '0') . '|' . $url);

            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $result = $this->parseCopart($url, $aggressiveMode);

            $needsRetry = $result !== null && empty($result['photos'] ?? []);
            if ($needsRetry) {
                $this->clearCacheForUrl($url);
                // Force one more pass in aggressive mode to try pulling photos
                $result = $this->parseCopart($url, true);
            }

            if ($result !== null && !empty($result['photos'] ?? [])) {
                Cache::put($cacheKey, $result, now()->addMinutes(10));
            }

            return $result;
        }

        if (str_contains($domain, 'iaai-auctions.com') || str_contains($domain, 'iaai.com')) {
            Log::info('ℹ️ IAAI parsing disabled', ['url' => $url]);
            return null;
        }

        return null;
    }

    private function ensureCacheDirectory(): void
    {
        try {
            File::ensureDirectoryExists(storage_path('framework/cache/data'), 0775, true);
        } catch (\Throwable $e) {
            Log::warning('AuctionParserService: unable to ensure cache directory', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function buildCopartCookieJar(): CookieJar
    {
        $cookies = $this->getParsedCopartCookies();

        return CookieJar::fromArray($cookies, '.copart.com');
    }

    /**
     * @return array<string,string>
     */
    private function getParsedCopartCookies(): array
    {
        if ($this->copartCookieArray !== null) {
            return $this->copartCookieArray;
        }

        $cookieString = $this->getCopartCookieString();
        if ($cookieString === '') {
            $this->copartCookieArray = [];
            return $this->copartCookieArray;
        }

        $cookies = [];
        $cookiePairs = array_filter(array_map('trim', explode(';', $cookieString)));

        foreach ($cookiePairs as $pair) {
            if (stripos($pair, 'domain=') === 0 || stripos($pair, 'path=') === 0) {
                continue;
            }

            $separatorPos = strpos($pair, '=');
            if ($separatorPos === false) {
                continue;
            }

            $name = trim(substr($pair, 0, $separatorPos));
            $value = trim(substr($pair, $separatorPos + 1));

            if ($name === '' || $value === '') {
                continue;
            }

            $cookies[$name] = $value;
        }

        $this->copartCookieArray = $cookies;

        return $this->copartCookieArray;
    }

    private function getCopartCookieString(): string
    {
        if ($this->copartCookieString !== null) {
            return $this->copartCookieString;
        }

        $cookieString = $this->copartCookieManager->getCookieHeader();
        if (is_string($cookieString) && trim($cookieString) !== '') {
            $this->copartCookieString = trim($cookieString);
            return $this->copartCookieString;
        }

        $this->copartCookieString = '';

        return $this->copartCookieString;
    }

    private function refreshCopartCookies(): ?string
    {
        $cookieString = $this->copartCookieManager->refreshCookies();
        if (! is_string($cookieString) || trim($cookieString) === '') {
            return null;
        }

        $this->copartCookieString = trim($cookieString);
        $this->copartCookieArray = null;

        return $this->copartCookieString;
    }

    private function getCopartCookieHeader(): ?string
    {
        $cookieString = $this->getCopartCookieString();

        return $cookieString !== '' ? $cookieString : null;
    }

    public function wasCopartBlocked(): bool
    {
        return $this->copartBlockedDuringLastParse;
    }

    private function markCopartBlocked(): void
    {
        $this->copartBlocked = true;
        $this->copartBlockedDuringLastParse = true;

        if (! $this->copartCookiesRefreshed) {
            $this->copartCookiesRefreshed = true;
            $refreshed = $this->refreshCopartCookies();
            if ($refreshed) {
                Log::info('Copart cookies refreshed after block');
            }
        }
    }

    private function flagCopartBlockFromBody(?string $body): void
    {
        if (! is_string($body) || $body === '') {
            return;
        }

        if (stripos($body, '_Incapsula_Resource') !== false || stripos($body, 'Incapsula incident ID') !== false) {
            $this->markCopartBlocked();
        }
    }

    private function copartConfigValue(string $key, string $default): string
    {
        $value = config("services.copart.{$key}");

        return is_string($value) && trim($value) !== ''
            ? trim($value)
            : $default;
    }

    private function copartUserAgent(): string
    {
        return $this->copartConfigValue('user_agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    }

    private function copartOrigin(): string
    {
        return $this->copartConfigValue('origin', 'https://www.copart.com');
    }

    private function copartRefererFallback(): string
    {
        return $this->copartConfigValue('referer', 'https://www.copart.com/');
    }

    /**
     * Общие опции HTTP для запросов к Copart. Позволяет прокидывать cookie jar и принудительный DNS-resolve.
     */
    private function copartHttpOptions(?CookieJar $cookieJar = null): array
    {
        $options = [
            'verify' => false,
        ];

        if ($cookieJar) {
            $options['cookies'] = $cookieJar;
        }

        $curlOptions = [];

        $resolveList = $this->getCopartResolveList();
        if (!empty($resolveList)) {
            $curlOptions[\CURLOPT_RESOLVE] = $resolveList;
        }

        if (!empty($curlOptions)) {
            $options['curl'] = $curlOptions;
        }

        return $options;
    }

    /**
     * Возвращает массив значений формата host:port:ip для CURLOPT_RESOLVE.
     *
     * @return array<int,string>
     */
    private function getCopartResolveList(): array
    {
        $raw = config('services.copart.resolve') ?? env('COPART_RESOLVE');
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $entries = array_filter(array_map('trim', explode(',', $raw)));

        return array_values($entries);
    }

    /**
     * Загружает данные лота и фотографии через Puppeteer, когда прямые запросы блокируются.
     *
     * @return array{details: mixed, images: mixed, cookie: ?string}|null
     */
    private function fetchCopartLotViaHeadless(string $lotId): ?array
    {
        if (!config('services.copart.headless_enabled', true)) {
            Log::info('Copart headless skipped by config');
            return null;
        }

        $script = base_path('scraper/fetch-copart-lot.cjs');
        if (!is_file($script)) {
            Log::warning('Copart headless script missing', ['script' => $script]);
            return null;
        }

        // Keep headless attempt shorter to avoid user waiting too long on the import screen
        $process = new Process(['node', $script, $lotId], base_path(), null, null, 20);
        $process->run();

        if (!$process->isSuccessful()) {
            Log::warning('Copart headless fetch failed', [
                'lot_id' => $lotId,
                'error' => trim($process->getErrorOutput()) ?: trim($process->getOutput()),
            ]);
            return null;
        }

        $output = trim($process->getOutput());
        if ($output === '') {
            Log::warning('Copart headless fetch returned empty output', ['lot_id' => $lotId]);
            return null;
        }

        $decoded = json_decode($output, true);
        if (!is_array($decoded)) {
            Log::warning('Copart headless fetch produced invalid JSON', [
                'lot_id' => $lotId,
                'snippet' => substr($output, 0, 200),
            ]);
            return null;
        }

        return [
            'details' => $decoded['details'] ?? null,
            'images' => $decoded['images'] ?? null,
            'cookie' => isset($decoded['cookies']) && is_string($decoded['cookies']) && trim($decoded['cookies']) !== ''
                ? trim($decoded['cookies'])
                : null,
        ];
    }

    private function isPuppeteerAvailable(): bool
    {
        $path = env('PUPPETEER_EXECUTABLE_PATH');
        if (is_string($path) && trim($path) !== '' && is_file(trim($path))) {
            return true;
        }

        return false;
    }

    private function applyCopartCookieOverride(?string $cookieString): void
    {
        if (!is_string($cookieString)) {
            return;
        }

        $cookieString = trim($cookieString);
        if ($cookieString === '') {
            return;
        }

        Cache::put(CopartCookieManager::CACHE_KEY, $cookieString, now()->addHours(6));
        $this->copartCookieString = $cookieString;
        $this->copartCookieArray = null;
    }

    private function applyHeadlessCookies(array $payload, array &$headers, ?CookieJar &$cookieJar): void
    {
        $cookieString = $payload['cookie'] ?? null;
        if (!is_string($cookieString) || trim($cookieString) === '') {
            return;
        }

        $this->applyCopartCookieOverride($cookieString);
        $this->rebuildCopartRequestContext($headers, $cookieJar);
    }

    private function rebuildCopartRequestContext(array &$headers, ?CookieJar &$cookieJar): void
    {
        $cookieHeader = $this->getCopartCookieHeader();
        if ($cookieHeader) {
            $headers['Cookie'] = $cookieHeader;
        } else {
            unset($headers['Cookie']);
        }

        $cookieJar = $this->buildCopartCookieJar();
    }

    /**
     * @param array<string,string> $headers
     */
    private function requestCopartJson(string $url, array $headers, ?CookieJar $cookieJar = null): ?array
    {
        $options = $this->copartHttpOptions($cookieJar);
        $options['http_errors'] = false;

        try {
            $response = Http::timeout(10)
                ->retry(1, 500)
                ->withHeaders($headers)
                ->withOptions($options)
                ->get($url);

            $body = $response->body();
            $this->flagCopartBlockFromBody($body);

            $status = $response->status();

            if (in_array($status, [401, 403, 429], true)) {
                Log::warning('⚠️ Copart HTTP auth/blocked status', [
                    'url' => $url,
                    'status' => $status,
                ]);
                $this->markCopartBlocked();
                return null;
            }

            if ($response->successful()) {
                $payload = json_decode($body, true);
                if (is_array($payload)) {
                    return $payload;
                }

                Log::warning('⚠️ Copart JSON parse failed (HTTP)', [
                    'url' => $url,
                    'body_snippet' => substr($body ?? '', 0, 200),
                ]);
            } else {
                Log::warning('⚠️ Copart HTTP status', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body_snippet' => substr($body ?? '', 0, 200),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('⚠️ Copart HTTP error: ' . $e->getMessage(), ['url' => $url]);
        }

        if ($this->copartBlocked) {
            return null;
        }

        return $this->requestCopartJsonViaCurl($url, $headers);
    }

    /**
     * @param array<string,string> $headers
     */
    private function requestCopartJsonViaCurl(string $url, array $headers): ?array
    {
        if ($this->copartBlocked) {
            return null;
        }

        $command = $this->buildCurlCommand($url, $headers);
        $process = new Process($command);
        $process->setTimeout(15);
        $process->run();

        if (!$process->isSuccessful()) {
            Log::warning('⚠️ Copart curl JSON failed', [
                'url' => $url,
                'error' => trim($process->getErrorOutput()) ?: trim($process->getOutput()),
            ]);
            return null;
        }

        $body = trim($process->getOutput());
        $this->flagCopartBlockFromBody($body);
        if ($body === '') {
            return null;
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            Log::warning('⚠️ Copart curl returned non-JSON', [
                'url' => $url,
                'snippet' => substr($body, 0, 200),
            ]);
            return null;
        }

        return $decoded;
    }

    /**
     * @param array<string,string> $headers
     */
    private function requestCopartBody(string $url, array $headers, ?CookieJar $cookieJar = null): ?string
    {
        $options = $this->copartHttpOptions($cookieJar);
        $options['http_errors'] = false;

        try {
            $response = Http::timeout(10)
                ->retry(1, 500)
                ->withHeaders($headers)
                ->withOptions($options)
                ->get($url);

            $body = $response->body();
            $this->flagCopartBlockFromBody($body);

            $status = $response->status();

            if (in_array($status, [401, 403, 429], true)) {
                Log::warning('⚠️ Copart HTTP body blocked status', [
                    'url' => $url,
                    'status' => $status,
                ]);
                $this->markCopartBlocked();
                return null;
            }

            if ($response->successful()) {
                return $body;
            }

            Log::warning('⚠️ Copart HTTP body status', [
                'url' => $url,
                'status' => $response->status(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('⚠️ Copart HTTP body error: ' . $e->getMessage(), ['url' => $url]);
        }

        if ($this->copartBlocked) {
            return null;
        }

        return $this->requestCopartBodyViaCurl($url, $headers);
    }

    /**
     * @param array<string,string> $headers
     */
    private function requestCopartBodyViaCurl(string $url, array $headers): ?string
    {
        if ($this->copartBlocked) {
            return null;
        }

        $command = $this->buildCurlCommand($url, $headers);
        $process = new Process($command);
        $process->setTimeout(15);
        $process->run();

        if (!$process->isSuccessful()) {
            Log::warning('⚠️ Copart curl body failed', [
                'url' => $url,
                'error' => trim($process->getErrorOutput()) ?: trim($process->getOutput()),
            ]);
            return null;
        }

        $body = $process->getOutput();
        $this->flagCopartBlockFromBody($body);

        return $body !== '' ? $body : null;
    }

    /**
     * @param array<string,string> $headers
     * @return array<int,string>
     */
    private function buildCurlCommand(string $url, array $headers): array
    {
        $command = [
            'curl',
            '--silent',
            '--show-error',
            '--max-time',
            '15',
        ];

        foreach ($this->getCopartResolveList() as $resolve) {
            $command[] = '--resolve';
            $command[] = $resolve;
        }

        foreach ($headers as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $command[] = '-H';
            $command[] = $key . ': ' . $value;
        }

        $command[] = $url;

        return $command;
    }

    private function parseCopart(string $url, bool $aggressive = true): ?array
    {
        $attempt = 0;
        $startedAt = microtime(true);
        $normalizedUrl = $this->normalizeCopartUrl($url) ?? $url;
        $normalizedTried = ($normalizedUrl === $url);
        $currentUrl = $url;
        $result = null;

        while ($attempt < 3) {
            if ((microtime(true) - $startedAt) > self::COPART_MAX_DURATION_SECONDS) {
                Log::warning('⏱️ Copart parse aborted due to time cap', [
                    'elapsed' => round(microtime(true) - $startedAt, 2),
                    'attempt' => $attempt + 1,
                ]);
                break;
            }
            $this->copartBlocked = false;
            $result = $this->parseCopartAttempt($currentUrl, $aggressive);

            $retryReason = $this->shouldForceRetry($result);
            if ($retryReason === null) {
                return $result;
            }

            Log::info('🔁 Copart retry scheduled', [
                'reason' => $retryReason,
                'attempt' => $attempt + 1,
                'aggressive' => $aggressive,
            ]);

            if ($attempt === 0) {
                $attempt++;
                $aggressive = true;

                $refreshed = $this->refreshCopartCookies();
                if ($refreshed) {
                    Log::info('🔁 Copart cookies refreshed, retrying parse');
                } else {
                    Log::warning('⚠️ Copart cookie refresh unavailable, retrying with existing session');
                }

                continue;
            }

            if (!$normalizedTried && $currentUrl !== $normalizedUrl) {
                $normalizedTried = true;
                $attempt++;
                $currentUrl = $normalizedUrl;

                Log::info('🔁 Copart retrying with normalized URL (slug removed)', [
                    'original_url' => $url,
                    'normalized_url' => $normalizedUrl,
                ]);

                continue;
            }

            break;
        }

        return $result;
    }

    private function parseCopartAttempt(string $url, bool $aggressive = true): ?array
    {
        try {
            $lotHtml = null;
            Log::info('🔍 Parsing Copart URL: ' . $url);
            preg_match('/\/lot\/(\d+)/', $url, $lotMatches);
            $lotId = $lotMatches[1] ?? null;

            if (!$lotId) {
                Log::warning('❌ Could not extract lot ID from URL');
                return null;
            }

            Log::info('✅ Lot ID extracted: ' . $lotId);
            $photos = [];
            $make = null;
            $model = null;
            $year = null;
            $mileage = null;
            $color = null;
            $engineStr = null;
            $primaryDamage = null;
            $headlessPayload = null;

            $headers = [
                'User-Agent' => $this->copartUserAgent(),
                'Accept' => 'application/json, text/plain, */*',
                'Referer' => $url,
                'Accept-Language' => 'en-US,en;q=0.9',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache',
                'Origin' => $this->copartOrigin(),
                'X-Requested-With' => 'XMLHttpRequest',
                'Sec-Fetch-Dest' => 'empty',
                'Sec-Fetch-Mode' => 'cors',
                'Sec-Fetch-Site' => 'same-origin',
            ];

            $cookieHeader = $this->getCopartCookieHeader();
            if ($cookieHeader) {
                $headers['Cookie'] = $cookieHeader;
            }

            // Reusable cookie jar (может содержать заранее сохранённые куки из .env)
            $cookieJar = $this->buildCopartCookieJar();

            // GET MAIN VEHICLE DATA FROM API
            $apiUrl = "https://www.copart.com/public/data/lotdetails/solr/{$lotId}";
            Log::info('📡 Fetching vehicle data from API: ' . $apiUrl);
            $apiData = $this->requestCopartJson($apiUrl, $headers, $cookieJar);
            $details = $apiData['data']['lotDetails'] ?? [];

            $auctionEndAt = null;

            if (($this->copartBlocked || empty($details)) && $headlessPayload === null) {
                $headlessPayload = $this->fetchCopartLotViaHeadless($lotId);
                if (!is_array($headlessPayload)) {
                    $headlessPayload = false;
                } else {
                    $this->applyHeadlessCookies($headlessPayload, $headers, $cookieJar);
                    $headlessDetails = data_get($headlessPayload, 'details.data.lotDetails');
                    if (is_array($headlessDetails) && !empty($headlessDetails)) {
                        $details = $headlessDetails;
                        $apiData = $headlessPayload['details'] ?? $apiData;
                        Log::info('✅ Copart headless fallback returned vehicle meta');
                    }
                    $this->copartBlocked = false;
                    $this->copartBlockedDuringLastParse = false;
                }
            }

            if ($this->copartBlocked && empty($details)) {
                Log::warning('❌ Copart blocked while fetching lot meta, aborting attempt');
                return null;
            }

            if (!empty($details)) {
                $make = $details['mkn'] ?? null;
                $model = $details['lm'] ?? null;
                $year = $details['lcy'] ?? null;
                $mileage = isset($details['od']) ? (int)$details['od'] : null;
                $color = $details['clr'] ?? null;
                $engineStr = $details['egn'] ?? null;
                $primaryDamage = $this->extractCopartPrimaryDamage($details);

                $auctionEndAt = $this->detectCopartAuctionEnd($details);

                Log::info('✅ Got vehicle data: make=' . $make . ', model=' . $model . ', year=' . $year);
            } elseif ($apiData === null) {
                Log::warning('⚠️ Vehicle API returned no data after retries');
            }

            $buyNowPrice = $this->extractCopartBuyNowPrice($details);
            $buyNowCurrency = $this->extractCopartBuyNowCurrency($details);
            $operationalStatus = $this->extractCopartOperationalStatus($details);
            $currentBidPrice = $this->extractCopartCurrentBid($details);
            $currentBidCurrency = $this->extractCopartCurrentBidCurrency($details) ?? $buyNowCurrency;

            if ($aggressive && ($buyNowPrice === null || $operationalStatus === null || $currentBidPrice === null)) {
                $lotHtml = $this->fetchCopartLotHtml($url, $cookieJar);

                if ($lotHtml) {
                    if ($buyNowPrice === null) {
                        [$htmlBuyNowPrice, $htmlBuyNowCurrency] = $this->extractCopartBuyNowFromHtml($lotHtml);
                        if ($htmlBuyNowPrice !== null) {
                            $buyNowPrice = $htmlBuyNowPrice;
                            $buyNowCurrency ??= $htmlBuyNowCurrency;
                        }
                    }

                    if ($operationalStatus === null) {
                        $operationalStatus = $this->extractOperationalStatusFromHtml($lotHtml);
                    }

                    if ($currentBidPrice === null) {
                        [$htmlCurrentBid, $htmlCurrentBidCurrency] = $this->extractCopartCurrentBidFromHtml($lotHtml);
                        if ($htmlCurrentBid !== null) {
                            $currentBidPrice = $htmlCurrentBid;
                            $currentBidCurrency ??= $htmlCurrentBidCurrency;
                        }
                    }

                    if ($primaryDamage === null) {
                        $primaryDamage = $this->extractCopartPrimaryDamageFromHtml($lotHtml);
                    }
                }
            }

            // GET PHOTOS FROM API
            $imageApiUrl = "https://www.copart.com/public/data/lotdetails/solr/lotImages/{$lotId}";
            try {
                Log::info('📸 Fetching images from: ' . $imageApiUrl);
                usleep(500000); // 0.5 sec delay between requests

                $imgHeaders = $headers;
                $imgHeaders['Referer'] = $url;
                $imgHeaders['Accept'] = 'application/json, text/plain, */*';

                $imgData = $this->requestCopartJson($imageApiUrl, $imgHeaders, $cookieJar);
                $imagesList = data_get($imgData, 'data.imagesList');

                if (($this->copartBlocked || empty($imagesList)) && $headlessPayload === null) {
                    $headlessPayload = $this->fetchCopartLotViaHeadless($lotId);
                    if (!is_array($headlessPayload)) {
                        $headlessPayload = false;
                    } else {
                        $this->applyHeadlessCookies($headlessPayload, $headers, $cookieJar);
                        if (empty($details)) {
                            $headlessDetails = data_get($headlessPayload, 'details.data.lotDetails');
                            if (is_array($headlessDetails) && !empty($headlessDetails)) {
                                $details = $headlessDetails;
                            }
                        }
                        $imgData = $headlessPayload['images'] ?? $imgData;
                        $imagesList = data_get($imgData, 'data.imagesList', $imagesList);
                        $this->copartBlocked = false;
                        $this->copartBlockedDuringLastParse = false;
                    }
                }

                if ($this->copartBlocked && empty($imagesList)) {
                    Log::warning('❌ Copart blocked while fetching image metadata');
                    return null;
                }

                $imagesArray = [];
                $imageUrls = [];

                if (isset($imgData['data']) && is_string($imgData['data'])) {
                    $decodedData = json_decode($imgData['data'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedData)) {
                        $imgData['data'] = $decodedData;
                    }
                }

                if (is_array($imgData)) {
                    Log::info('✅ Image API response received');

                    $responseKeys = isset($imgData['data']) ? array_keys($imgData['data']) : [];
                    Log::info('🔍 API response structure: data keys = [' . implode(', ', $responseKeys) . ']');

                    if (isset($imgData['data']['imagesList'])) {
                        $imagesList = $imgData['data']['imagesList'];
                        if (is_array($imagesList)) {
                            $isListKeys = array_keys($imagesList);
                            Log::info('🔍 imagesList structure: keys = [' . implode(', ', $isListKeys) . '], count = ' . count($imagesList));
                        } else {
                            Log::info('🔍 imagesList is not an array, type: ' . gettype($imagesList));
                        }
                    } else {
                        Log::warning('🔍 No imagesList found in response');
                    }

                    // Handle different API response structures
                    if (isset($imgData['data']['imagesList']['content']) && is_array($imgData['data']['imagesList']['content'])) {
                        $imagesArray = $imgData['data']['imagesList']['content'];
                        Log::info('✅ Found imagesList.content with ' . count($imagesArray) . ' images');
                    } elseif (isset($imgData['data']['imagesList']) && is_array($imgData['data']['imagesList'])) {
                        // Check if it's a direct array of images or has pagination structure
                        $imagesList = $imgData['data']['imagesList'];

                        // If first element has image properties, it's direct array
                        if (!empty($imagesList) && isset($imagesList[0]) &&
                            (isset($imagesList[0]['fullUrl']) || isset($imagesList[0]['highResUrl']) || isset($imagesList[0]['thumbnailUrl']))) {
                            $imagesArray = $imagesList;
                            Log::info('✅ Found direct imagesList array with ' . count($imagesArray) . ' items');
                        } else {
                            // Maybe it's paginated or has different structure, let's explore
                            foreach ($imagesList as $key => $value) {
                                if (is_array($value) && !empty($value)) {
                                    Log::info('🔍 Checking imagesList[' . $key . '] with ' . count($value) . ' items');
                                    // Check if this looks like an images array
                                    if (isset($value[0]) && is_array($value[0]) &&
                                        (isset($value[0]['fullUrl']) || isset($value[0]['highResUrl']) || isset($value[0]['thumbnailUrl']))) {
                                        $imagesArray = $value;
                                        Log::info('✅ Found images in imagesList[' . $key . '] with ' . count($imagesArray) . ' items');
                                        break;
                                    }
                                }
                            }
                        }
                    } else {
                        Log::warning('⚠️ imagesList structure not recognized - logging full structure');
                        Log::warning('🔍 Full API response: ' . json_encode($imgData, JSON_PRETTY_PRINT));
                    }
                }

            } catch (\Exception $e) {
                Log::warning('⚠️ Image API request failed: ' . $e->getMessage());
            }

            // After attempting the primary API, broaden the search if needed
            if ($aggressive && empty($imagesArray)) {
                Log::info('🔄 Primary image API empty, trying alternate endpoints');
                $imagesArray = $this->fetchImagesViaAlternateApis($lotId, $url, $headers, $cookieJar);
            }

            if ($aggressive && empty($imagesArray)) {
                Log::info('🔄 Alternate endpoints failed, trying HTML scraping fallback');
                $imagesArray = $this->scrapeImagesFromHtml($url, $lotId, $cookieJar, $lotHtml);
            }

            if ($aggressive && empty($imagesArray)) {
                Log::info('🔄 HTML scraping also failed, trying URL generation');
                $imagesArray = $this->generatePotentialImageUrls($lotId);
            }

            if (!empty($imagesArray)) {
                $imageUrls = [];

                foreach ($imagesArray as $img) {
                    $imgUrl = $img['fullUrl'] ?? $img['highResUrl'] ?? $img['thumbnailUrl'] ?? $img['link'] ?? null;
                    if (!$imgUrl) {
                        continue;
                    }

                    $normalizedUrl = $this->normalizeCopartImageUrl($imgUrl);
                    if (!$normalizedUrl || $this->isPlaceholderImage($normalizedUrl)) {
                        continue;
                    }

                    $imageUrls[] = $normalizedUrl;
                }

                Log::info('📸 Extracted ' . count($imageUrls) . ' image URLs after fallbacks');

                // Remove duplicates by normalized path
                $seenPaths = [];
                foreach ($imageUrls as $imgUrl) {
                    $path = parse_url($imgUrl, PHP_URL_PATH) ?? $imgUrl;
                    $query = parse_url($imgUrl, PHP_URL_QUERY);
                    $normalizedPath = preg_replace('/_(thn|hrs|thb|tmb|ful)\.(jpg|jpeg|png|webp)$/i', '.$2', $path);
                    $dedupeKey = strtolower($normalizedPath . ($query ? '?' . $query : ''));

                    if (isset($seenPaths[$dedupeKey])) {
                        continue;
                    }
                    $seenPaths[$dedupeKey] = true;

                    $proxyUrl = url('/proxy/image') . '?u=' . rawurlencode($imgUrl);
                    if (!empty($url)) {
                        $proxyUrl .= '&r=' . rawurlencode($url);
                    }
                    $photos[] = $proxyUrl;
                }

                Log::info('✅ Final photos count: ' . count($photos));
            } else {
                Log::warning('⚠️ No images found after all strategies');
            }

            // FALLBACK: Parse from URL if main data missing
            if (!$year || !$make || !$model) {
                Log::info('⚡ Fallback: Parsing from URL...');
                preg_match('/(\d{4})[-\s]([a-zA-Z]+)[-\s]([a-zA-Z0-9\s\-]+)/i', $url, $matches);
                $year = $year ?? ($matches[1] ?? date('Y'));
                $make = $make ?? (isset($matches[2]) ? ucfirst(strtolower($matches[2])) : 'Неизвестно');
                $modelRaw = $model ?? ($matches[3] ?? 'Неизвестно');
                $model = preg_replace('/(nb|ak|ca|tx|fl|ny|ga|me)-[\w]+$/i', '', $modelRaw);
                $model = ucwords(strtolower(trim($model)));
            }

            // ESTIMATE MILEAGE if missing
            if (!$mileage) {
                $age = date('Y') - (int)$year;
                $mileage = max(0, $age * 12000 + rand(-3000, 5000));
                Log::info('⚡ Estimated mileage: ' . $mileage);
            }

            // PARSE ENGINE DISPLACEMENT
            $engineCc = $this->parseEngineString($engineStr);

            // ✅ Use placeholder if no photos found
            if (empty($photos)) {
                Log::warning('⚠️ No photos found, using placeholder');
                $placeholderUrl = 'https://via.placeholder.com/800x600/e5e7eb/6b7280?text=No+Image+Available';
                // ✅ FIX: Ensure placeholder also uses an absolute URL via url() helper
                $photos = [url('/proxy/image?u=' . rawurlencode($placeholderUrl))];
            }

            $data = [
                'make' => $make ?: 'Неизвестно',
                'model' => $model ?: 'Неизвестно',
                'year' => is_numeric($year) ? (int)$year : date('Y'),
                'mileage' => $mileage,
                'exterior_color' => $color ?: 'Неизвестно',
                'transmission' => 'automatic',
                'fuel_type' => 'gasoline',
                'engine_displacement_cc' => $engineCc,
                'body_type' => 'SUV',
                'buy_now_price' => $buyNowPrice,
                'buy_now_currency' => $buyNowPrice !== null ? ($buyNowCurrency ?? 'USD') : null,
                'current_bid_price' => $currentBidPrice,
                'current_bid_currency' => $currentBidPrice !== null ? ($currentBidCurrency ?? $buyNowCurrency ?? 'USD') : null,
                'operational_status' => $operationalStatus,
                'primary_damage' => $primaryDamage,
                'photos' => array_values($photos),
                'source_auction_url' => $url,
                'auction_ends_at' => $auctionEndAt ? $auctionEndAt->toIso8601String() : null,
            ];

            Log::info('📦 Final parsed: ' . $data['make'] . ' ' . $data['model'] . ' (' . $data['year'] . '), ' . count($data['photos']) . ' photos');
            return $data;
        } catch (\Exception $e) {
            Log::error('❌ Copart error: ' . $e->getMessage());
            return null;
        }
    }

    private function parseListAm(string $url): ?array
    {
        try {
            Log::info('🔍 Parsing List.am URL: ' . $url);

            $payload = $this->parseListAmStatic($url);

            $needsHeadless = !is_array($payload) || count($payload['images'] ?? []) < 1;
            if ($needsHeadless) {
                $headlessPayload = $this->fetchListAmViaHeadless($url);
                if (is_array($headlessPayload)) {
                    $payload = $headlessPayload;
                }
            }

            if (!is_array($payload)) {
                Log::warning('⚠️ List.am headless returned no payload', ['url' => $url]);
                return $this->parseListAmFromSlug($url);
            }

            $title = is_string($payload['title'] ?? null) ? trim($payload['title']) : '';
            $description = is_string($payload['description'] ?? null) ? trim($payload['description']) : null;

            [$year, $make, $model] = $this->extractListAmVehicleFromTitle($title ?: $url);

            $price = $this->normalizePriceValue($payload['price'] ?? null);
            $currency = $this->normalizeCurrencyCode($payload['currency'] ?? null);
            $mileage = $this->normalizeNumericValue($payload['mileage'] ?? null);
            if (!$mileage && $description) {
                $mileage = $this->extractMileageFromText($description);
            }

            $photos = $this->proxyExternalImages($payload['images'] ?? [], $url);

            if (empty($photos)) {
                $placeholderUrl = 'https://via.placeholder.com/800x600/e5e7eb/6b7280?text=No+Image+Available';
                $photos = [url('/proxy/image?u=' . rawurlencode($placeholderUrl))];
                Log::warning('⚠️ List.am images missing, using placeholder', ['url' => $url]);
            }

            $data = [
                'make' => $make ?: 'Неизвестно',
                'model' => $model ?: 'Неизвестно',
                'year' => $year ?: (int) date('Y'),
                'mileage' => $mileage,
                'exterior_color' => null,
                'transmission' => 'automatic',
                'fuel_type' => 'gasoline',
                'engine_displacement_cc' => null,
                'body_type' => null,
                'photos' => array_values($photos),
                'source_auction_url' => $url,
                'auction_ends_at' => null,
                'buy_now_price' => $price,
                'buy_now_currency' => $price !== null ? ($currency ?? 'AMD') : null,
                'current_bid_price' => null,
                'current_bid_currency' => null,
                'operational_status' => null,
                'title' => $title ?: null,
                'description' => $description ?: null,
            ];

            // Если это похоже не на транспорт — прекращаем обработку
            if (!$this->looksLikeVehicleFromListAm($data)) {
                Log::warning('❌ List.am item rejected as non-vehicle', ['url' => $url, 'title' => $title]);
                return null;
            }

            Log::info('📦 List.am parsed', [
                'url' => $url,
                'make' => $data['make'],
                'model' => $data['model'],
                'year' => $data['year'],
                'photos' => count($data['photos']),
            ]);

            return $data;
        } catch (\Throwable $e) {
            Log::warning('❌ List.am error: ' . $e->getMessage(), ['url' => $url]);
            return null;
        }
    }

    /**
     * Грубая проверка, что объявление с List.am похоже на транспорт.
     */
    private function looksLikeVehicleFromListAm(array $data): bool
    {
        $photos = $data['photos'] ?? [];
        if (!is_array($photos) || count($photos) < 2) {
            return false;
        }

        $year = $data['year'] ?? null;
        $currentYear = (int) date('Y');
        if (!is_numeric($year) || (int) $year < 1980 || (int) $year > $currentYear + 1) {
            return false;
        }

        $title = strtolower(trim((string) ($data['title'] ?? '')));
        $description = strtolower(trim((string) ($data['description'] ?? '')));
        $haystack = $title . ' ' . $description;

        $makes = [
            'acura','alfa romeo','alfa','audi','bmw','buick','cadillac','chevrolet','chevy','chrysler','citroen','dacia','daewoo','daihatsu','dodge','fiat','ford','genesis','gmc','honda','hyundai','infiniti','isuzu','jaguar','jeep','kia','lada','land rover','lexus','maserati','mazda','mercedes','benz','mini','mitsubishi','nissan','opel','peugeot','porsche','ram','renault','saab','seat','skoda','smart','subaru','suzuki','tesla','toyota','volkswagen','vw','volvo','hummer','pontiac','saturn','scion','uaz','gaz'
        ];
        foreach ($makes as $make) {
            if (str_contains($haystack, $make)) {
                return true;
            }
        }

        // fallback: если есть год и минимум 3 фото, но нет брендов — всё равно пропустим
        if (count($photos) >= 3) {
            return true;
        }

        return false;
    }

    private function parseListAmStatic(string $url): ?array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => config('services.listam.user_agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'),
                'Accept-Language' => 'ru,en;q=0.9',
                'Referer' => 'https://www.list.am/',
            ])
                ->timeout(12)
                ->get($url);

            if (!$response->successful()) {
                return null;
            }

            $html = $response->body();
            if (!is_string($html) || trim($html) === '') {
                return null;
            }

            if (stripos($html, 'cf-browser-verification') !== false || stripos($html, 'challenge-platform') !== false) {
                return null;
            }

            $title = $this->extractMetaContent($html, 'og:title')
                ?: $this->matchFirst($html, ['/\\b<h1[^>]*>([^<]+)<\\/h1>/i']);
            $description = $this->extractMetaContent($html, 'og:description')
                ?: $this->matchFirst($html, ['/<div[^>]+itemprop=["\']description["\'][^>]*>(.*?)<\\/div>/is']);

            $price = $this->matchFirst($html, [
                '/property=["\']product:price:amount["\'][^>]+content=["\']([^"\']+)["\']/i',
                '/itemprop=["\']price["\'][^>]+content=["\']([^"\']+)["\']/i',
                '/itemprop=["\']price["\'][^>]*>([^<]+)/i',
            ]);
            $currency = $this->matchFirst($html, [
                '/property=["\']product:price:currency["\'][^>]+content=["\']([^"\']+)["\']/i',
                '/itemprop=["\']priceCurrency["\'][^>]+content=["\']([^"\']+)["\']/i',
            ]);

            $images = [];
            $seen = [];
            $pushImage = function (?string $value) use (&$images, &$seen, $url) {
                if (!is_string($value)) {
                    return;
                }
                $normalized = $this->normalizeExternalImageUrl($value, $url);
                if (!$normalized || !$this->isLikelyListAmPhoto($normalized)) {
                    return;
                }
                $key = strtolower($normalized);
                if (isset($seen[$key])) {
                    return;
                }
                $seen[$key] = true;
                $images[] = $normalized;
            };

            $pushImage($this->extractMetaContent($html, 'og:image'));

            if (preg_match_all('/<script[^>]+application\\/ld\\+json[^>]*>(.*?)<\\/script>/is', $html, $matches)) {
                foreach ($matches[1] as $scriptBody) {
                    $decoded = json_decode(trim($scriptBody), true);
                    if (!is_array($decoded)) {
                        continue;
                    }

                    $candidates = array_is_list($decoded) ? $decoded : [$decoded];
                    foreach ($candidates as $item) {
                        if (!is_array($item)) {
                            continue;
                        }
                        $imagesField = $item['images'] ?? $item['image'] ?? null;
                        if (is_string($imagesField)) {
                            $pushImage($imagesField);
                        } elseif (is_array($imagesField)) {
                            foreach ($imagesField as $img) {
                                $pushImage(is_string($img) ? $img : null);
                            }
                        }
                    }
                }
            }

            if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $imgMatches)) {
                foreach ($imgMatches[1] as $imgSrc) {
                    $pushImage($imgSrc);
                }
            }

            $mileage = null;
            if (is_string($description) && $description !== '') {
                $mileage = $this->extractMileageFromText($description);
            }
            if ($mileage === null) {
                $mileage = $this->extractMileageFromText($html);
            }

            return [
                'title' => $title ?: null,
                'description' => $description ?: null,
                'price' => $price ?: null,
                'currency' => $currency ?: null,
                'mileage' => $mileage,
                'images' => $images,
            ];
        } catch (\Throwable $e) {
            Log::debug('List.am static parse failed', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function fetchListAmViaHeadless(string $url): ?array
    {
        $script = base_path('scraper/fetch-listam-item.cjs');
        if (!is_file($script)) {
            Log::warning('List.am headless script missing', ['script' => $script]);
            return null;
        }

        $process = new Process(['node', $script, $url], base_path(), null, null, 120);
        $process->run();

        if (!$process->isSuccessful()) {
            Log::warning('List.am headless fetch failed', [
                'url' => $url,
                'error' => trim($process->getErrorOutput()) ?: trim($process->getOutput()),
            ]);
            return null;
        }

        $output = trim($process->getOutput());
        if ($output === '') {
            return null;
        }

        $decoded = json_decode($output, true);
        if (!is_array($decoded)) {
            Log::warning('List.am headless produced invalid JSON', [
                'url' => $url,
                'snippet' => substr($output, 0, 200),
            ]);
            return null;
        }

        return $decoded;
    }

    private function extractListAmVehicleFromTitle(string $title): array
    {
        $year = null;
        if (preg_match('/(19|20)\d{2}/', $title, $m)) {
            $year = (int) $m[0];
        }

        $parts = preg_split('/[\s,\-|\/]+/', strtolower($title));
        $parts = array_values(array_filter(array_map('trim', $parts)));

        $makes = [
            'acura','alfa','romeo','audi','bmw','buick','cadillac','chevrolet','chevy','chrysler','citroen','dacia','daewoo','daihatsu','dodge','fiat','ford','genesis','gmc','honda','hyundai','infiniti','isuzu','jaguar','jeep','kia','lada','land','rover','lexus','lincoln','maserati','mazda','mercedes','benz','mini','mitsubishi','nissan','opel','peugeot','porsche','ram','renault','saab','seat','skoda','smart','subaru','suzuki','tesla','toyota','volkswagen','vw','volvo','hummer','pontiac','saturn','scion','uaz','gaz',
        ];

        $make = null;
        $model = null;

        foreach ($parts as $idx => $token) {
            $clean = preg_replace('/[^a-z0-9]+/i', '', $token);
            if ($clean === '') {
                continue;
            }

            if (in_array($clean, $makes, true)) {
                $make = $this->titleCase($clean);
                $modelTokens = array_slice($parts, $idx + 1, 3);
                $modelTokens = array_map(function ($value) {
                    return preg_replace('/[^a-z0-9]+/i', '', $value);
                }, $modelTokens);
                $modelTokens = array_values(array_filter($modelTokens));
                if (!empty($modelTokens)) {
                    $model = $this->titleCase(implode(' ', $modelTokens));
                }
                break;
            }
        }

        return [$year, $make, $model];
    }

    private function normalizePriceValue($raw): ?float
    {
        if (is_numeric($raw)) {
            return (float) $raw;
        }

        if (!is_string($raw)) {
            return null;
        }

        $clean = preg_replace('/[^\d.,]/', '', $raw);
        $clean = str_replace([' ', ','], ['', ''], $clean);
        if ($clean === '' || !is_numeric($clean)) {
            return null;
        }

        return (float) $clean;
    }

    private function normalizeCurrencyCode(?string $currency): ?string
    {
        if (!is_string($currency)) {
            return null;
        }

        $c = strtoupper(trim($currency));
        if ($c === '') {
            return null;
        }

        $map = [
            'AMD' => 'AMD',
            'ДРАМ' => 'AMD',
            '֏' => 'AMD',
            'USD' => 'USD',
            '$' => 'USD',
            'EUR' => 'EUR',
            '€' => 'EUR',
        ];

        if (isset($map[$c])) {
            return $map[$c];
        }

        return $c;
    }

    private function normalizeExternalImageUrl(string $imgUrl, string $referer): ?string
    {
        $imgUrl = trim($imgUrl);
        if ($imgUrl === '') {
            return null;
        }

        if (str_starts_with($imgUrl, '//')) {
            $imgUrl = 'https:' . $imgUrl;
        } elseif (!preg_match('/^https?:\\/\\//i', $imgUrl)) {
            if ($referer) {
                $imgUrl = rtrim($referer, '/') . '/' . ltrim($imgUrl, '/');
            } else {
                return null;
            }
        }

        return $imgUrl;
    }

    private function isLikelyListAmPhoto(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST) ?: '';
        $path = parse_url($url, PHP_URL_PATH) ?: '';

        $host = is_string($host) ? strtolower($host) : '';
        $path = is_string($path) ? strtolower($path) : '';

        if ($host === '' || !str_contains($host, 'list.am')) {
            return false;
        }

        return (bool) preg_match('/\\/(f|r)\\/\\d+\\/\\d+\\.(jpe?g|png|webp)$/i', $path)
            || (bool) preg_match('/\\/images\\/\\d+\\/\\d+\\.(jpe?g|png|webp)$/i', $path)
            || (bool) preg_match('/\\/mphotos\\/.+\\.(jpe?g|png|webp)$/i', $path);
    }

    /**
     * @param array<int,mixed> $rawImages
     * @return array<int,string>
     */
    private function proxyExternalImages(array $rawImages, string $referer): array
    {
        $photos = [];
        $seen = [];

        foreach ($rawImages as $img) {
            if (!is_string($img) || strlen($img) < 5) {
                continue;
            }

            $normalized = $this->normalizeExternalImageUrl($img, $referer);
            if (!$normalized) {
                continue;
            }

            $key = strtolower($normalized);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $proxyUrl = url('/proxy/image') . '?u=' . rawurlencode($normalized);
            if (!empty($referer)) {
                $proxyUrl .= '&r=' . rawurlencode($referer);
            }
            $photos[] = $proxyUrl;
        }

        return $photos;
    }

    private function normalizeNumericValue($value): ?int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);
        if ($digits === '') {
            return null;
        }

        return (int) $digits;
    }

    private function extractMileageFromText(string $text): ?int
    {
        if (preg_match('/([\d\s\.,]+)\s*(км|km|կմ)/iu', $text, $m)) {
            $digits = preg_replace('/\D+/', '', $m[1] ?? '');
            if ($digits !== '' && is_numeric($digits)) {
                return (int) $digits;
            }
        }

        return null;
    }

    private function parseListAmFromSlug(string $url): ?array
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return null;
        }

        $segments = array_values(array_filter(array_map('trim', explode('/', $path))));
        $last = end($segments) ?: '';
        $last = preg_replace('/[^a-z0-9\\-]+/i', '-', $last);
        $last = trim($last, '-');
        if ($last === '') {
            return null;
        }

        $parts = array_values(array_filter(explode('-', $last)));
        if (empty($parts)) {
            return null;
        }

        $title = implode(' ', $parts);
        [$year, $make, $model] = $this->extractListAmVehicleFromTitle($title);

        return [
            'make' => $make ?: 'Неизвестно',
            'model' => $model ?: 'Неизвестно',
            'year' => $year ?: (int) date('Y'),
            'mileage' => null,
            'exterior_color' => null,
            'transmission' => 'automatic',
            'fuel_type' => 'gasoline',
            'engine_displacement_cc' => null,
            'body_type' => null,
            'photos' => [],
            'source_auction_url' => $url,
            'auction_ends_at' => null,
            'buy_now_price' => null,
            'buy_now_currency' => null,
            'current_bid_price' => null,
            'current_bid_currency' => null,
            'operational_status' => null,
        ];
    }

    private function shouldForceRetry(?array $result): ?string
    {
        if ($result === null) {
            return 'empty-result';
        }

        if ($this->copartBlocked) {
            return 'copart-blocked';
        }

        return null;
    }

    private function normalizeCopartUrl(string $url): ?string
    {
        if (!str_contains($url, 'copart.com/lot/')) {
            return null;
        }

        if (!preg_match('/copart\\.com\\/lot\\/(\\d+)/i', $url, $m)) {
            return null;
        }

        $lotId = $m[1];

        return 'https://www.copart.com/lot/' . $lotId;
    }

    private function minCopartPhotos(): int
    {
        return max(1, (int) config('services.copart.min_photos', 8));
    }

    private function parseIAAI(string $url): ?array
    {
        try {
            $response = Http::timeout(10)
                ->retry(1, 500)
                ->withHeaders($this->iaaiRequestHeaders($url))
                ->withCookies($this->iaaiCookies(), '.iaai.com')
                ->withOptions([
                    'verify' => false,
                    'allow_redirects' => true,
                    'http_errors' => false,
                ])
                ->get($url);

            if (!$response->successful()) {
                Log::warning('⚠️ IAAI HTTP status: ' . $response->status(), ['url' => $url]);
                return null;
            }

            $html = $response->body();
            if (!is_string($html) || trim($html) === '') {
                Log::warning('⚠️ IAAI empty response body', ['url' => $url]);
                return null;
            }

            $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5);

            if ($this->iaaiPageIndicatesMissingLot($html)) {
                Log::info('ℹ️ IAAI reports lot not found', ['url' => $url]);
                return null;
            }

            $state = $this->extractIaaiState($html);

            [$titleYear, $titleMake, $titleModel] = $this->parseIaaiTitle($this->extractMetaContent($html, 'og:title'));

            $make = $titleMake ?: $this->matchFirst($html, [
                '/data-uname="lotdetailmake"[^>]*>\s*([^<]+)/i',
                '/"vehicleMake"\s*:\s*"([^"]+)"/i',
                '/"make"\s*:\s*"([^"]+)"/i',
            ]);

            $model = $titleModel ?: $this->matchFirst($html, [
                '/data-uname="lotdetailmodel"[^>]*>\s*([^<]+)/i',
                '/"vehicleModel"\s*:\s*"([^"]+)"/i',
                '/"model"\s*:\s*"([^"]+)"/i',
            ]);

            $year = $titleYear ?: $this->extractYear($html);

            if ($state) {
                $stateMake = $this->normalizeText((string) data_get($state, 'vehicleDetails.vehicleSummary.make'));
                if ($stateMake && !$make) {
                    $make = $this->titleCase($stateMake);
                }

                $stateModel = $this->normalizeText((string) data_get($state, 'vehicleDetails.vehicleSummary.model'));
                if ($stateModel && !$model) {
                    $model = $this->titleCase($stateModel);
                }

                $stateYear = data_get($state, 'vehicleDetails.vehicleSummary.year')
                    ?? data_get($state, 'vehicleDetails.vehicleSummary.modelYear');
                if ($stateYear && !$year && is_numeric($stateYear)) {
                    $year = (int) $stateYear;
                }
            }

            $mileageRaw = $this->matchFirst($html, [
                '/data-uname="lotdetailodometerreading"[^>]*>\s*([^<]+)/i',
                '/Odometer(?: Reading)?:\s*<\/span>\s*<span[^>]*>\s*([^<]+)/i',
                '/"odometerReading"\s*:\s*"([^"]+)"/i',
            ]);
            $mileage = $this->extractNumericValue($mileageRaw);

            if (!$mileage && $state) {
                $stateMileage = data_get($state, 'vehicleDetails.vehicleSummary.odometerReading');
                if ($stateMileage) {
                    $mileage = $this->extractNumericValue((string) $stateMileage);
                }
            }

            $color = $this->matchFirst($html, [
                '/data-uname="lotdetailexteriorcolor"[^>]*>\s*([^<]+)/i',
                '/Exterior Color:\s*<\/span>\s*<span[^>]*>\s*([^<]+)/i',
                '/"exteriorColor"\s*:\s*"([^"]+)"/i',
            ]);

            if (!$color && $state) {
                $stateColor = $this->normalizeText((string) data_get($state, 'vehicleDetails.vehicleSummary.exteriorColor'));
                if ($stateColor) {
                    $color = $stateColor;
                }
            }

            $transmission = $this->matchFirst($html, [
                '/data-uname="lotdetailtransmission"[^>]*>\s*([^<]+)/i',
                '/Transmission:\s*<\/span>\s*<span[^>]*>\s*([^<]+)/i',
                '/"transmission"\s*:\s*"([^"]+)"/i',
            ]) ?: 'automatic';

            if ($state) {
                $stateTransmission = $this->normalizeText((string) data_get($state, 'vehicleDetails.vehicleSummary.transmission'));
                if ($stateTransmission) {
                    $transmission = strtolower($stateTransmission);
                }
            }

            $fuelType = $this->matchFirst($html, [
                '/data-uname="lotdetailfuletype"[^>]*>\s*([^<]+)/i',
                '/data-uname="lotdetailfueltype"[^>]*>\s*([^<]+)/i',
                '/Fuel Type:\s*<\/span>\s*<span[^>]*>\s*([^<]+)/i',
                '/"fuelType"\s*:\s*"([^"]+)"/i',
            ]) ?: 'gasoline';

            if ($state) {
                $stateFuel = $this->normalizeText((string) data_get($state, 'vehicleDetails.vehicleSummary.fuelType'));
                if ($stateFuel) {
                    $fuelType = strtolower($stateFuel);
                }
            }

            $bodyType = $this->matchFirst($html, [
                '/data-uname="lotdetailvehicletype"[^>]*>\s*([^<]+)/i',
                '/data-uname="lotdetailbodytype"[^>]*>\s*([^<]+)/i',
                '/Body Style:\s*<\/span>\s*<span[^>]*>\s*([^<]+)/i',
                '/"bodyStyle"\s*:\s*"([^"]+)"/i',
            ]);

            if (!$bodyType && $state) {
                $stateBody = $this->normalizeText((string) data_get($state, 'vehicleDetails.vehicleSummary.bodyStyle'));
                if ($stateBody) {
                    $bodyType = $stateBody;
                }
            }

            $engineStr = $this->matchFirst($html, [
                '/data-uname="lotdetailengine"[^>]*>\s*([^<]+)/i',
                '/Engine Type:\s*<\/span>\s*<span[^>]*>\s*([^<]+)/i',
                '/"engineType"\s*:\s*"([^"]+)"/i',
            ]);

            if (!$engineStr && $state) {
                $stateEngine = $this->normalizeText((string) data_get($state, 'vehicleDetails.vehicleSummary.engineType'));
                if ($stateEngine) {
                    $engineStr = $stateEngine;
                }
            }

            $auctionEndsAtRaw = $this->matchFirst($html, [
                '/data-uname="lotdetailsaledate"[^>]*>\s*([^<]+)/i',
                '/Sale Date:\s*<\/span>\s*<span[^>]*>\s*([^<]+)/i',
                '/"saleDate"\s*:\s*"([^"]+)"/i',
            ]);
            $auctionEndsAt = $this->parseIaaiAuctionDate($auctionEndsAtRaw);

            if (!$auctionEndsAt && $state) {
                $stateSaleDate = data_get($state, 'vehicleDetails.vehicleSummary.saleDate');
                if ($stateSaleDate) {
                    $auctionEndsAt = $this->parseIaaiAuctionDate((string) $stateSaleDate);
                }
            }

            $photos = $this->extractIaaiPhotos($html, $url, $state);
            if (empty($photos)) {
                $placeholderUrl = 'https://via.placeholder.com/800x600/e5e7eb/6b7280?text=No+Image+Available';
                $photos = [url('/proxy/image?u=' . rawurlencode($placeholderUrl))];
            }

            $data = [
                'make' => $make ? $this->titleCase($make) : 'Неизвестно',
                'model' => $model ? $this->titleCase($model) : 'Неизвестно',
                'year' => $year ?: date('Y'),
                'mileage' => $mileage,
                'exterior_color' => $color ? $this->titleCase($color) : null,
                'transmission' => strtolower($transmission) === 'manual' ? 'manual' : 'automatic',
                'fuel_type' => $fuelType ? strtolower($fuelType) : 'gasoline',
                'engine_displacement_cc' => $this->parseEngineString($engineStr),
                'body_type' => $bodyType ? $this->titleCase($bodyType) : null,
                'photos' => array_values($photos),
                'source_auction_url' => $url,
                'auction_ends_at' => $auctionEndsAt ? $auctionEndsAt->toIso8601String() : null,
            ];

            if (empty($data['make']) || empty($data['model'])) {
                Log::warning('⚠️ IAAI parsed without essential fields', ['url' => $url]);
                return null;
            }

            Log::info('✅ IAAI parsed vehicle', [
                'make' => $data['make'],
                'model' => $data['model'],
                'year' => $data['year'],
                'photos' => count($data['photos']),
            ]);

            return $data;
        } catch (\Throwable $e) {
            Log::error('❌ IAAI parsing error: ' . $e->getMessage(), ['url' => $url]);
            return null;
        }
    }

    private function parseEngineString(?string $engineStr): ?int
    {
        if (!$engineStr) {
            return null;
        }

        // Parse engine size in liters (e.g., "2.0L")
        if (preg_match('/(\d+\.?\d*)\s*[lL]/', $engineStr, $eM)) {
            return (int) ((float) $eM[1] * 1000);
        }
        // Parse engine size in cc (e.g., "1500cc")
        elseif (preg_match('/(\d{3,4})\s*cc/i', $engineStr, $ccM)) {
            return (int) $ccM[1];
        }

        return null;
    }

    private function extractCopartBuyNowPrice(array $details): ?float
    {
        $value = $this->searchNestedByKeyFragments($details, ['buy', 'now'], true);

        if ($value === null) {
            $value = $this->searchNestedByKeyFragments($details, ['buy', 'today'], true);
        }

        return $value;
    }

    private function extractCopartBuyNowCurrency(array $details): ?string
    {
        $fragmentsList = [
            ['buy', 'now', 'curr'],
            ['buy', 'now', 'currency'],
            ['currency'],
            ['curr'],
            ['cu'],
        ];

        foreach ($fragmentsList as $fragments) {
            $candidate = $this->searchNestedByKeyFragments($details, $fragments);
            $currency = $this->sanitizeCurrencyCode($candidate);
            if ($currency !== null) {
                return $currency;
            }
        }

        return null;
    }

    private function extractCopartCurrentBid(array $details): ?float
    {
        $fragmentsList = [
            ['current', 'bid'],
            ['curr', 'bid'],
            ['high', 'bid'],
            ['bid', 'amount'],
            ['bid', 'value'],
        ];

        foreach ($fragmentsList as $fragments) {
            $candidate = $this->searchNestedByKeyFragments($details, $fragments, true);
            if ($candidate !== null) {
                return $candidate;
            }
        }

        return null;
    }

    private function extractCopartCurrentBidCurrency(array $details): ?string
    {
        $fragmentsList = [
            ['current', 'bid', 'curr'],
            ['current', 'bid', 'currency'],
            ['high', 'bid', 'curr'],
            ['bid', 'currency'],
        ];

        foreach ($fragmentsList as $fragments) {
            $candidate = $this->searchNestedByKeyFragments($details, $fragments);
            $currency = $this->sanitizeCurrencyCode($candidate);
            if ($currency !== null) {
                return $currency;
            }
        }

        return null;
    }

    private function extractCopartOperationalStatus(array $details): ?string
    {
        foreach ($this->collectStringLeaves($details) as $value) {
            $status = $this->normalizeOperationalStatus($value);
            if ($status !== null) {
                return $status;
            }
        }

        return null;
    }

    private function extractCopartPrimaryDamage(array $details): ?string
    {
        $candidates = [
            data_get($details, 'dd'),
            data_get($details, 'pd'),
            data_get($details, 'damageDescription'),
            data_get($details, 'primaryDamage'),
            data_get($details, 'sdd'),
            data_get($details, 'secondaryDamage'),
            $this->searchNestedByKeyFragments($details, ['damage']),
            $this->searchNestedByKeyFragments($details, ['primary', 'damage']),
        ];

        foreach ($candidates as $value) {
            $normalized = $this->normalizeDamage($value);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        foreach ($this->collectStringLeaves($details) as $leaf) {
            if (!is_string($leaf)) {
                continue;
            }

            if (stripos($leaf, 'damage') !== false) {
                $normalized = $this->normalizeDamage($leaf);
                if ($normalized !== null) {
                    return $normalized;
                }
            }
        }

        return null;
    }

    private function extractCopartPrimaryDamageFromHtml(?string $html): ?string
    {
        if (!$html) {
            return null;
        }

        $patterns = [
            '/data-uname="lotdetailprimarydamage"[^>]*>\s*([^<]+)/i',
            '/data-uname="lotdetaildamagedescription"[^>]*>\s*([^<]+)/i',
            '/data-uname="lotdetailPrimarydamagevalue"[^>]*>\s*([^<]+)/i',
            '/Primary Damage:\s*<\/span>\s*<span[^>]*>\s*([^<]+)/i',
            '/Damage Description:\s*<\/span>\s*<span[^>]*>\s*([^<]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches) && isset($matches[1])) {
                $normalized = $this->normalizeDamage($matches[1]);
                if ($normalized !== null) {
                    return $normalized;
                }
            }
        }

        return null;
    }

    /**
     * @return array{0:?float,1:?string}
     */
    private function extractCopartBuyNowFromHtml(?string $html): array
    {
        if (!$html) {
            return [null, null];
        }

        $patterns = [
            '/data-uname="lotdetailbuybid"[^>]*>\s*([^<]+)/i',
            '/Buy\s+(?:It\s+)?Now[^0-9$€£A-Z]*([\$€£])?\s*([\d.,]+)/i',
            '/Buy\s+(?:It\s+)?Now\s+Price[^0-9$€£A-Z]*([\$€£])?\s*([\d.,]+)/i',
            '/Buy\s+Now\s+Price[^0-9$€£A-Z]*([\$€£])?\s*([\d.,]+)/i',
            '/data-uname="lotdetailbuyitnowprice"[^>]*>\s*([^<]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $rawValue = $matches[count($matches) - 1] ?? null;
                $price = $this->parseMoneyValue($rawValue);

                if ($price !== null) {
                    $symbol = $matches[1] ?? null;
                    $currency = $this->currencyFromSymbol($symbol) ?? $this->detectCurrencyCodeFromContext($matches[0] ?? null);

                    return [$price, $currency];
                }
            }
        }

        if (preg_match('/"buy(?:It)?Now(?:Price|Amount|Bid)"\s*:\s*"?(?<price>[\d.,]+)/i', $html, $matches)) {
            $price = $this->parseMoneyValue($matches['price'] ?? null);
            if ($price !== null) {
                $currency = null;
                if (preg_match('/"buy(?:It)?Now(?:Currency|Curr|CurrCode)"\s*:\s*"(?<curr>[A-Z]{3,5})"/i', $html, $currMatch)) {
                    $currency = strtoupper(trim($currMatch['curr']));
                }

                return [$price, $currency];
            }
        }

        return [null, null];
    }

    /**
     * @return array{0:?float,1:?string}
     */
    private function extractCopartCurrentBidFromHtml(?string $html): array
    {
        if (!$html) {
            return [null, null];
        }

        $patterns = [
            '/data-uname="lotdetailcurrentbid"[^>]*>\s*([^<]+)/i',
            '/Current\s+Bid[^0-9$€£A-Z]*([\$€£])?\s*([\d.,]+)/i',
            '/Bid\s+Status[^0-9$€£A-Z]*([\$€£])?\s*([\d.,]+)/i',
            '/data-uname="lotdetailbidamount"[^>]*>\s*([^<]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $rawValue = $matches[count($matches) - 1] ?? null;
                $price = $this->parseMoneyValue($rawValue);

                if ($price !== null) {
                    $symbol = $matches[1] ?? null;
                    $currency = $this->currencyFromSymbol($symbol) ?? $this->detectCurrencyCodeFromContext($matches[0] ?? null);

                    return [$price, $currency];
                }
            }
        }

        if (preg_match('/"currentBid(?:Amount|Price|Value|Bid)"\s*:\s*"?(?<price>[\d.,]+)/i', $html, $matches)) {
            $price = $this->parseMoneyValue($matches['price'] ?? null);
            if ($price !== null) {
                $currency = null;
                if (preg_match('/"currentBid(?:Currency|Curr|CurrCode)"\s*:\s*"(?<curr>[A-Z]{3,5})"/i', $html, $currMatch)) {
                    $currency = strtoupper(trim($currMatch['curr']));
                }

                return [$price, $currency];
            }
        }

        return [null, null];
    }

    private function sanitizeCurrencyCode(mixed $value): ?string
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                $currency = $this->sanitizeCurrencyCode($item);
                if ($currency !== null) {
                    return $currency;
                }
            }
            return null;
        }

        if (!is_string($value)) {
            return null;
        }

        $normalized = strtoupper(trim($value));

        return preg_match('/^[A-Z]{3,5}$/', $normalized) ? $normalized : null;
    }

    private function extractOperationalStatusFromHtml(?string $html): ?string
    {
        if (!$html) {
            return null;
        }

        $patterns = [
            '/data-uname="lotdetailrunanddrive"[^>]*>\s*([^<]+)/i',
            '/data-uname="lotdetailoperationalstatus"[^>]*>\s*([^<]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches) && isset($matches[1])) {
                $status = $this->normalizeOperationalStatus($matches[1]);
                if ($status !== null) {
                    return $status;
                }
            }
        }

        return $this->normalizeOperationalStatus(strip_tags($html));
    }

    private function searchNestedByKeyFragments(array $data, array $fragments, bool $numericOnly = false): mixed
    {
        foreach ($data as $key => $value) {
            if (is_string($key)) {
                $lowerKey = strtolower($key);
                $matches = true;

                foreach ($fragments as $fragment) {
                    if (!str_contains($lowerKey, strtolower($fragment))) {
                        $matches = false;
                        break;
                    }
                }

                if ($matches) {
                    if ($numericOnly) {
                        $numeric = $this->extractFirstNumericLeaf($value);
                        if ($numeric !== null) {
                            return $numeric;
                        }
                        continue;
                    }

                    if (is_string($value)) {
                        return trim($value);
                    }

                    if (is_numeric($value)) {
                        return (string) $value;
                    }

                    return null;
                }
            }

            if (is_array($value)) {
                $found = $this->searchNestedByKeyFragments($value, $fragments, $numericOnly);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    private function extractFirstNumericLeaf(mixed $value): ?float
    {
        if (is_array($value)) {
            foreach ($value as $child) {
                $numeric = $this->extractFirstNumericLeaf($child);
                if ($numeric !== null) {
                    return $numeric;
                }
            }

            return null;
        }

        return $this->parseMoneyValue($value);
    }

    private function collectStringLeaves(array $data): array
    {
        $bucket = [];
        foreach ($data as $value) {
            if (is_array($value)) {
                $bucket = array_merge($bucket, $this->collectStringLeaves($value));
            } elseif (is_string($value)) {
                $bucket[] = $value;
            }
        }

        return $bucket;
    }

    private function normalizeOperationalStatus(?string $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            return null;
        }

        $dictionary = [
            'run and drive' => 'Run and Drive',
            'run & drive' => 'Run and Drive',
            'engine start program' => 'Engine Start Program',
            'enhanced vehicles' => 'Enhanced Vehicle',
            'enhanced vehicle' => 'Enhanced Vehicle',
            'stationary' => 'Stationary',
            'won\'t start' => "Won't Start",
            'does not start' => 'Does Not Start',
            'doesn\'t start' => "Doesn't Start",
            'no start' => 'No Start',
        ];

        foreach ($dictionary as $needle => $label) {
            if (str_contains($normalized, $needle)) {
                return $label;
            }
        }

        return null;
    }

    private function normalizeDamage(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $clean = trim(strip_tags($value));
        if ($clean === '') {
            return null;
        }

        $clean = preg_replace('/\s+/', ' ', $clean);

        return $this->titleCase($clean);
    }

    private function parseMoneyValue(mixed $value): ?float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $sanitized = preg_replace('/[^0-9.,-]/', '', $value);
        if ($sanitized === '') {
            return null;
        }

        $normalized = str_replace(',', '', $sanitized);

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    private function currencyFromSymbol(?string $symbol): ?string
    {
        return match ($symbol) {
            '$' => 'USD',
            '€' => 'EUR',
            '£' => 'GBP',
            default => null,
        };
    }

    private function detectCurrencyCodeFromContext(?string $context): ?string
    {
        if (!is_string($context)) {
            return null;
        }

        if (stripos($context, 'usd') !== false) {
            return 'USD';
        }

        if (stripos($context, 'eur') !== false) {
            return 'EUR';
        }

        if (stripos($context, 'gbp') !== false) {
            return 'GBP';
        }

        return null;
    }

    /**
     * @return array<string,string>
     */
    private function iaaiRequestHeaders(string $referer): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
            'Accept-Language' => 'en-US,en;q=0.9,ru;q=0.8',
            'Cache-Control' => 'no-cache',
            'Pragma' => 'no-cache',
            'Referer' => $referer ?: 'https://www.iaai.com/',
            'Connection' => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
        ];
    }

    /**
     * @return array<string,string>
     */
    private function iaaiCookies(): array
    {
        $raw = config('services.iaai.cookies') ?? env('IAAI_COOKIES');
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $cookies = [];
        $pairs = array_filter(array_map('trim', explode(';', $raw)));
        foreach ($pairs as $pair) {
            $pos = strpos($pair, '=');
            if ($pos === false) {
                continue;
            }

            $name = trim(substr($pair, 0, $pos));
            $value = trim(substr($pair, $pos + 1));

            if ($name !== '') {
                $cookies[$name] = $value;
            }
        }

        return $cookies;
    }

    private function iaaiPageIndicatesMissingLot(string $html): bool
    {
        $phrases = [
            'vehicle not found',
            'lot not found',
            'no vehicle found',
            'we are unable to locate this vehicle',
            'vehicle you are looking for is no longer available',
        ];

        foreach ($phrases as $needle) {
            if (stripos($html, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{0:?int,1:?string,2:?string}
     */
    private function parseIaaiTitle(?string $title): array
    {
        $normalized = $this->normalizeText($title);
        if ($normalized === null) {
            return [null, null, null];
        }

        $normalized = preg_replace('/\\s+for sale.*$/i', '', $normalized);
        $normalized = preg_replace('/\\s*[-|]\\s*iaai.*$/i', '', $normalized);

        if (preg_match('/^(?P<year>(19|20)\\d{2})\\s+(?P<rest>.+)$/', $normalized, $matches)) {
            $year = (int) $matches['year'];
            $rest = trim($matches['rest']);
            if ($rest === '') {
                return [$year, null, null];
            }

            $parts = preg_split('/\\s+/', $rest, 2);
            $make = $parts[0] ?? null;
            $model = $parts[1] ?? null;

            return [
                $year,
                $make ? $this->titleCase($make) : null,
                $model ? $this->titleCase($model) : null,
            ];
        }

        return [null, null, null];
    }

    private function extractMetaContent(string $html, string $property): ?string
    {
        $patterns = [
            sprintf('/<meta[^>]+property=["\']%s["\'][^>]+content=["\']([^"\']+)["\']/i', preg_quote($property, '/')),
            sprintf('/<meta[^>]+name=["\']%s["\'][^>]+content=["\']([^"\']+)["\']/i', preg_quote($property, '/')),
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                return $this->normalizeText($matches[1]);
            }
        }

        return null;
    }

    private function matchFirst(string $html, array $patterns): ?string
    {
        foreach ($patterns as $pattern) {
            if (@preg_match($pattern, $html, $matches) && isset($matches[1])) {
                $value = strip_tags($matches[1]);
                $normalized = $this->normalizeText($value);
                if ($normalized !== null) {
                    return $normalized;
                }
            }
        }

        return null;
    }

    private function extractYear(string $html): ?int
    {
        $yearText = $this->matchFirst($html, [
            '/data-uname="lotdetailyear"[^>]*>\\s*([^<]+)/i',
            '/"vehicleYear"\\s*:\\s*"([^"]+)"/i',
            '/"year"\\s*:\\s*"([^"]+)"/i',
        ]);

        if ($yearText && preg_match('/(19|20)\\d{2}/', $yearText, $matches)) {
            return (int) $matches[0];
        }

        return null;
    }

    private function extractNumericValue(?string $value): ?int
    {
        if (!is_string($value)) {
            return null;
        }

        $digits = preg_replace('/[^\\d]/', '', $value);
        if ($digits === '' || !ctype_digit($digits)) {
            return null;
        }

        $number = (int) $digits;

        return $number > 0 ? $number : null;
    }

    private function parseIaaiAuctionDate(?string $value): ?Carbon
    {
        $normalized = $this->normalizeText($value);
        if ($normalized === null) {
            return null;
        }

        if (stripos($normalized, 'tbd') !== false) {
            return null;
        }

        $formats = [
            'm/d/Y h:i A',
            'm/d/Y H:i',
            'd.m.Y H:i',
            'Y-m-d H:i',
            'M d, Y h:i A',
            'M d Y h:i A',
        ];

        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $normalized, 'America/New_York');
                return $parsed->setTimezone(config('app.timezone'));
            } catch (\Throwable $e) {
                continue;
            }
        }

        try {
            $parsed = Carbon::parse($normalized, 'America/New_York');
            return $parsed->setTimezone(config('app.timezone'));
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @return array<int,string>
     */
    private function extractIaaiPhotos(string $html, string $sourceUrl, ?array $state = null): array
    {
        $photos = [];

        if ($state) {
            $stateCollections = [
                data_get($state, 'vehicleDetails.vehicleSummary.photos'),
                data_get($state, 'vehicleDetails.vehicleSummary.images'),
                data_get($state, 'vehicleDetails.media.photos'),
                data_get($state, 'vehicleDetails.gallery.photos'),
                data_get($state, 'media.photos'),
            ];

            foreach ($stateCollections as $collection) {
                if (empty($collection) || !is_iterable($collection)) {
                    continue;
                }

                foreach ($collection as $item) {
                    $resolved = $this->resolveIaaiPhotoUrl($item);
                    if ($resolved) {
                        $photos[$resolved] = $this->buildProxyImageUrl($resolved, $sourceUrl);
                    }
                }
            }
        }

        if (preg_match_all('/https?:\/\/(?:content|images|photos)\.iaai\.(?:com|net)\/[^"\'>\s]+/i', $html, $matches)) {
            foreach ($matches[0] as $rawUrl) {
                $normalized = $this->normalizeIaaiImageUrl($rawUrl);
                if ($normalized) {
                    $photos[$normalized] = $this->buildProxyImageUrl($normalized, $sourceUrl);
                }
            }
        }

        if (preg_match_all('/data-src=["\']([^"\']+\.?(?:jpe?g|png|webp)[^"\']*)["\']/i', $html, $lazyMatches)) {
            foreach ($lazyMatches[1] as $rawUrl) {
                $normalized = $this->normalizeIaaiImageUrl($rawUrl);
                if ($normalized) {
                    $photos[$normalized] = $this->buildProxyImageUrl($normalized, $sourceUrl);
                }
            }
        }

        if (preg_match_all('/src=["\']([^"\']+\.?(?:jpe?g|png|webp)[^"\']*)["\']/i', $html, $srcMatches)) {
            foreach ($srcMatches[1] as $rawUrl) {
                $normalized = $this->normalizeIaaiImageUrl($rawUrl);
                if ($normalized) {
                    $photos[$normalized] = $this->buildProxyImageUrl($normalized, $sourceUrl);
                }
            }
        }

        if (preg_match_all('/srcset=["\']([^"\']+)["\']/i', $html, $srcsetMatches)) {
            foreach ($srcsetMatches[1] as $srcset) {
                $candidates = preg_split('/\s*,\s*/', $srcset);
                foreach ($candidates as $candidate) {
                    $parts = preg_split('/\s+/', trim($candidate));
                    $urlPart = $parts[0] ?? null;
                    if (!$urlPart) {
                        continue;
                    }
                    $normalized = $this->normalizeIaaiImageUrl($urlPart);
                    if ($normalized) {
                        $photos[$normalized] = $this->buildProxyImageUrl($normalized, $sourceUrl);
                    }
                }
            }
        }

        if (preg_match_all('/background-image:\s*url\(([^)]+)\)/i', $html, $bgMatches)) {
            foreach ($bgMatches[1] as $rawUrl) {
                $clean = trim($rawUrl, '"\' ');
                $normalized = $this->normalizeIaaiImageUrl($clean);
                if ($normalized) {
                    $photos[$normalized] = $this->buildProxyImageUrl($normalized, $sourceUrl);
                }
            }
        }

        if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $metaMatch)) {
            $normalized = $this->normalizeIaaiImageUrl($metaMatch[1]);
            if ($normalized) {
                $photos[$normalized] = $this->buildProxyImageUrl($normalized, $sourceUrl);
            }
        }

        return array_values($photos);
    }

    private function normalizeIaaiImageUrl(string $url): ?string
    {
        $decoded = html_entity_decode($url, ENT_QUOTES | ENT_HTML5);
        $normalized = $this->normalizeText($decoded);
        if ($normalized === null) {
            return null;
        }

        if (str_starts_with($normalized, '//')) {
            $normalized = 'https:' . $normalized;
        }

        if (!str_starts_with($normalized, 'http')) {
            if (preg_match('/^(?:content|images|photos)\.iaai\.(?:com|net)/i', $normalized)) {
                $normalized = 'https://' . $normalized;
            } elseif (str_starts_with($normalized, '/')) {
                $normalized = 'https://content.iaai.com' . $normalized;
            } else {
                $normalized = 'https://content.iaai.com/' . ltrim($normalized, '/');
            }
        }

        $parts = parse_url($normalized);
        if (!is_array($parts) || empty($parts['host']) || empty($parts['path'])) {
            return null;
        }

        if (!preg_match('/\\.(?:jpe?g|png|gif|webp)(?:$|[?&#])/i', $parts['path'])) {
            return null;
        }

        return $normalized;
    }

    private function buildProxyImageUrl(string $source, ?string $referer = null): string
    {
        $query = ['u' => $source];

        if ($referer) {
            $query['r'] = $referer;
        }

        return url('/proxy/image?' . http_build_query($query));
    }

    private function resolveIaaiPhotoUrl(mixed $photo): ?string
    {
        if (is_string($photo)) {
            return $this->normalizeIaaiImageUrl($photo);
        }

        if (!is_array($photo)) {
            return null;
        }

        $candidates = [
            'url', 'imageUrl', 'imageURL', 'image', 'path', 'full', 'fullUrl',
            'fullURL', 'large', 'largeUrl', 'largeURL', 'photoUrl', 'cdnUrl',
            'originalUrl', 'originalURL', 'src',
        ];

        foreach ($candidates as $key) {
            if (!empty($photo[$key]) && is_string($photo[$key])) {
                $normalized = $this->normalizeIaaiImageUrl($photo[$key]);
                if ($normalized) {
                    return $normalized;
                }
            }
        }

        if (isset($photo['links']) && is_array($photo['links'])) {
            foreach (['full', 'large', 'original', 'xl', 'xlLarge'] as $linkKey) {
                $linkValue = $photo['links'][$linkKey] ?? null;
                if (is_string($linkValue)) {
                    $normalized = $this->normalizeIaaiImageUrl($linkValue);
                    if ($normalized) {
                        return $normalized;
                    }
                }
            }
        }

        if (isset($photo['sizes']) && is_array($photo['sizes'])) {
            foreach (['full', 'large', 'xl'] as $sizeKey) {
                $sizeValue = $photo['sizes'][$sizeKey] ?? null;
                if (is_string($sizeValue)) {
                    $normalized = $this->normalizeIaaiImageUrl($sizeValue);
                    if ($normalized) {
                        return $normalized;
                    }
                }
            }
        }

        return null;
    }

    private function extractIaaiState(string $html): ?array
    {
        $marker = 'window.__INITIAL_STATE__';
        $pos = strpos($html, $marker);
        if ($pos === false) {
            return null;
        }

        $equalsPos = strpos($html, '=', $pos);
        if ($equalsPos === false) {
            return null;
        }

        $jsonStart = strpos($html, '{', $equalsPos);
        if ($jsonStart === false) {
            return null;
        }

        $jsonFragment = $this->extractJsonObject(substr($html, $jsonStart));
        if ($jsonFragment === null) {
            return null;
        }

        $decoded = json_decode($jsonFragment, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            Log::warning('⚠️ Failed to decode IAAI state JSON', ['error' => json_last_error_msg()]);
            return null;
        }

        return $decoded;
    }

    private function extractJsonObject(string $text): ?string
    {
        $text = ltrim($text);
        if ($text === '' || $text[0] !== '{') {
            return null;
        }

        $length = strlen($text);
        $depth = 0;
        $inString = false;
        $escape = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $text[$i];

            if ($inString) {
                if ($escape) {
                    $escape = false;
                    continue;
                }
                if ($char === '\\') {
                    $escape = true;
                    continue;
                }
                if ($char === '"') {
                    $inString = false;
                }
                continue;
            }

            if ($char === '"') {
                $inString = true;
                continue;
            }

            if ($char === '{') {
                $depth++;
                continue;
            }

            if ($char === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($text, 0, $i + 1);
                }
            }
        }

        return null;
    }

    private function titleCase(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return $value;
        }

        $upper = strtoupper($value);
        if ($upper === $value && strlen($value) <= 4) {
            return $upper;
        }

        return ucwords(strtolower($value));
    }

private function normalizeText(?string $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        $normalized = preg_replace('/\\s+/u', ' ', $trimmed);
        if ($normalized === null) {
            return null;
        }

        if (str_contains($normalized, '\\/')) {
            $normalized = str_replace('\\/', '/', $normalized);
        }

        if (str_contains($normalized, '\\u002F') || str_contains($normalized, '\\u002f')) {
            $normalized = str_ireplace('\\u002f', '/', $normalized);
        }

        if (str_contains($normalized, '\\')) {
            $normalized = stripcslashes($normalized);
        }

        return $normalized;
    }

    /**
     * Try several alternate Copart endpoints that sometimes expose images when the primary API fails.
     *
     * @param string $lotId
     * @param string $sourceUrl
     * @return array<int, array<string,string>>
     */
    /**
     * @param array<string,string> $baseHeaders
     */
    private function fetchImagesViaAlternateApis(string $lotId, string $sourceUrl, array $baseHeaders, ?CookieJar $cookieJar = null): array
    {
        $endpoints = [
            "https://www.copart.com/public/data/lotDetails/json/{$lotId}?requestType=en_US",
            "https://www.copart.com/public/data/lotDetails/json/{$lotId}",
            "https://www.copart.com/public/data/lotdetails/lot/{$lotId}",
        ];

        $images = [];

        $headers = $baseHeaders;
        $headers['Referer'] = $sourceUrl;
        $headers['Accept'] = 'application/json, text/plain, */*';
        $headers['Cache-Control'] = $headers['Cache-Control'] ?? 'no-cache';
        $headers['Accept-Language'] = $headers['Accept-Language'] ?? 'en-US,en;q=0.9';

        foreach ($endpoints as $endpoint) {
            try {
                $payload = $this->requestCopartJson($endpoint, $headers, $cookieJar);
                if (!is_array($payload)) {
                    continue;
                }

                $rawUrls = $this->extractImagesFromData($payload);

                foreach ($rawUrls as $rawUrl) {
                    $normalized = $this->normalizeCopartImageUrl($rawUrl);
                    if (!$normalized || $this->isPlaceholderImage($normalized)) {
                        continue;
                    }

                    $images[$normalized] = [
                        'fullUrl' => $normalized,
                        'highResUrl' => $normalized,
                        'thumbnailUrl' => $normalized,
                    ];
                }

                if (!empty($images)) {
                    Log::info('✅ Alternate endpoint returned images', [
                        'endpoint' => $endpoint,
                        'count' => count($images),
                    ]);
                    break;
                }
            } catch (\Throwable $e) {
                Log::warning('⚠️ Alternate endpoint failed: ' . $e->getMessage(), [
                    'endpoint' => $endpoint,
                ]);
            }
        }

        return array_values($images);
    }

    /**
     * @param array<string,mixed> $details
     */
    private function detectCopartAuctionEnd(array $details): ?Carbon
    {
        try {
            $timestamp = $details['ad'] ?? null; // auction date (ms)
            if (!$timestamp) {
                return null;
            }

            $timezone = $details['ianaTimeZone'] ?? $details['tz'] ?? 'America/New_York';
            $date = Carbon::createFromTimestampMs($timestamp, $timezone);

            $timeStr = $details['at'] ?? null; // auction time e.g. 10:00:00
            if (is_string($timeStr) && preg_match('/^\d{2}:\d{2}:\d{2}$/', $timeStr)) {
                $date = Carbon::parse($date->toDateString() . ' ' . $timeStr, $timezone);
            }

            // Some lots include minutes left (timeLeft). If date already past but есть timeLeft
            if (($details['timeLeft'] ?? null) && isset($details['timeLeft']['milliseconds'])) {
                $withOffset = Carbon::now($timezone)->addMilliseconds((int) $details['timeLeft']['milliseconds']);
                if ($withOffset->greaterThan($date)) {
                    $date = $withOffset;
                }
            }

            return $date->setTimezone(config('app.timezone'));
        } catch (\Throwable $e) {
            Log::warning('⚠️ Failed to detect Copart auction end: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Recursively iterates through data to detect image URLs that Copart returns in nested structures.
     *
     * @param array<mixed> $data
     * @return array<int, string>
     */
    private function extractImagesFromData(array $data): array
    {
        $bucket = [];
        $this->walkImageData($data, $bucket);

        return $bucket;
    }

    /**
     * @param mixed $value
     * @param array<int,string> $bucket
     */
    private function walkImageData(mixed $value, array &$bucket): void
    {
        if (is_array($value)) {
            $candidateKeys = ['link', 'url', 'fullUrl', 'highResUrl', 'thumbnailUrl', 'imageUrl', 'src', 'path'];
            foreach ($candidateKeys as $key) {
                if (!empty($value[$key]) && is_string($value[$key])) {
                    $bucket[] = $value[$key];
                }
            }

            foreach ($value as $nested) {
                $this->walkImageData($nested, $bucket);
            }
            return;
        }

        if (is_string($value)) {
            $lower = strtolower($value);
            if ((str_contains($lower, 'cs.copart.com') || str_contains($lower, 'pics.copart.com')) &&
                preg_match('/\.(jpg|jpeg|png|webp)(?:\?|$)/i', $value)) {
                $bucket[] = $value;
            }
        }
    }

    /**
     * Normalise Copart image URLs so that further processing can rely on absolute HTTPS links.
     */
    private function normalizeCopartImageUrl(string $url): ?string
    {
        $url = trim(html_entity_decode($url));
        if ($url === '') {
            return null;
        }

        $url = str_replace(['\\/', '\\u002F'], '/', $url);

        if (str_starts_with($url, '//')) {
            $url = 'https:' . $url;
        }

        if (!preg_match('/^https?:\/\//i', $url)) {
            if (str_starts_with($url, 'cs.copart.com') || str_starts_with($url, 'pics.copart.com')) {
                $url = 'https://' . $url;
            } else {
                $url = 'https://cs.copart.com/' . ltrim($url, '/');
            }
        }

        // Parse components to tidy duplicated host/path fragments often returned by Copart HTML
        $parsed = parse_url($url);
        if ($parsed !== false && !empty($parsed['host'])) {
            $host = strtolower($parsed['host']);
            $path = $parsed['path'] ?? '';

            // If Copart returns path that still includes the host fragment, strip it
            $path = preg_replace('#^/+(cs|pics)\.copart\.com/#i', '/', $path);
            // Collapse multiple slashes while keeping leading single slash
            $path = '/' . ltrim(preg_replace('#/{2,}#', '/', $path), '/');

            // Ensure host is one of expected Copart hosts; otherwise keep original
            if (preg_match('#^(cs|pics)\.copart\.com$#i', $host)) {
                $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
                $url = 'https://' . $host . $path . $query;
            }
        }

        // Promote thumbnails to full-size where obvious
        $url = preg_replace('/_(thn|thb|tmb)(\.(?:jpg|jpeg|png|webp))(?:$|\?)/i', '_ful$2', $url);

        if (preg_match('/_(ful|thb|hrs|tmb)$/i', $url)) {
            $url .= '.jpg';
        }

        $path = parse_url($url, PHP_URL_PATH) ?? '';
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if ($extension !== '' && ! in_array($extension, $allowedExtensions, true)) {
            return null;
        }

        return $url;
    }

    /**
     * Quick heuristic to avoid placeholders and "no image" stand-ins.
     */
    private function isPlaceholderImage(string $url): bool
    {
        $lower = strtolower($url);

        return str_contains($lower, 'placeholder')
            || str_contains($lower, 'noimage')
            || str_contains($lower, 'no_image')
            || str_contains($lower, 'no+image');
    }

    /**
     * When DOM-based scraping returns nothing, fallback to aggressive pattern searches.
     *
     * @return array<int,string>
     */
    private function additionalHtmlImageScan(string $html): array
    {
        $results = [];

        preg_match_all('/https?:\/\/(?:cs|pics)\.copart\.com\/[^\s"\'<>]+\.(?:jpg|jpeg|png|webp)/i', $html, $directMatches);
        if (!empty($directMatches[0])) {
            foreach ($directMatches[0] as $url) {
                $results[] = html_entity_decode($url);
            }
        }

        preg_match_all('/"(https?:\\\\\/\\\\\/(?:cs|pics)\.copart\.com\\\\\/[^"]+\.(?:jpg|jpeg|png|webp))"/i', $html, $encodedMatches);
        if (!empty($encodedMatches[1])) {
            foreach ($encodedMatches[1] as $encodedUrl) {
                $results[] = stripcslashes($encodedUrl);
            }
        }

        if (empty($results)) {
            preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $html, $scriptBlocks);
            foreach ($scriptBlocks[1] ?? [] as $scriptContent) {
                if (preg_match_all('/https?:\\\\?\/\\\\?\/(?:cs|pics)\.copart\.com\\\\?\/[^"\'\\\\]+(?:jpg|jpeg|png|webp)/i', $scriptContent, $scriptMatches)) {
                    foreach ($scriptMatches[0] as $scriptUrl) {
                        $results[] = stripcslashes($scriptUrl);
                    }
                }
            }
        }

        return $results;
    }

    private function fetchCopartLotHtml(string $url, ?CookieJar $cookieJar = null): ?string
    {
        if (!$cookieJar) {
            $cookieJar = CookieJar::fromArray([], '.copart.com');
        }

        $headers = [
            'User-Agent' => $this->copartUserAgent(),
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.5',
            'Accept-Encoding' => 'gzip, deflate',
            'DNT' => '1',
            'Connection' => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
            'Referer' => $this->copartRefererFallback(),
            'Origin' => $this->copartOrigin(),
        ];

        if ($cookieHeader = $this->getCopartCookieHeader()) {
            $headers['Cookie'] = $cookieHeader;
        }

        return $this->requestCopartBody($url, $headers, $cookieJar);
    }

    private function scrapeImagesFromHtml(string $url, string $lotId, ?CookieJar $cookieJar = null, ?string $preFetchedHtml = null): array
    {
        $imagesArray = [];

        if (!$cookieJar) {
            $cookieJar = CookieJar::fromArray([], '.copart.com');
        }

        try {
            Log::info('🔄 Attempting HTML scraping for lot: ' . $lotId);

            $html = $preFetchedHtml ?? $this->fetchCopartLotHtml($url, $cookieJar);
            if ($html === null) {
                Log::warning('⚠️ Failed to fetch HTML body for scraping');
                return [];
            }
            Log::info('🔄 HTML response length: ' . strlen($html) . ' chars');

            // First try regex patterns to find image URLs directly in HTML/JavaScript
            $foundUrls = [];

            // Pattern 1: Look for Copart image URLs in any format
            preg_match_all('/https?:\/\/[^"\'>\s]*(?:cs\.)?copart\.com[^"\'>\s]*\.(jpg|jpeg|png|webp)/i', $html, $matches);
            if (!empty($matches[0])) {
                $foundUrls = array_merge($foundUrls, $matches[0]);
                Log::info('🔍 Found ' . count($matches[0]) . ' URLs via regex pattern 1');
            }

            // Pattern 2: Look for image paths that might be relative
            preg_match_all('/\/[^"\'>\s]*(?:lpp|images?|photos?)[^"\'>\s]*\.(jpg|jpeg|png|webp)/i', $html, $matches);
            if (!empty($matches[0])) {
                foreach ($matches[0] as $relativePath) {
                    $foundUrls[] = 'https://cs.copart.com' . $relativePath;
                }
                Log::info('🔍 Found ' . count($matches[0]) . ' URLs via regex pattern 2');
            }

            // Pattern 3: Look for lot-specific image identifiers
            preg_match_all('/["\']([^"\']*' . $lotId . '[^"\']*\.(jpg|jpeg|png|webp))["\']/', $html, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $imagePath) {
                    if (!str_starts_with($imagePath, 'http')) {
                        $imagePath = 'https://cs.copart.com' . ltrim($imagePath, '/');
                    }
                    $foundUrls[] = $imagePath;
                }
                Log::info('🔍 Found ' . count($matches[1]) . ' URLs via regex pattern 3 (lot-specific)');
            }

            // If regex didn't find anything, try DOM parsing
            if (empty($foundUrls)) {
                Log::info('🔄 Regex found nothing, trying DOM parsing...');

                $dom = new \DOMDocument();
                libxml_use_internal_errors(true);
                @$dom->loadHTML($html);
                libxml_clear_errors();

                $xpath = new \DOMXPath($dom);

                // Try different selectors for Copart images
                $selectors = [
                    '//img[contains(@src, "copart")]/@src',
                    '//img[contains(@src, "lpp")]/@src',
                    '//img[contains(@data-src, "copart")]/@data-src',
                    '//img[contains(@data-original, "copart")]/@data-original',
                    '//*[@data-image]/@data-image',
                    '//*[contains(@class, "lot-image")]/@src',
                    '//*[contains(@class, "vehicle-image")]/@src',
                ];

                foreach ($selectors as $selector) {
                    $imageNodes = $xpath->query($selector);
                    foreach ($imageNodes as $node) {
                        $imgUrl = trim($node->nodeValue);
                        if (!empty($imgUrl)) {
                            $foundUrls[] = $imgUrl;
                        }
                    }
                }
                Log::info('🔍 DOM parsing found ' . count($foundUrls) . ' additional URLs');
            }

            // Clean and normalize URLs
            $cleanUrls = [];
            foreach ($foundUrls as $imgUrl) {
                $normalized = $this->normalizeCopartImageUrl($imgUrl);
                if (!$normalized || $this->isPlaceholderImage($normalized)) {
                    continue;
                }

                $lower = strtolower($normalized);
                if (str_contains($lower, 'logo') ||
                    str_contains($lower, 'icon') ||
                    str_contains($lower, 'banner') ||
                    str_contains($lower, 'avatar') ||
                    str_contains($lower, 'sprite')) {
                    continue;
                }

                $cleanUrls[] = $normalized;
            }

            if (empty($cleanUrls)) {
                $extraUrls = $this->additionalHtmlImageScan($html);
                foreach ($extraUrls as $extraUrl) {
                    $normalized = $this->normalizeCopartImageUrl($extraUrl);
                    if (!$normalized || $this->isPlaceholderImage($normalized)) {
                        continue;
                    }
                    $cleanUrls[] = $normalized;
                }
            }

            // Remove duplicates and convert to API format
            $cleanUrls = array_values(array_unique($cleanUrls));
            foreach ($cleanUrls as $imgUrl) {
                // Convert to the format expected by the main parsing code
                $imagesArray[] = [
                    'fullUrl' => $imgUrl,
                    'highResUrl' => $imgUrl,
                    'thumbnailUrl' => $imgUrl,
                ];
            }

            Log::info('🔄 Scraped ' . count($imagesArray) . ' images from HTML after cleaning');

            // Log first few URLs for debugging
            foreach (array_slice($imagesArray, 0, 3) as $i => $img) {
                Log::info('🔍 Scraped image ' . ($i + 1) . ': ' . substr($img['fullUrl'], 0, 100) . '...');
            }

        } catch (\Exception $e) {
            Log::warning('⚠️ HTML scraping error: ' . $e->getMessage());
        }

        return $imagesArray;
    }

    private function generatePotentialImageUrls(string $lotId): array
    {
        $imagesArray = [];

        try {
            Log::info('🎯 Generating potential image URLs for lot: ' . $lotId);

            // Copart uses different patterns for image URLs
            // Pattern analysis from working lots shows these formats:

            // Base patterns - we'll try different combinations
            $baseUrls = [
                'https://cs.copart.com/v1/AUTH_svc.pdoc00001/lpp/',
                'https://cs.copart.com/v1/AUTH_svc.pdoc00001/ids-c-prod-lpp/',
                'https://pics.copart.com/v1/AUTH_svc.pdoc00001/lpp/',
            ];

            // Common suffixes Copart uses
            $suffixes = ['_ful', '_thb', '_hrs', '_tmb'];
            $extensions = ['.jpg', '.jpeg'];

            // Try to generate some potential URLs
            $potentialUrls = [];

            foreach ($baseUrls as $baseUrl) {
                // Try direct lot ID patterns
                foreach ($suffixes as $suffix) {
                    foreach ($extensions as $ext) {
                        // Pattern 1: direct lot ID
                        $potentialUrls[] = $baseUrl . $lotId . $suffix . $ext;

                        // Pattern 2: lot ID with folder structure (first 4 digits)
                        $folder = substr($lotId, 0, 4);
                        $potentialUrls[] = $baseUrl . $folder . '/' . $lotId . $suffix . $ext;

                        // Pattern 3: lot ID with hash-like structure
                        $hash = md5($lotId);
                        $potentialUrls[] = $baseUrl . substr($hash, 0, 4) . '/' . $hash . '_' . $lotId . $suffix . $ext;
                    }
                }
            }

            Log::info('🎯 Generated ' . count($potentialUrls) . ' potential URLs to test');

            // Test each URL to see if it exists (but limit to avoid too many requests)
            $urlsToTest = array_slice($potentialUrls, 0, 20); // Test first 20 URLs
            $workingUrls = [];

            foreach ($urlsToTest as $testUrl) {
                try {
                    $response = Http::timeout(5)
                        ->withHeaders([
                            'User-Agent' => $this->copartUserAgent(),
                        ])
                        ->withOptions($this->copartHttpOptions())
                        ->head($testUrl); // Use HEAD to just check if URL exists

                    if ($response->successful() &&
                        str_contains(strtolower($response->header('Content-Type', '')), 'image')) {
                        $workingUrls[] = $testUrl;
                        Log::info('✅ Found working URL: ' . substr($testUrl, -60));

                        // If we found one, stop testing - likely the pattern works
                        if (count($workingUrls) >= 1) {
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore failures, just try next URL
                }

                // Small delay to be nice to the server
                usleep(100000); // 0.1 second
            }

            // Convert working URLs to expected format
            foreach ($workingUrls as $imgUrl) {
                $imagesArray[] = [
                    'fullUrl' => $imgUrl,
                    'highResUrl' => $imgUrl,
                    'thumbnailUrl' => $imgUrl,
                ];
            }

            Log::info('🎯 URL generation found ' . count($imagesArray) . ' working images');

        } catch (\Exception $e) {
            Log::warning('⚠️ URL generation error: ' . $e->getMessage());
        }

        return $imagesArray;
    }
}
