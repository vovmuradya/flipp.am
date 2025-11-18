<?php

namespace App\Console\Commands;

use App\Models\Listing;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ExpireEndedAuctions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'listings:expire-ended-auctions
        {--limit=200 : Максимальное число объявлений за один запуск}
        {--dry-run : Показать, что будет удалено, но не удалять}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Находит аукционные объявления, срок которых истёк, и мягко удаляет их.';

    public function handle(): int
    {
        $limit = (int) $this->option('limit') ?: 200;
        if ($limit < 1) {
            $limit = 1;
        }

        $dryRun = (bool) $this->option('dry-run');
        $now = Carbon::now();

        $expiredListings = Listing::query()
            ->with('vehicleDetail')
            ->whereNull('deleted_at')
            ->whereHas('vehicleDetail', function (Builder $query) use ($now) {
                $query->where('is_from_auction', true)
                    ->whereNotNull('auction_ends_at')
                    ->where('auction_ends_at', '<=', $now);
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        if ($expiredListings->isEmpty()) {
            $this->info('Просроченных аукционных объявлений не найдено.');
            return self::SUCCESS;
        }

        $this->info(sprintf('Найдено %d объявлений для автоудаления.', $expiredListings->count()));

        foreach ($expiredListings as $listing) {
            $detail = $listing->vehicleDetail;
            if (!$detail) {
                continue;
            }

            $endedAt = $detail->auction_ends_at?->toDateTimeString() ?? 'неизвестно';
            $this->line(sprintf(
                '#%d | %s | окончание: %s',
                $listing->id,
                $listing->title ?? 'без названия',
                $endedAt
            ));

            if ($dryRun) {
                continue;
            }

            $listing->delete();
            Log::info('ExpireEndedAuctions: listing soft deleted', [
                'listing_id' => $listing->id,
                'ended_at' => $detail->auction_ends_at?->toIso8601String(),
            ]);
        }

        if ($dryRun) {
            $this->info('Dry-run завершён, данные не изменялись.');
        } else {
            $this->info('Готово: завершённые аукционы скрыты из поиска.');
        }

        return self::SUCCESS;
    }
}
