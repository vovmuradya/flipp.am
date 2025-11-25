<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // Добавьте Log для логирования ошибок

class ImageProxyController extends Controller
{
    /**
     * Проксирует изображения с разрешённых доменов (например, Copart),
     * добавляя необходимые заголовки и позволяя отображать их на сайте.
     *
     * GET /image-proxy?url={encoded}
     */
    public function show(Request $request)
    {
        $url = $request->query('url');
        if (!$url) {
            abort(400, 'Invalid URL');
        }

        // Если параметр был закодирован в route(), он может быть закодирован — декодируем.
        $url = urldecode($url);

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            abort(400, 'Invalid URL');
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');

        // Разрешённые источники (поддерживаем совпадение по хосту и по подстроке)
        $allowedHosts = [
            'cs.copart.com',
            'www.cs.copart.com',
            'content.iaai.net',
            'images.iaai.com',
            'photos.iaai.com',
            'copart.com',
            'iaai.com',
            'via.placeholder.com',
        ];

        $allowed = false;
        foreach ($allowedHosts as $ah) {
            if ($ah === $host || str_ends_with($host, '.' . $ah) || str_contains($host, $ah)) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            abort(403, 'Host is not allowed');
        }

        try {
            $configuredReferer = config('services.copart.referer') ?? 'https://www.copart.com/';
        $configuredOrigin = config('services.copart.origin') ?? 'https://www.copart.com';
        $trimTop = (int) $request->query('trim_top', 0);
            $configuredUserAgent = config('services.copart.user_agent') ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36';

            // ✅ ИСПРАВЛЕНИЕ: Динамически используем Referer из запроса, если он есть
            $referer = $request->query('r', $configuredReferer);

            // 🔥 УЛУЧШЕННЫЕ ЗАГОЛОВКИ для обхода защиты Copart
            $response = Http::timeout(25)
                ->withHeaders([
                    'User-Agent' => $configuredUserAgent,
                    'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Referer' => $referer, // Используем динамический Referer
                    'Origin' => $configuredOrigin,
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
                ->get($url);

            Log::info('🖼️ ImageProxy attempt', [
                'url' => substr($url, 0, 100),
                'status' => $response->status(),
                'content_type' => $response->header('Content-Type'),
            ]);

            if (!$response->successful()) {
                Log::warning('ImageProxy: upstream not successful', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body_preview' => substr($response->body(), 0, 200),
                ]);

                // Попробуем получить плейсхолдер и вернуть его
                $placeholder = Http::timeout(10)->get('https://via.placeholder.com/800x600/e5e7eb/6b7280?text=No+Image+Available');
                return response($placeholder->body(), 200)
                    ->header('Content-Type', 'image/png')
                    ->header('Cache-Control', 'public, max-age=3600');
            }

            $contentType = $response->header('Content-Type', 'image/jpeg');

            // Проверяем, что это действительно изображение
            if (!str_contains($contentType, 'image')) {
                Log::warning('ImageProxy: not an image response', [
                    'url' => $url,
                    'content_type' => $contentType,
                ]);

                $placeholder = Http::timeout(10)->get('https://via.placeholder.com/800x600/e5e7eb/6b7280?text=Invalid+Image');
                return response($placeholder->body(), 200)
                    ->header('Content-Type', 'image/png')
                    ->header('Cache-Control', 'public, max-age=3600');
            }

            $body = $response->body();

            if ($trimTop > 0 && function_exists('imagecreatefromstring')) {
                $img = @imagecreatefromstring($body);
                if ($img !== false) {
                    $width = imagesx($img);
                    $height = imagesy($img);
                    $trim = min($trimTop, $height - 1);
                    $cropped = imagecreatetruecolor($width, $height - $trim);
                    imagecopy($cropped, $img, 0, 0, 0, $trim, $width, $height - $trim);
                    ob_start();
                    imagepng($cropped);
                    $body = ob_get_clean();
                    imagedestroy($img);
                    imagedestroy($cropped);
                    $contentType = 'image/png';
                }
            }

            return response($body, 200)
                ->header('Content-Type', $contentType)
                ->header('Cache-Control', 'public, max-age=86400')
                ->header('Access-Control-Allow-Origin', '*');

        } catch (\Exception $e) {
            Log::error('ImageProxy Failed Upstream Request', [
                'requested_url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Возвращаем плейсхолдер при любых ошибках
            try {
                $placeholder = Http::timeout(10)->get('https://via.placeholder.com/800x600/e5e7eb/6b7280?text=Error+Loading+Image');
                return response($placeholder->body(), 200)
                    ->header('Content-Type', 'image/png')
                    ->header('Cache-Control', 'public, max-age=3600');
            } catch (\Exception $e2) {
                // В крайнем случае — пустое изображение 1x1 transparent PNG
                $transparentPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
                return response($transparentPng, 200)
                    ->header('Content-Type', 'image/png')
                    ->header('Cache-Control', 'public, max-age=3600');
            }
        }
    }
}
