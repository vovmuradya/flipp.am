<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HighlightedListingSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('listings')) {
            return;
        }

        $exists = Listing::where('slug', 'asia-rocsta-2008-demo')->exists();
        if ($exists) {
            return;
        }

        $user = User::where('role', 'dealer')->first() ?? User::factory()->create([
            'name' => 'Asia Dealer',
            'email' => 'asia-dealer@example.com',
            'password' => bcrypt('secret123'),
            'role' => 'dealer',
        ]);

        $region = Region::where('name', 'Գյումրի')->first() ?? Region::first();
        $category = Category::whereIn('slug', ['cars', 'vehicles'])->first() ?? Category::first();

        if (!$user || !$region || !$category) {
            return;
        }

        $listingData = [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'region_id' => $region->id,
            'title' => 'Asia Rocsta 2008',
            'slug' => 'asia-rocsta-2008-demo',
            'description' => "Коллекционный внедорожник Asia Rocsta 2008 года выпуска.\n- Полный привод, механическая трансмиссия\n- Съёмный мягкий верх, экспедиционный багажник\n- Доступен осмотр в Гюмри, помощь с доставкой",
            'price' => 23400,
            'currency' => 'USD',
            'status' => 'active',
            'language' => 'ru',
        ];

        if (Schema::hasColumn('listings', 'listing_type')) {
            $listingData['listing_type'] = 'vehicle';
        }
        if (Schema::hasColumn('listings', 'is_from_auction')) {
            $listingData['is_from_auction'] = false;
        }

        $listing = Listing::create($listingData);

        if (Schema::hasTable('vehicle_details')) {
            $listing->vehicleDetail()->create([
                'make' => 'Asia',
                'model' => 'Rocsta',
                'year' => 2008,
                'mileage' => 234000,
                'body_type' => 'suv',
                'transmission' => 'manual',
                'fuel_type' => 'diesel',
                'engine_displacement_cc' => 1100,
                'exterior_color' => 'orange',
                'is_from_auction' => false,
                'source_auction_url' => null,
                'preview_image_url' => '/images/no-image.jpg',
            ]);
        }

        $placeholder = public_path('images/no-image.jpg');
        if (file_exists($placeholder)) {
            $listing->addMedia($placeholder)
                ->preservingOriginal()
                ->toMediaCollection('images');
        }
    }
}
