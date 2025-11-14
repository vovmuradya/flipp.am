<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AuctionListingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $listings = Listing::query()
            ->where('user_id', Auth::id())
            ->where('listing_type', 'vehicle')
            ->fromAuction()
            ->latest()
            ->paginate(15);

        return view('auction_listings.index', compact('listings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'auction_data' => 'required|json'
        ]);

        $auctionData = json_decode($request->input('auction_data'), true);

        try {
            DB::beginTransaction();

            $listing = Listing::create([
                'user_id' => Auth::id(),
                'title' => $auctionData['title'],
                'description' => $auctionData['description'],
                'price' => $auctionData['price'] ?? 0,
                'category_id' => $auctionData['category_id'],
                'region_id' => 1, // Default region, can be changed
                'listing_type' => 'vehicle',
                'status' => 'active',
                'is_from_auction' => true,
            ]);

            $vehicleData = $auctionData['vehicle'];
            $vehicleDetail = $listing->vehicleDetail()->create([
                'make' => $vehicleData['make'] ?? null,
                'model' => $vehicleData['model'] ?? null,
                'year' => $vehicleData['year'] ?? null,
                'mileage' => $vehicleData['mileage'] ?? null,
                'body_type' => $vehicleData['body_type'] ?? null,
                'transmission' => $vehicleData['transmission'] ?? null,
                'fuel_type' => $vehicleData['fuel_type'] ?? null,
                'engine_displacement_cc' => $vehicleData['engine_displacement_cc'] ?? null,
                'exterior_color' => $vehicleData['exterior_color'] ?? null,
                'is_from_auction' => true,
                'source_auction_url' => $auctionData['auction_url'] ?? null,
                'buy_now_price' => $vehicleData['buy_now_price'] ?? null,
                'buy_now_currency' => $vehicleData['buy_now_currency'] ?? null,
                'operational_status' => $vehicleData['operational_status'] ?? null,
                'auction_ends_at' => $vehicleData['auction_ends_at'] ?? null,
            ]);

            $photoSources = [];
            if (!empty($auctionData['photos']) && is_array($auctionData['photos'])) {
                $photoSources = array_merge($photoSources, $auctionData['photos']);
            }
            if (isset($auctionData['vehicle']['photos']) && is_array($auctionData['vehicle']['photos'])) {
                $photoSources = array_merge($photoSources, $auctionData['vehicle']['photos']);
            }

            $resolvedUrls = [];
            foreach ($photoSources as $photo) {
                $resolvedUrl = $this->normalizePhotoUrl($photo);
                if (!$resolvedUrl) {
                    continue;
                }

                $resolvedUrls[$resolvedUrl] = true;
            }

            $photoUrls = array_keys($resolvedUrls);

            if (!empty($photoUrls) && isset($vehicleDetail) && Schema::hasColumn('vehicle_details', 'preview_image_url')) {
                if (empty($vehicleDetail->preview_image_url)) {
                    $vehicleDetail->preview_image_url = $photoUrls[0];
                    $vehicleDetail->save();
                }
            }

            foreach ($photoUrls as $photoUrl) {
                try {
                    $listing->addMediaFromUrl($photoUrl)->toMediaCollection('images');
                } catch (\Exception $e) {
                    Log::error('Failed to add media from URL: ' . $e->getMessage(), [
                        'url' => $photoUrl,
                        'listing_id' => $listing->id,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('auction-listings.edit', $listing)->with('success', 'Аукционное объявление создано. Теперь вы можете установить цену и отредактировать описание.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Auction Listing Store Error: ' . $e->getMessage());
            return redirect()->route('listings.create-from-auction')->withErrors(['error' => 'Не удалось создать объявление: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Listing $auctionListing)
    {
        return view('auction_listings.show', ['listing' => $auctionListing]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Listing $auctionListing)
    {
        $this->authorize('update', $auctionListing);
        return view('auction_listings.edit', ['listing' => $auctionListing]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Listing $auctionListing)
    {
        $this->authorize('update', $auctionListing);

        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'description' => 'required|string|max:5000',
        ]);

        $auctionListing->update($validated);

        return redirect()->route('dashboard.my-auctions')->with('success', 'Аукционное объявление успешно обновлено.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Listing $auctionListing)
    {
        $this->authorize('delete', $auctionListing);
        $auctionListing->delete();
        return redirect()->route('dashboard.my-auctions')->with('success', 'Аукционное объявление успешно удалено.');
    }

    private function normalizePhotoUrl(mixed $photo): ?string
    {
        $photoUrl = null;

        if (is_string($photo)) {
            $photoUrl = trim($photo);
        } elseif (is_array($photo)) {
            foreach (['url', 'full', 'large', 'src', 'path'] as $key) {
                if (!empty($photo[$key]) && is_string($photo[$key])) {
                    $photoUrl = trim($photo[$key]);
                    break;
                }
            }
        }

        if (empty($photoUrl)) {
            return null;
        }

        if ($this->isPlaceholderPhoto($photoUrl)) {
            return null;
        }

        if (str_starts_with($photoUrl, '//')) {
            $photoUrl = 'https:' . $photoUrl;
        } elseif (str_starts_with($photoUrl, '/')) {
            $baseUrl = rtrim(config('app.url') ?? url('/'), '/');
            $photoUrl = $baseUrl . $photoUrl;
        }

        if (!preg_match('#^https?://#i', $photoUrl)) {
            return null;
        }

        return filter_var($photoUrl, FILTER_VALIDATE_URL) ? $photoUrl : null;
    }

    private function isPlaceholderPhoto(string $url): bool
    {
        $lower = strtolower($url);

        return str_contains($lower, 'placeholder')
            || str_contains($lower, 'no+image')
            || str_contains($lower, 'no%20image')
            || str_contains($lower, 'no_image');
    }
}
