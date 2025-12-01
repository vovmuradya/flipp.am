<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Services\Pricing\ImvService;
use App\Services\SellerScoreService;
use Illuminate\Console\Command;

class RecalculatePricingAndTrust extends Command
{
    protected $signature = 'listings:recalculate-pricing {--limit=500 : Max listings to process in this run}';
    protected $description = 'Пересчитать IMV/бейджи/аномалии и seller_score по активным объявлениям';

    public function handle(ImvService $imvService, SellerScoreService $sellerScoreService): int
    {
        $limit = (int) $this->option('limit');
        $processed = 0;

        Listing::query()
            ->with(['vehicleDetail', 'user'])
            ->active()
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->chunk(100, function ($listings) use (&$processed, $imvService, $sellerScoreService) {
                foreach ($listings as $listing) {
                    $result = $imvService->analyze($listing);
                    $listing->fill([
                        'imv_value' => $result['imv_value'] ?? null,
                        'price_badge' => $result['price_badge'] ?? null,
                        'price_anomaly' => $result['price_anomaly'] ?? false,
                        'price_analysis' => $result['price_analysis'] ?? null,
                    ])->save();

                    if ($listing->user) {
                        $sellerScoreService->update($listing->user, $listing);
                    }
                    $processed++;
                }
            });

        $this->info("Processed {$processed} listings");

        return self::SUCCESS;
    }
}
