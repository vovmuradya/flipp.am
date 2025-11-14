<?php

namespace App\Console\Commands;

use App\Models\VehicleDetail;
use App\Services\AuctionParserService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshCopartCurrentBids extends Command
{
    protected $signature = 'copart:refresh-current-bids {--limit=100 : Max listings to refresh in one run}';

    protected $description = 'Обновить значения текущих ставок Copart для аукционных объявлений.';

    public function handle(AuctionParserService $parser): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $processed = 0;
        $errors = 0;

        $this->info(sprintf('Refreshing current bids for up to %d auction listings…', $limit));

        VehicleDetail::query()
            ->where('is_from_auction', true)
            ->whereNotNull('source_auction_url')
            ->orderByDesc('current_bid_fetched_at')
            ->chunkById(25, function ($details) use (&$processed, $limit, $parser, &$errors) {
                foreach ($details as $detail) {
                    if ($processed >= $limit) {
                        return false;
                    }

                    $url = $detail->source_auction_url;
                    if (! is_string($url) || trim($url) === '') {
                        continue;
                    }

                    try {
                        $parser->clearCacheForUrl($url);
                        $parsed = $parser->parseFromUrl($url, aggressive: false);

                        if (!is_array($parsed)) {
                            Log::warning('Failed to refresh current bid (parse null)', ['listing_id' => $detail->listing_id, 'url' => $url]);
                            $errors++;
                            continue;
                        }

                        $bidPrice = $parsed['current_bid_price'] ?? null;
                        $bidCurrency = $parsed['current_bid_currency'] ?? null;

                        if ($bidPrice !== null && (!is_numeric($bidPrice) || $bidPrice <= 0)) {
                            $bidPrice = null;
                        }

                        if ($bidCurrency !== null && (!is_string($bidCurrency) || !preg_match('/^[A-Z]{3,5}$/', $bidCurrency))) {
                            $bidCurrency = null;
                        }

                        $detail->current_bid_price = $bidPrice;
                        $detail->current_bid_currency = $bidPrice !== null ? ($bidCurrency ?? 'USD') : null;
                        $detail->current_bid_fetched_at = $bidPrice !== null ? Carbon::now() : null;
                        $detail->save();

                        $processed++;
                        $this->line(sprintf('• Listing #%d updated (%s)', $detail->listing_id, $bidPrice !== null ? number_format($bidPrice, 0, '.', ' ') : 'no bid'));
                    } catch (\Throwable $e) {
                        $errors++;
                        Log::warning('Failed to refresh current bid', [
                            'listing_id' => $detail->listing_id,
                            'url' => $detail->source_auction_url,
                            'error' => $e->getMessage(),
                        ]);
                        $this->warn(sprintf('Failed for listing %d: %s', $detail->listing_id, $e->getMessage()));
                    }
                }

                return $processed < $limit;
            });

        $this->info(sprintf('Done. Updated %d listings (%d errors).', $processed, $errors));

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
