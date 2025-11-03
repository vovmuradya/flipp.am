<?php

namespace App\Jobs;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportAuctionPhotos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int */
    public $timeout = 60; // сек на всю задачу

    /** @var int */
    public $tries = 2;

    /** @var int */
    protected int $listingId;

    /** @var array<string> */
    protected array $photoUrls;

    /**
     * @param int $listingId
     * @param array<string> $photoUrls
     */
    public function __construct(int $listingId, array $photoUrls)
    {
        $this->listingId = $listingId;
        $this->photoUrls = array_values(array_filter($photoUrls));
        $this->onQueue('media');
    }

    public function handle(): void
    {
        $listing = Listing::find($this->listingId);
        if (!$listing) {
            Log::warning('ImportAuctionPhotos: listing not found', ['listing_id' => $this->listingId]);
            return;
        }

        // Ограничим до 14 фото
        $urls = array_slice($this->photoUrls, 0, 14);
        $successCount = 0;

        foreach ($urls as $url) {
            try {
                $realUrl = $url;

                // 1️⃣ Если это proxy-URL, извлекаем реальный URL из параметра u
                if (str_contains($url, '/proxy/image') || str_contains($url, '/image-proxy')) {
                    $parsed = parse_url($url);
                    if (!empty($parsed['query'])) {
                        parse_str($parsed['query'], $params);
                        if (!empty($params['u'])) {
                            $realUrl = urldecode($params['u']);
                        }
                    }
                }

                // 2️⃣ Если URL относительный, делаем его абсолютным
                if (str_starts_with($realUrl, '/')) {
                    $realUrl = rtrim(config('app.url'), '/') . $realUrl;
                }

                // 3️⃣ Проверяем валидность URL
                if (!filter_var($realUrl, FILTER_VALIDATE_URL)) {
                    Log::warning('ImportAuctionPhotos: invalid URL after extraction', [
                        'original' => substr($url, 0, 100),
                        'extracted' => substr($realUrl, 0, 100),
                        'listing_id' => $this->listingId
                    ]);
                    continue;
                }

                Log::info('📸 ImportAuctionPhotos: loading from ' . substr($realUrl, 0, 100), [
                    'listing_id' => $listing->id
                ]);

                // 4️⃣ Загружаем НАПРЯМУЮ с реального URL в коллекцию 'auction_photos'
                $listing->addMediaFromUrl($realUrl)
                    ->toMediaCollection('auction_photos');

                $successCount++;
                Log::info('✅ ImportAuctionPhotos: added media', [
                    'listing_id' => $listing->id,
                    'url' => substr($realUrl, 0, 100),
                ]);
            } catch (\Throwable $e) {
                Log::warning('⚠️ ImportAuctionPhotos: failed', [
                    'listing_id' => $this->listingId,
                    'url' => substr($url, 0, 100),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('✅ ImportAuctionPhotos: job completed', [
            'listing_id' => $this->listingId,
            'total' => count($urls),
            'successful' => $successCount
        ]);
    }
}

