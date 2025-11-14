<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AuctionFetchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auction:fetch {--dry-run : Only log action without touching data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Placeholder command for fetching auctions (currently logs activity).';

    public function handle(): int
    {
        if ($this->option('dry-run')) {
            $this->info('Auction fetch dry-run completed (no work executed).');
            return self::SUCCESS;
        }

        Log::info('auction:fetch executed (placeholder command).');
        $this->info('Auction fetch executed (placeholder).');

        return self::SUCCESS;
    }
}
