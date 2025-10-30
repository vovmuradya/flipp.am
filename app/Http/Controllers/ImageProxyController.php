<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            abort(400, 'Invalid URL');
        }

        $parts = parse_url($url);
        $host = $parts['host'] ?? '';

        // Разрешённые источники
        $allowedHosts = [
            'cs.copart.com',
            'www.cs.copart.com',
            'content.iaai.net',
            'images.iaai.com',
            'photos.iaai.com',
        ];

        if (!in_array(strtolower($host), $allowedHosts, true)) {
            abort(403, 'Host is not allowed');
        }

        // Выполняем запрос к источнику
        $response = Http::timeout(20)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                // Некоторые хосты требуют реферер
                'Referer' => 'https://www.copart.com/',
            ])
            ->get($url);

        if (!$response->successful()) {
            abort($response->status() ?: 502, 'Upstream error');
        }

        $contentType = $response->header('Content-Type', 'image/jpeg');
        return response($response->body(), 200)
            ->header('Content-Type', $contentType)
            ->header('Cache-Control', 'public, max-age=86400');
    }
}

