<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\Process\Process;

class AuctionParserService
{
    public function parseFromUrl(string $url, bool $aggressive = true): ?array
    {
        $url = trim($url);
        $url = preg_replace('/\s+/', '', $url);

        $domain = parse_url($url, PHP_URL_HOST);

        if (str_contains($domain, 'copart.com')) {
            $cacheKey = 'auction_parser:' . md5(($aggressive ? '1' : '0') . '|' . $url);

            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $result = $this->parseCopart($url, $aggressive);

            if ($result !== null) {
                Cache::put($cacheKey, $result, now()->addMinutes(10));
            }

            return $result;
        }

        if (str_contains($domain, 'iaai-auctions.com') || str_contains($domain, 'iaai.com')) {
            return $this->parseIAAI($url);
        }

        return null;
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
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $cache = [];
        $cookieString = $this->getCopartCookieString();

        if ($cookieString === '') {
            return $cache;
        }

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

            $cache[$name] = $value;
        }

        return $cache;
    }

    private function getCopartCookieString(): string
    {
        $cookieString = config('services.copart.cookies') ?? env('COPART_COOKIES');

        return is_string($cookieString) ? trim($cookieString) : '';
    }

    private function getCopartCookieHeader(): ?string
    {
        $cookieString = $this->getCopartCookieString();

        return $cookieString !== '' ? $cookieString : null;
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
     * @param array<string,string> $headers
     */
    private function requestCopartJson(string $url, array $headers, ?CookieJar $cookieJar = null): ?array
    {
        $options = $this->copartHttpOptions($cookieJar);
        $options['http_errors'] = false;

        try {
            $response = Http::timeout(20)
                ->withHeaders($headers)
                ->withOptions($options)
                ->get($url);

            if ($response->successful()) {
                $payload = $response->json();
                if (is_array($payload)) {
                    return $payload;
                }
                Log::warning('⚠️ Copart JSON parse failed (HTTP)', [
                    'url' => $url,
                    'body_snippet' => substr($response->body(), 0, 200),
                ]);
            } else {
                Log::warning('⚠️ Copart HTTP status', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body_snippet' => substr($response->body(), 0, 200),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('⚠️ Copart HTTP error: ' . $e->getMessage(), ['url' => $url]);
        }

        return $this->requestCopartJsonViaCurl($url, $headers);
    }

    /**
     * @param array<string,string> $headers
     */
    private function requestCopartJsonViaCurl(string $url, array $headers): ?array
    {
        $command = $this->buildCurlCommand($url, $headers);
        $process = new Process($command);
        $process->setTimeout(25);
        $process->run();

        if (!$process->isSuccessful()) {
            Log::warning('⚠️ Copart curl JSON failed', [
                'url' => $url,
                'error' => trim($process->getErrorOutput()) ?: trim($process->getOutput()),
            ]);
            return null;
        }

        $body = trim($process->getOutput());
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
            $response = Http::timeout(20)
                ->withHeaders($headers)
                ->withOptions($options)
                ->get($url);

            if ($response->successful()) {
                return $response->body();
            }

            Log::warning('⚠️ Copart HTTP body status', [
                'url' => $url,
                'status' => $response->status(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('⚠️ Copart HTTP body error: ' . $e->getMessage(), ['url' => $url]);
        }

        return $this->requestCopartBodyViaCurl($url, $headers);
    }

    /**
     * @param array<string,string> $headers
     */
    private function requestCopartBodyViaCurl(string $url, array $headers): ?string
    {
        $command = $this->buildCurlCommand($url, $headers);
        $process = new Process($command);
        $process->setTimeout(25);
        $process->run();

        if (!$process->isSuccessful()) {
            Log::warning('⚠️ Copart curl body failed', [
                'url' => $url,
                'error' => trim($process->getErrorOutput()) ?: trim($process->getOutput()),
            ]);
            return null;
        }

        return $process->getOutput();
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
            '20',
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
        try {
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

            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'application/json, text/plain, */*',
                'Referer' => $url,
                'Accept-Language' => 'en-US,en;q=0.9',
                'Cache-Control' => 'no-cache',
                'Pragma' => 'no-cache',
                'Origin' => 'https://www.copart.com',
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

            $auctionEndAt = null;

            if (isset($apiData['data']['lotDetails'])) {
                $details = $apiData['data']['lotDetails'];
                $make = $details['mkn'] ?? null;
                $model = $details['lm'] ?? null;
                $year = $details['lcy'] ?? null;
                $mileage = isset($details['od']) ? (int)$details['od'] : null;
                $color = $details['clr'] ?? null;
                $engineStr = $details['egn'] ?? null;

                $auctionEndAt = $this->detectCopartAuctionEnd($details);

                Log::info('✅ Got vehicle data: make=' . $make . ', model=' . $model . ', year=' . $year);
            } elseif ($apiData === null) {
                Log::warning('⚠️ Vehicle API returned no data after retries');
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
                $imagesArray = $this->scrapeImagesFromHtml($url, $lotId, $cookieJar);
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
                    $path = parse_url($imgUrl, PHP_URL_PATH) ?? '';
                    $normalized = preg_replace('/_(thn|hrs|thb|tmb|ful)\.(jpg|jpeg|png|webp)$/i', '.$2', $path);

                    if (isset($seenPaths[$normalized])) {
                        continue;
                    }
                    $seenPaths[$normalized] = true;

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

    private function parseIAAI(string $url): ?array
    {
        return null;
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

    private function scrapeImagesFromHtml(string $url, string $lotId, ?CookieJar $cookieJar = null): array
    {
        $imagesArray = [];

        if (!$cookieJar) {
            $cookieJar = CookieJar::fromArray([], '.copart.com');
        }

        try {
            Log::info('🔄 Attempting HTML scraping for lot: ' . $lotId);

            // Fetch the HTML content of the auction page
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate',
                'DNT' => '1',
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
            ];
            if ($cookieHeader = $this->getCopartCookieHeader()) {
                $headers['Cookie'] = $cookieHeader;
            }

            $html = $this->requestCopartBody($url, $headers, $cookieJar);
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
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
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
