<?php

namespace App\Console\Commands;

use App\Models\Listing;
use Illuminate\Console\Command;

class ExpireListings extends Command
{
    protected $signature = 'listings:expire';
    protected $description = 'Set status to "expired" for listings older than 60 days';

    public function handle(): void
    {
        $count = Listing::where('status', 'active')
            ->where('created_at', '<', now()->subDays(60))
            ->update(['status' => 'expired']);

        $this->info("Expired {$count} listings.");
    }
}
