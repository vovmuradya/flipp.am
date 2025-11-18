<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\UpdateCopartCookiesCommand::class,
        \App\Console\Commands\AuctionFetchCommand::class,
        \App\Console\Commands\RefreshCopartCookies::class,
        \App\Console\Commands\RefreshCopartCurrentBids::class,
        \App\Console\Commands\BackfillAuctionPhotos::class,
        \App\Console\Commands\ExpireEndedAuctions::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('copart:refresh-cookies --silent')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/copart-cookies.log'));

        $schedule->command('copart:refresh-current-bids --limit=150')
            ->dailyAt('04:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/copart-bids.log'));

        $schedule->command('auctions:backfill-photos --limit=20')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/auction-backfill.log'));

        $schedule->command('listings:expire-ended-auctions --limit=200')
            ->everyTenMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/auction-expire.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
