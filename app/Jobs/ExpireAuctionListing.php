<?php

namespace App\Jobs;

use App\Models\Listing;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExpireAuctionListing implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $listingId;

    public $timeout = 60;

    public function __construct(int $listingId)
    {
        $this->listingId = $listingId;
        $this->onQueue('media');
    }

    public function handle(): void
    {
        $listing = Listing::with('vehicleDetail')->find($this->listingId);
        if (!$listing) {
            Log::info('ExpireAuctionListing: listing already removed', ['listing_id' => $this->listingId]);
            return;
        }

        $detail = $listing->vehicleDetail;
        if (!$detail || !$detail->auction_ends_at) {
            Log::info('ExpireAuctionListing: no auction end time', ['listing_id' => $this->listingId]);
            return;
        }

        $now = Carbon::now();
        $end = $detail->auction_ends_at instanceof Carbon ? $detail->auction_ends_at : Carbon::parse($detail->auction_ends_at);

        if ($end->isFuture()) {
            static::dispatch($this->listingId)->delay($end);
            Log::info('ExpireAuctionListing: rescheduled', [
                'listing_id' => $this->listingId,
                'run_at' => $end->toIso8601String(),
            ]);
            return;
        }

        $listing->delete();
        Log::info('ExpireAuctionListing: listing soft deleted', ['listing_id' => $this->listingId]);
    }
}
