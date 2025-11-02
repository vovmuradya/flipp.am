<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            'placehold.co',
            'via.placeholder.com',
        ];

        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

        // Гибкая проверка: если host содержит copart.com или iaai, тоже разрешаем
        $allowed = false;
        if ($host && (str_contains($host, 'copart.com') || str_contains($host, 'iaai.com') || in_array($host, $allowedHosts, true))) {
            $allowed = true;
        }

        if (!$allowed) {
            Log::warning('🚫 Proxy: Host not allowed', ['host' => $host, 'url' => substr($url, 0, 200)]);
            abort(403, 'Host not allowed');
        }

        // Извлекаем lot ID для построения альтернативных URL
        $lotId = null;
        if (preg_match('#/lpp/(\d+)/#i', $url, $m)) {
            $lotId = $m[1];
        } elseif (preg_match('#/ids-c-prod-lpp/\d+/[^/]+_(\d+)#i', $url, $m)) {
            $lotId = $m[1];
        }

        // Принимаем опциональный реферер (страница лота)
        $referer = $request->query('r') ? urldecode($request->query('r')) : 'https://www.copart.com/';

        try {
            // Функция для выполнения запроса с общими заголовками
            $doRequest = function(string $targetUrl) use ($referer) {
                return Http::timeout(25)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
                        'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                        'Accept-Language' => 'en-US,en;q=0.9',
                        'Referer' => $referer,
                        'Origin' => 'https://www.copart.com',
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
                    ])
                    ->withOptions([
                        'allow_redirects' => ['max' => 5],
                        'verify' => false,
                        'http_errors' => false,
                    ])
                    ->get($targetUrl);
            };

            // Генерируем массив альтернативных URL для попыток (для Copart)
            // Основной URL уже содержит правильный путь от API
            $urlsToTry = [$url];

            // Добавляем только варианты с заменой суффиксов (не генерируем несуществующие пути)
            $additionalVariants = [];
            if (str_contains($host, 'copart.com')) {
                // Пробуем разные суффиксы качества изображения
                $additionalVariants[] = preg_replace('#_thn\.(jpg|jpeg|png|webp)$#i', '_ful.$1', $url);
                $additionalVariants[] = preg_replace('#_hrs\.(jpg|jpeg|png|webp)$#i', '_ful.$1', $url);
                $additionalVariants[] = preg_replace('#_thb\.(jpg|jpeg|png|webp)$#i', '_ful.$1', $url);
                $additionalVariants[] = preg_replace('#_tmb\.(jpg|jpeg|png|webp)$#i', '_ful.$1', $url);
            }
            $urlsToTry = array_values(array_unique(array_merge($urlsToTry, array_filter($additionalVariants))));

            $response = null;
            $successUrl = null;

            // Пробуем все варианты
            foreach ($urlsToTry as $tryUrl) {
                $response = $doRequest($tryUrl);

                Log::info('🖼️ ProxyController attempt', [
                    'url' => substr($tryUrl, 0, 200),
                    'status' => $response->status(),
                    'content_type' => $response->header('Content-Type'),
                    'size' => strlen($response->body()),
                ]);

                if ($response->successful() && str_contains($response->header('Content-Type', ''), 'image')) {
                    $successUrl = $tryUrl;
                    Log::info('✅ ProxyController: Image loaded successfully', ['url' => substr($tryUrl, 0, 200)]);
                    break;
                }
            }

            if (!$response || !$response->successful() || !str_contains($response->header('Content-Type', ''), 'image')) {
                Log::error('❌ All attempts failed, returning placeholder', [
                    'original_url' => substr($url, 0, 200),
                    'attempts' => count($urlsToTry),
                ]);
                return $this->returnTransparentPlaceholder();
            }

            $contentType = $response->header('Content-Type', 'image/jpeg');
            $etag = $response->header('ETag');
            $lastModified = $response->header('Last-Modified');

            // Создаём Response и добавляем заголовки
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
            return $this->returnTransparentPlaceholder();
        }
    }

    /**
     * Возвращаем прозрачный 1x1 PNG при ошибке загрузки
     */
    private function returnTransparentPlaceholder()
    {
        // Base64 прозрачного 1x1 PNG (43 байта)
        $transparentPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+P+/HgAFhAJ/wlseKgAAAABJRU5ErkJggg==');

        return response($transparentPng, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
