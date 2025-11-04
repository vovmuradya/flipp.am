<?php
namespace Database\Seeders;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем 10 обычных пользователей
        User::factory(10)->create(['role' => 'individual']);
        // Создаем 3 дилера (ТЗ v2.1: роль 'dealer' вместо 'agency')
        User::factory(3)->create(['role' => 'dealer']);

        $categories = Category::whereNotNull('parent_id')->get();
        $regions = Region::where('type', 'city')->get();
        $users = User::where('role', '!=', 'admin')->get();

        $hasListingType = Schema::hasColumn('listings', 'listing_type');
        $hasIsFromAuction = Schema::hasColumn('listings', 'is_from_auction');
        $hasVehicleDetails = Schema::hasTable('vehicle_details');

        // Создаем 50 объявлений
        for ($i = 0; $i < 50; $i++) {
            $title = fake()->sentence(3);
            $listingData = [
                'user_id' => $users->random()->id,
                'category_id' => $categories->random()->id,
                'region_id' => $regions->random()->id,
                'title' => $title,
                'slug' => Str::slug($title) . '-' . uniqid(),
                'description' => fake()->paragraph(5),
                'price' => fake()->numberBetween(100, 50000),
                'currency' => 'USD',
                'status' => 'active',
                'language' => 'ru',
            ];

            if ($hasListingType) {
                $listingData['listing_type'] = 'parts';
            }

            Listing::create($listingData);
        }

        // Дополняем тестовые данные объявлениями типа vehicle с деталями
        $vehicleCategoryIds = Category::whereIn('slug', ['cars', 'motorcycles', 'trucks'])
            ->pluck('id')
            ->toArray();

        $dealerUsers = $users->where('role', 'dealer')->values();

        if (!empty($vehicleCategoryIds) && $dealerUsers->isNotEmpty() && $regions->isNotEmpty()) {
            for ($i = 0; $i < 12; $i++) {
                $dealer = $dealerUsers[$i % $dealerUsers->count()];
                $categoryId = $vehicleCategoryIds[array_rand($vehicleCategoryIds)];
                $region = $regions->random();

                $make = fake()->randomElement(['Toyota', 'BMW', 'Audi', 'Mercedes-Benz', 'Honda', 'Ford']);
                $model = fake()->randomElement(['Camry', '3 Series', 'A4', 'C-Class', 'Civic', 'Focus']);
                $year = fake()->numberBetween(date('Y') - 10, date('Y'));
                $isAuction = fake()->boolean(35);

                $title = sprintf('%s %s %d', $make, $model, $year);

                $listingData = [
                    'user_id' => $dealer->id,
                    'category_id' => $categoryId,
                    'region_id' => $region->id,
                    'title' => $title,
                    'slug' => Str::slug($title) . '-' . uniqid(),
                    'description' => fake()->paragraph(4),
                    'price' => fake()->numberBetween(4000, 45000),
                    'currency' => 'USD',
                    'status' => 'active',
                    'language' => 'ru',
                ];

                if ($hasListingType) {
                    $listingData['listing_type'] = 'vehicle';
                }
                if ($hasIsFromAuction) {
                    $listingData['is_from_auction'] = $isAuction;
                }

                $listing = Listing::create($listingData);

                if ($hasVehicleDetails) {
                    $listing->vehicleDetail()->create([
                        'make' => $make,
                        'model' => $model,
                        'year' => $year,
                        'mileage' => fake()->numberBetween(10_000, 160_000),
                        'body_type' => fake()->randomElement(['sedan', 'suv', 'hatchback', 'wagon']),
                        'transmission' => fake()->randomElement(['automatic', 'manual', 'cvt']),
                        'fuel_type' => fake()->randomElement(['gasoline', 'diesel', 'hybrid']),
                        'engine_displacement_cc' => fake()->numberBetween(1400, 3200),
                        'exterior_color' => fake()->randomElement(['Белый', 'Черный', 'Серый', 'Синий', 'Красный']),
                        'is_from_auction' => $isAuction,
                        'source_auction_url' => $isAuction ? 'https://www.copart.com/lot/' . fake()->numberBetween(10000000, 99999999) : null,
                        'auction_ends_at' => $isAuction ? Carbon::now()->addDays(fake()->numberBetween(1, 7)) : null,
                    ]);
                }
            }
        }
    }
}
