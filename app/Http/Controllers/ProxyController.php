<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class ProxyController extends Controller
{
    /**
     * Прокси для изображений с аукционов (ограниченный список хостов).
     * Пример: /proxy/image?u=https%3A%2F%2Fcs.copart.com%2Fpath%2Fimg.jpg&r=https%3A%2F%2Fwww.copart.com%2Flot%2F123456
     */
    public function image(Request $request)
    {
        $url = $request->query('u');
        if (!$url) {
            abort(400, 'Missing url');
        }

        // Если url закодирован (как /proxy/image?u=https%3A%2F%2F...), декодируем
        $url = urldecode($url);

        // Нормализуем: если протокол отсутствует, добавим https://
        if (!preg_match('#^https?://#i', $url)) {
            if (strpos($url, '//') === 0) {
                $url = 'https:' . $url;
            } else {
                $url = 'https://' . ltrim($url, '/');
            }
        }

        // Безопасность: разрешаем только белые хосты
        $allowedHosts = [
            'cs.copart.com',
            'pics.copart.com',
            'images.copart.com',
            'content.iaai.com',
            'content.iaai.net',
            'images.iaai.com',
            'photos.iaai.com',
            'placehold.co',
            'via.placeholder.com',
            'placeholder.com',
            'dummyimage.com',
        ];

        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        // Гибкая проверка: если host содержит copart.com или iaai, тоже разрешаем
        $allowed = false;
        if ($host && (str_contains($host, 'copart.com') || str_contains($host, 'iaai.com') || str_contains($host, 'iaai.net') || in_array($host, $allowedHosts, true))) {
            $allowed = true;
        }

        if (!$allowed) {
            Log::warning('🚫 Proxy: Host not allowed', ['host' => $host, 'url' => substr($url, 0, 200)]);
            return $this->placeholderResponse('Host+not+allowed');
        }

        // Извлекаем lot ID для построения альтернативных URL (может пригодиться позже)
        $lotId = null;
        if (preg_match('#/lpp/(\d+)/#i', $url, $m)) {
            $lotId = $m[1];
        } elseif (preg_match('#/ids-c-prod-lpp/\d+/[^/]+_(\d+)#i', $url, $m)) {
            $lotId = $m[1];
        }

        // Принимаем опциональный реферер (страница лота)
        $configuredReferer = config('services.copart.referer') ?? 'https://www.copart.com/';
        $configuredOrigin = config('services.copart.origin') ?? 'https://www.copart.com';
        $configuredUserAgent = config('services.copart.user_agent') ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36';

        $referer = $request->query('r')
            ? urldecode($request->query('r'))
            : (str_contains($host, 'iaai') ? 'https://www.iaai.com/' : $configuredReferer);

        $origin = str_contains($host, 'iaai') ? 'https://www.iaai.com' : $configuredOrigin;
        $copartCookieHeader = str_contains($host, 'copart.com') ? $this->getCopartCookieHeader() : null;
        $iaaiCookies = str_contains($host, 'iaai') ? $this->getIaaiCookies() : [];

        try {
            // Функция для выполнения запроса с общими заголовками
            $doRequest = function(string $targetUrl) use ($referer, $copartCookieHeader, $iaaiCookies, $origin) {
                $headers = [
                    'User-Agent' => $configuredUserAgent,
                    'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Referer' => $referer,
                    'Origin' => $origin,
                        'DNT' => '1',
                        'Connection' => 'keep-alive',
                        'Sec-Fetch-Dest' => 'image',
                        'Sec-Fetch-Mode' => 'no-cors',
                        'Sec-Fetch-Site' => 'same-site',
                        'sec-ch-ua' => '"Chromium";v="131", "Not_A Brand";v="24", "Google Chrome";v="131"',
                        'sec-ch-ua-mobile' => '?0',
                    'sec-ch-ua-platform' => '"Windows"',
                    'Cache-Control' => 'no-cache',
                    'Pragma' => 'no-cache',
                ];

                if ($copartCookieHeader) {
                    $headers['Cookie'] = $copartCookieHeader;
                }
                if (!empty($iaaiCookies)) {
                    $headers['Cookie'] = implode('; ', array_map(
                        fn ($name, $value) => $name . '=' . $value,
                        array_keys($iaaiCookies),
                        $iaaiCookies
                    ));
                }

                $options = [
                    'allow_redirects' => ['max' => 5],
                    'verify' => false,
                    'http_errors' => false,
                ];
                $options = $this->appendCopartCurlResolve($options);

                $http = Http::timeout(25)
                    ->withHeaders($headers)
                    ->withOptions($options);

                if (!empty($iaaiCookies)) {
                    $http = $http->withCookies($iaaiCookies, '.iaai.com');
                }

                return $http->get($targetUrl);
            };

            // Генерируем массив альтернативных URL для попыток (для Copart)
            $urlsToTry = [$url];

            if (str_contains($host, 'copart.com')) {
                // Варианты замены суффиксов с расширением
                $withExtVariants = [];
                $withExtVariants[] = preg_replace('#_thn\.(jpg|jpeg|png|webp)$#i', '_ful.$1', $url);
                $withExtVariants[] = preg_replace('#_hrs\.(jpg|jpeg|png|webp)$#i', '_ful.$1', $url);
                $withExtVariants[] = preg_replace('#_thb\.(jpg|jpeg|png|webp)$#i', '_ful.$1', $url);
                $withExtVariants[] = preg_replace('#_tmb\.(jpg|jpeg|png|webp)$#i', '_ful.$1', $url);

                // Если URL оканчивается на суффикс без расширения — пробуем добавить популярные расширения
                if (preg_match('#_(ful|thb|hrs|tmb)$#i', $url)) {
                    $withExtVariants[] = $url . '.jpg';
                    $withExtVariants[] = $url . '.jpeg';
                    $withExtVariants[] = $url . '.png';
                }

                $urlsToTry = array_values(array_unique(array_merge($urlsToTry, array_filter($withExtVariants))));
            }

            $response = null;
            $successUrl = null;

            // Пробуем все варианты
            foreach ($urlsToTry as $tryUrl) {
                $response = $doRequest($tryUrl);

                $ct = $response->header('Content-Type', '');
                $size = strlen($response->body());

                Log::info('🖼️ ProxyController attempt', [
                    'url' => substr($tryUrl, 0, 200),
                    'status' => $response->status(),
                    'content_type' => $ct,
                    'size' => $size,
                ]);

                // Успех: код 2xx + тип контента image + разумный размер (> 100 байт)
                if ($response->successful() && str_contains(strtolower($ct), 'image') && $size > 100) {
                    $successUrl = $tryUrl;
                    Log::info('✅ ProxyController: Image loaded successfully', ['url' => substr($tryUrl, 0, 200)]);
                    break;
                }
            }

            // Если ничего не вышло — возвращаем понятный плейсхолдер (200), а не 404-пустышку
            if (!$response || !$response->successful() || !str_contains(strtolower($response->header('Content-Type', '')), 'image') || strlen($response->body()) <= 100) {
                Log::warning('⚠️ ProxyController: all HTTP attempts failed, trying curl fallback', [
                    'original_url' => substr($url, 0, 200),
                    'attempts' => count($urlsToTry),
                ]);

                foreach ($urlsToTry as $tryUrl) {
                    $curlResult = $this->fetchImageViaCurl($tryUrl, $copartCookieHeader, $referer, $iaaiCookies);
                    if ($curlResult) {
                        Log::info('✅ ProxyController: curl fallback succeeded', ['url' => substr($tryUrl, 0, 200)]);

                        return response($curlResult['body'], 200)
                            ->header('Content-Type', $curlResult['content_type'] ?? 'image/jpeg')
                            ->header('Cache-Control', 'public, max-age=86400')
                            ->header('Access-Control-Allow-Origin', '*');
                    }
                }

                Log::warning('⚠️ ProxyController: fallback failed, returning placeholder', [
                    'original_url' => substr($url, 0, 200),
                ]);
                return $this->placeholderResponse();
            }

            $contentType = $response->header('Content-Type', 'image/jpeg');
            $etag = $response->header('ETag');
            $lastModified = $response->header('Last-Modified');

            $res = response($response->body(), 200)
                ->header('Content-Type', $contentType)
                ->header('Cache-Control', 'public, max-age=86400')
                ->header('Access-Control-Allow-Origin', '*');

            if (!empty($etag)) {
                $res->header('ETag', $etag);
            }
            if (!empty($lastModified)) {
                $res->header('Last-Modified', $lastModified);
            }

            return $res;
        } catch (\Throwable $e) {
            Log::error('❌ Proxy image exception: ' . $e->getMessage(), ['url' => substr($url, 0, 200), 'trace' => substr($e->getTraceAsString(), 0, 500)]);
            return $this->placeholderResponse('Exception');
        }
    }

    /**
     * Возвращаем читаемый плейсхолдер (200 OK), чтобы на странице было видно «нет фото», а не синий блок.
     */
    private function placeholderResponse(string $text = 'No+Image+Available')
    {
        try {
            $placeholder = Http::timeout(10)
                ->withOptions(['http_errors' => false])
                ->get('https://via.placeholder.com/800x600/e5e7eb/6b7280?text=' . $text);

            $body = $placeholder->successful() ? $placeholder->body() : base64_decode('iVBORw0KGgoAAAANSUhEUgAAAwAAAAIACAYAAABvQm0fAAAACXBIWXMAAAsSAAALEgHS3X78AAAAGXRFWHRTb2Z0d2FyZQBwYWludC5uZXQgNC4wLjJCJ3e2AAAB+klEQVR4nO3SMQEAIAwAsHf/0x1C2K7L4K9m7gIVpGm9GgAAAAAAAAAAAAAAAAAAgE8E5v0BAAAAAAAAAAAAAAAAAPC2wJgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADgH3u0AQAAB0w4g+QAAAAASUVORK5CYII=');

            return response($body, 200)
                ->header('Content-Type', 'image/png')
                ->header('Cache-Control', 'public, max-age=3600');
        } catch (\Throwable $e) {
            // В крайнем случае — пустой 1x1 PNG
            $transparentPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
            return response($transparentPng, 200)
                ->header('Content-Type', 'image/png')
                ->header('Cache-Control', 'public, max-age=3600');
        }
    }

    /**
     * Добавляет в опции cURL-принудительный resolve, если он задан в .env.
     *
     * @param array<string,mixed> $options
     * @return array<string,mixed>
     */
    private function appendCopartCurlResolve(array $options): array
    {
        $curlOptions = $options['curl'] ?? [];

        $resolveRaw = config('services.copart.resolve');
        if (is_string($resolveRaw) && trim($resolveRaw) !== '') {
            $entries = array_filter(array_map('trim', explode(',', $resolveRaw)));
            if (!empty($entries)) {
                $curlOptions[\CURLOPT_RESOLVE] = array_values($entries);
            }
        }

        if (!empty($curlOptions)) {
            $options['curl'] = $curlOptions;
        }

        return $options;
    }

    private function fetchImageViaCurl(string $targetUrl, ?string $cookieHeader, string $referer, array $iaaiCookies = []): ?array
    {
        $headerFile = tempnam(sys_get_temp_dir(), 'copart-hdr-');
        if ($headerFile === false) {
            return null;
        }

        $host = strtolower(parse_url($targetUrl, PHP_URL_HOST) ?? '');
        $isIaai = str_contains($host, 'iaai');

        $command = [
            'curl',
            '--silent',
            '--show-error',
            '--max-time',
            '25',
            '--location',
            '-D',
            $headerFile,
            '-o',
            '-',
        ];

        $resolveRaw = config('services.copart.resolve');
        if (is_string($resolveRaw) && trim($resolveRaw) !== '') {
            foreach (array_filter(array_map('trim', explode(',', $resolveRaw))) as $resolve) {
                $command[] = '--resolve';
                $command[] = $resolve;
            }
        }

        $configuredOrigin = config('services.copart.origin') ?? 'https://www.copart.com';
        $configuredUserAgent = config('services.copart.user_agent') ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36';
        $origin = $isIaai ? 'https://www.iaai.com' : $configuredOrigin;

        $headerList = [
            'User-Agent: ' . $configuredUserAgent,
            'Accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            'Referer: ' . $referer,
            'Origin: ' . $origin,
            'DNT: 1',
            'Connection: keep-alive',
            'Sec-Fetch-Dest: image',
            'Sec-Fetch-Mode: no-cors',
            'Sec-Fetch-Site: same-site',
            'sec-ch-ua: "Chromium";v="131", "Not_A Brand";v="24", "Google Chrome";v="131"',
            'sec-ch-ua-mobile: ?0',
            'sec-ch-ua-platform: "Windows"',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
        ];

        if (!empty($iaaiCookies)) {
            $cookieHeader = implode('; ', array_map(
                fn ($name, $value) => $name . '=' . $value,
                array_keys($iaaiCookies),
                $iaaiCookies
            ));
        }

        if ($cookieHeader) {
            $headerList[] = 'Cookie: ' . $cookieHeader;
        }

        foreach ($headerList as $headerLine) {
            $command[] = '-H';
            $command[] = $headerLine;
        }

        $command[] = $targetUrl;

        $process = new Process($command);
        $process->setTimeout(35);
        $process->run();

        $headersRaw = @file_get_contents($headerFile) ?: '';
        @unlink($headerFile);

        if (!$process->isSuccessful()) {
            Log::warning('⚠️ Curl image fallback failed', [
                'url' => substr($targetUrl, 0, 200),
                'error' => trim($process->getErrorOutput()) ?: trim($process->getOutput()),
            ]);
            return null;
        }

        $body = $process->getOutput();
        if ($body === '') {
            return null;
        }

        $headerBlocks = preg_split("/\r?\n\r?\n/", trim($headersRaw));
        $lastHeaderBlock = $headerBlocks ? trim((string) end($headerBlocks)) : trim($headersRaw);
        $headerLines = preg_split("/\r?\n/", $lastHeaderBlock);

        $status = null;
        $contentType = null;

        foreach ($headerLines as $index => $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if ($index === 0 && preg_match('/^HTTP\/\d(?:\.\d)?\s+(\d+)/', $line, $m)) {
                $status = (int) $m[1];
            }

            if (stripos($line, 'content-type:') === 0) {
                $contentType = trim(substr($line, strlen('content-type:')));
            }
        }

        if ($status === null || $status < 200 || $status >= 300) {
            Log::warning('⚠️ Curl image fallback status not OK', [
                'status' => $status,
                'url' => substr($targetUrl, 0, 200),
            ]);
            return null;
        }

        if ($contentType === null || stripos($contentType, 'image') === false) {
            Log::warning('⚠️ Curl image fallback non-image content', [
                'content_type' => $contentType,
                'url' => substr($targetUrl, 0, 200),
            ]);
            return null;
        }

        if (strlen($body) <= 100) {
            Log::warning('⚠️ Curl image fallback body too small', [
                'length' => strlen($body),
                'url' => substr($targetUrl, 0, 200),
            ]);
            return null;
        }

        return [
            'body' => $body,
            'content_type' => $contentType,
        ];
    }

    private function getIaaiCookies(): array
    {
        $cookieString = config('services.iaai.cookies');
        if (!is_string($cookieString) || trim($cookieString) === '') {
            return [];
        }

        $cookies = [];
        foreach (array_filter(array_map('trim', explode(';', $cookieString))) as $pair) {
            $separator = strpos($pair, '=');
            if ($separator === false) {
                continue;
            }

            $name = trim(substr($pair, 0, $separator));
            $value = trim(substr($pair, $separator + 1));
            if ($name === '') {
                continue;
            }
            $cookies[$name] = $value;
        }

        return $cookies;
    }

    private function getCopartCookieHeader(): ?string
    {
        $cookieString = config('services.copart.cookies');
        $cookieString = is_string($cookieString) ? trim($cookieString) : '';

        return $cookieString !== '' ? $cookieString : null;
    }
}
