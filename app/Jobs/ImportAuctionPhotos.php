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
        $this->afterCommit();
    }

    public function handle(): void
    {
        Log::info('ImportAuctionPhotos: started', ['listing_id' => $this->listingId]);

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
                $downloadUrl = $this->extractUpstreamUrl($url);

                if (!$downloadUrl || !filter_var($downloadUrl, FILTER_VALIDATE_URL)) {
                    Log::warning('ImportAuctionPhotos: invalid URL after extraction', [
                        'original' => substr($url, 0, 100),
                        'resolved' => $downloadUrl ? substr($downloadUrl, 0, 100) : null,
                        'listing_id' => $this->listingId
                    ]);
                    continue;
                }

                Log::info('📸 ImportAuctionPhotos: loading from ' . substr($downloadUrl, 0, 100), [
                    'listing_id' => $listing->id
                ]);

                $listing->addMediaFromUrl($downloadUrl, $this->buildHeaders($url))
                    ->toMediaCollection('auction_photos');

                $successCount++;
                Log::info('✅ ImportAuctionPhotos: added media', [
                    'listing_id' => $listing->id,
                    'url' => substr($downloadUrl, 0, 100),
                ]);
            } catch (\Throwable $e) {
                Log::warning('⚠️ ImportAuctionPhotos: failed', [
                    'listing_id' => $this->listingId,
                    'url' => substr($url, 0, 100),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('ImportAuctionPhotos: finished', [
            'listing_id' => $this->listingId,
            'total' => count($urls),
            'successful' => $successCount
        ]);
    }

    private function extractUpstreamUrl(string $url): ?string
    {
        $trimmed = trim($url);

        if (str_starts_with($trimmed, '/')) {
            $trimmed = rtrim(config('app.url'), '/') . $trimmed;
        }

        if (!str_contains($trimmed, '/proxy/image')) {
            return $trimmed;
        }

        $parts = parse_url($trimmed);
        if (empty($parts['query'])) {
            return $trimmed;
        }

        parse_str($parts['query'], $params);

        $upstream = $params['u'] ?? null;

        return is_string($upstream) ? $upstream : null;
    }

    /**
     * @return array<string,string>
     */
    private function buildHeaders(string $originalUrl): array
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0 Safari/537.36',
            'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
        ];

        $parts = parse_url($originalUrl);
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $params);
            if (!empty($params['r']) && is_string($params['r'])) {
                $headers['Referer'] = $params['r'];
            }
        }

        return $headers;
    }
}
