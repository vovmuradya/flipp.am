<?php

namespace App\Console\Commands;

use App\Jobs\ImportAuctionPhotos;
use App\Models\Listing;
use App\Services\AuctionParserService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BackfillAuctionPhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auctions:backfill-photos {--limit=25 : Максимальное число объявлений за проход}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Догрузить фотографии для аукционных объявлений, у которых пустая коллекция auction_photos';

    /**
     * Execute the console command.
     */
    public function handle(AuctionParserService $parser): int
    {
        $limit = (int) $this->option('limit') ?: 25;
        $limit = max(1, min($limit, 100));

        $listings = $this->queryListingsWithoutPhotos($limit);

        if ($listings->isEmpty()) {
            $this->info('Все аукционные объявления уже имеют фото.');
            return Command::SUCCESS;
        }

        $this->info(sprintf('Найдено %d объявлений без фото. Начинаю догрузку…', $listings->count()));

        foreach ($listings as $listing) {
            $detail = $listing->vehicleDetail;
            $sourceUrl = $detail?->source_auction_url;

            if (!$sourceUrl) {
                $this->warn("Listing #{$listing->id}: пропущен (нет source_auction_url)");
                continue;
            }

            try {
                $parsed = $parser->parseFromUrl(
                    $sourceUrl,
                    aggressive: (bool) config('services.copart.aggressive', false)
                );

                $photos = $parsed['photos'] ?? [];
                if (empty($photos)) {
                    $message = "Listing #{$listing->id}: Copart вернул пустой список фото.";
                    $this->warn($message);
                    Log::warning($message, ['listing_id' => $listing->id, 'source_url' => $sourceUrl]);
                    continue;
                }

                ImportAuctionPhotos::dispatchSync($listing->id, $photos);

                $this->info("Listing #{$listing->id}: добавлено " . count($photos) . ' фото.');
            } catch (\Throwable $exception) {
                $message = "Listing #{$listing->id}: ошибка {$exception->getMessage()}";
                $this->error($message);
                Log::error('BackfillAuctionPhotos error', [
                    'listing_id' => $listing->id,
                    'source_url' => $sourceUrl,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return Command::SUCCESS;
    }

    /**
     * @return Collection<int, Listing>
     */
    private function queryListingsWithoutPhotos(int $limit): Collection
    {
        return Listing::query()
            ->with(['vehicleDetail', 'media'])
            ->whereHas('vehicleDetail', function (Builder $query) {
                $query->whereNotNull('source_auction_url');
            })
            ->whereDoesntHave('media', function (Builder $query) {
                $query->where('collection_name', 'auction_photos');
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}
