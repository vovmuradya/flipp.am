<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CopartImageService
{
    private const IMAGE_CACHE_KEY = 'copart_image_urls';
    private const IMAGE_CACHE_TTL = 86400; // 24 hours

    public function __construct(
        private readonly CopartCookieManager $cookieManager
    ) {}

    /**
     * Главный метод: каскадное получение изображений лота.
     */
    public function fetchLotImages(string $lotId, array $existingData = []): array
    {
        Log::info("Fetching images for lot {$lotId}");

        $cacheKey = "copart_images_{$lotId}";
        if ($cached = Cache::get($cacheKey)) {
            Log::info("Images for lot {$lotId} loaded from cache");
            return $cached;
        }

        $strategies = [
            'existing_data' => fn () => $this->extractFromExistingData($existingData),
            'cdn_pattern' => fn () => $this->fetchViaCdnPattern($lotId),
            'headless' => fn () => $this->fetchViaHeadless($lotId),
            'alternative_api' => fn () => $this->fetchViaAlternativeApi($lotId),
        ];

        foreach ($strategies as $name => $strategy) {
            try {
                $images = $strategy();

                if (!empty($images) && $this->validateImages($images)) {
                    Log::info("Images fetched via {$name} for lot {$lotId}", [
                        'count' => count($images),
                    ]);

                    Cache::put($cacheKey, $images, self::IMAGE_CACHE_TTL);

                    return $images;
                }
            } catch (\Throwable $e) {
                Log::warning("Image strategy {$name} failed for lot {$lotId}: {$e->getMessage()}");
            }
        }

        Log::warning("All image strategies failed for lot {$lotId}");
        return $this->getPlaceholder($lotId);
    }

    private function extractFromExistingData(array $data): array
    {
        $images = [];

        if (!empty($data['images']) && is_array($data['images'])) {
            $images = array_merge($images, $data['images']);
        }

        if (!empty($data['imagesList']) && is_array($data['imagesList'])) {
            foreach ($data['imagesList'] as $img) {
                if (is_string($img)) {
                    $images[] = $img;
                } elseif (is_array($img) && isset($img['url'])) {
                    $images[] = $img['url'];
                }
            }
        }

        if (!empty($data['lotImages']) && is_array($data['lotImages'])) {
            $images = array_merge($images, $data['lotImages']);
        }

        return array_unique(array_filter($images));
    }

    private function fetchViaCdnPattern(string $lotId): array
    {
        $patterns = $this->getCdnPatterns();
        $lotIdPart = substr($lotId, -3);

        foreach ($patterns as $pattern) {
            $images = $this->testCdnPattern($pattern, $lotId, $lotIdPart);
            if (!empty($images)) {
                return $images;
            }
        }

        return [];
    }

    private function getCdnPatterns(): array
    {
        return [
            'https://cs.copart.com/v1/AUTH_svc.pdoc00001/PIX%s/%s/%d.JPG',
            'https://lotpix.copart.com/%s/%d.JPG',
            'https://vis.copart.com/v1/%s/%d.jpg',
            'https://images.copart.com/lot/%s/%d.jpg',
        ];
    }

    private function testCdnPattern(string $pattern, string $lotId, string $lotIdPart): array
    {
        $images = [];
        $maxImages = 30;
        $consecutiveFails = 0;
        $maxConsecutiveFails = 3;

        for ($i = 1; $i <= $maxImages; $i++) {
            if (strpos($pattern, '%s') !== false && substr_count($pattern, '%s') === 2) {
                $url = sprintf($pattern, $lotIdPart, $lotId, $i);
            } else {
                $url = sprintf($pattern, $lotId, $i);
            }

            if ($this->imageExists($url)) {
                $images[] = $url;
                $consecutiveFails = 0;
            } else {
                $consecutiveFails++;
                if ($consecutiveFails >= $maxConsecutiveFails) {
                    break;
                }
            }
        }

        return $images;
    }

    private function imageExists(string $url): bool
    {
        $urlHash = md5($url);
        $cacheKey = self::IMAGE_CACHE_KEY . "_{$urlHash}";
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::timeout(3)
                ->retry(2, 100)
                ->head($url);

            $exists = $response->successful()
                && $response->header('Content-Type')
                && str_contains($response->header('Content-Type'), 'image');

            Cache::put($cacheKey, $exists, 86400);
            return $exists;
        } catch (\Throwable $e) {
            Cache::put($cacheKey, false, 3600);
            return false;
        }
    }

    private function fetchViaHeadless(string $lotId): array
    {
        $scriptPath = base_path('scraper/fetch-copart-lot.cjs');
        if (!is_file($scriptPath)) {
            throw new \Exception("Headless script not found: {$scriptPath}");
        }

        $command = sprintf(
            'node %s %s 2>&1',
            escapeshellarg($scriptPath),
            escapeshellarg($lotId)
        );

        Log::info("Running headless for images: {$command}");
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception("Headless failed: " . implode("\n", $output));
        }

        $result = json_decode(implode('', $output), true);
        if (isset($result['cookies']) && !empty($result['cookies'])) {
            $this->cookieManager->saveCookies($result['cookies']);
            Log::info("Updated cookies from headless for lot {$lotId}");
        }

        $images = $result['data']['images'] ?? [];
        return is_array($images) ? $images : [];
    }

    private function fetchViaAlternativeApi(string $lotId): array
    {
        $endpoints = [
            "https://www.copart.com/public/data/lotdetails/solr/{$lotId}/images",
            "https://www.copart.com/lotImages/{$lotId}",
            "https://www.copart.com/api/lots/{$lotId}/images",
        ];

        $cookieHeader = $this->cookieManager->getCookieHeader();

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => config('services.copart.user_agent'),
                    'Accept' => 'application/json',
                    'Referer' => 'https://www.copart.com/',
                    'Cookie' => $cookieHeader,
                ])
                    ->timeout(10)
                    ->withOptions(['verify' => false])
                    ->get($endpoint);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['images']) && is_array($data['images'])) {
                        return $data['images'];
                    }
                    if (isset($data['data']['images']) && is_array($data['data']['images'])) {
                        return $data['data']['images'];
                    }
                    if (is_array($data) && !empty($data)) {
                        return $data;
                    }
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return [];
    }

    private function validateImages(array $images): bool
    {
        if (empty($images)) {
            return false;
        }

        foreach ($images as $url) {
            if (str_contains($url, 'placeholder')) {
                return false;
            }
        }

        return true;
    }

    private function getPlaceholder(string $lotId): array
    {
        return [
            "https://placehold.co/800x600/cccccc/666666/png?text=Lot+{$lotId}+No+Image",
        ];
    }

    public function clearImageCache(string $lotId): void
    {
        Cache::forget("copart_images_{$lotId}");
        Log::info("Image cache cleared for lot {$lotId}");
    }

    public function clearAllImageCache(): void
    {
        Cache::flush();
        Log::info("All image cache cleared");
    }
}
