<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProxyController extends Controller
{
    /**
     * Прокси для изображений с аукционов (ограниченный список хостов).
     * Пример: /proxy/image?u=https%3A%2F%2Fcs.copart.com%2Fv1%2FAUTH_svc.pdoc00001%2F...
     */
    public function image(Request $request)
    {
        $url = $request->query('u');
        if (!$url) {
            abort(400, 'Missing url');
        }

        // Безопасность: разрешаем только белые хосты
        $allowedHosts = [
            'cs.copart.com',
            'pics.copart.com',
            'images.copart.com',
            'placehold.co', // разрешим плейсхолдеры для отладки
        ];

        $host = parse_url($url, PHP_URL_HOST);
        if (!$host || !in_array(strtolower($host), $allowedHosts, true)) {
            abort(403, 'Host not allowed');
        }

        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                    'Referer' => 'https://www.copart.com/',
                ])
                ->get($url);

            if (!$response->successful()) {
                Log::warning('Proxy image fetch failed', ['status' => $response->status(), 'url' => $url]);
                abort(502, 'Upstream error');
            }

            $contentType = $response->header('Content-Type', 'image/jpeg');
            $etag = $response->header('ETag');
            $lastModified = $response->header('Last-Modified');

            return response($response->body(), 200)
                ->header('Content-Type', $contentType)
                ->header('Cache-Control', 'public, max-age=86400')
                ->when($etag, fn($r) => $r->header('ETag', $etag))
                ->when($lastModified, fn($r) => $r->header('Last-Modified', $lastModified));
        } catch (\Throwable $e) {
            Log::error('Proxy image exception: '.$e->getMessage(), ['url' => $url]);
            abort(500, 'Proxy error');
        }
    }
}

