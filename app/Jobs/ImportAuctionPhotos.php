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

        // Ограничим до 8 фото, чтобы не забивать очередь
        $urls = array_slice($this->photoUrls, 0, 8);
        foreach ($urls as $url) {
            try {
                // Если это наш прокси, превратим относительный путь в абсолютный
                if (str_starts_with($url, '/image-proxy')) {
                    $url = rtrim(config('app.url'), '/') . $url;
                }
                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    continue;
                }

                $listing->addMediaFromUrl($url)
                    ->toMediaCollection('images');

                Log::info('ImportAuctionPhotos: added media', [
                    'listing_id' => $listing->id,
                    'url' => $url,
                ]);
            } catch (\Throwable $e) {
                Log::warning('ImportAuctionPhotos: failed addMediaFromUrl', [
                    'listing_id' => $this->listingId,
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);
                // продолжаем следующие фото
            }
        }
    }
}

