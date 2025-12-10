<?php

namespace App\Console\Commands;

use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\CarGeneration;
use App\Models\Listing;
use App\Models\Region;
use App\Models\User;
use App\Support\VehicleCategoryResolver;
use Database\Seeders\CarBrandSeeder;
use Database\Seeders\CarGenerationSeeder;
use Database\Seeders\CarModelSeeder;
use Database\Seeders\RegionSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CreateSampleListing extends Command
{
    protected $signature = 'demo:create-listing {--dealer-email=dealer@example.com}';
    protected $description = 'Create a demo vehicle listing with seeded brands/models to quickly preview the UI.';

    public function handle(): int
    {
        $this->info('🚀 Preparing environment for demo listing...');

        $categoryId = VehicleCategoryResolver::resolve();
        if (!$categoryId) {
            $this->error('Не удалось определить категорию для транспорта. Проверьте таблицу categories.');
            return self::FAILURE;
        }

        $region = $this->ensureRegions();
        if (!$region) {
            $this->error('Не удалось создать регионы. Проверьте RegionSeeder.');
            return self::FAILURE;
        }

        $this->ensureCarDictionaries();

        $user = $this->ensureDealer($this->option('dealer-email'));

        $listing = $this->createOrUpdateListing($user->id, $categoryId, $region->id);

        $previewUrl = rtrim(config('app.url'), '/') . '/listings/' . $listing->slug;
        $this->newLine();
        $this->info('✅ Demo listing ready!');
        $this->line(' • Listing ID: ' . $listing->id);
        $this->line(' • URL: ' . $previewUrl);
        $this->line(' • Dealer email: ' . $user->email);
        $this->line(' • Dealer password: secret123 (измените при необходимости)');

        return self::SUCCESS;
    }

    protected function ensureRegions(): ?Region
    {
        if (Region::count() === 0) {
            $this->callSeedOnce(RegionSeeder::class);
        }

        return Region::where('type', 'city')->first();
    }

    protected function ensureCarDictionaries(): void
    {
        if (CarBrand::count() === 0) {
            $this->callSeedOnce(CarBrandSeeder::class);
        }
        if (CarModel::count() === 0) {
            $this->callSeedOnce(CarModelSeeder::class);
        }
        if (Schema::hasTable('car_generations') && CarGeneration::count() === 0) {
            $this->callSeedOnce(CarGenerationSeeder::class);
        }
    }

    protected function ensureDealer(string $email): User
    {
        return User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Demo Dealer',
                'password' => Hash::make('secret123'),
                'role' => 'dealer',
            ]
        );
    }

    protected function createOrUpdateListing(int $userId, int $categoryId, int $regionId): Listing
    {
        $slug = 'demo-nissan-rogue';
        $title = '2022 Nissan Rogue SV';

        $listingData = [
            'user_id' => $userId,
            'category_id' => $categoryId,
            'region_id' => $regionId,
            'title' => $title,
            'slug' => $slug,
            'description' => "Полностью укомплектованный Nissan Rogue SV 2022 года.\n- Пробег: 24 000 км\n- Полный пакет безопасности Nissan Safety Shield 360\n- Салон из ткани Graphite, подогрев передних сидений\n- Доступ без ключа, запуск кнопкой, адаптивный круиз-контроль",
            'price' => 24500,
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

        $listing = Listing::updateOrCreate(['slug' => $slug], $listingData);

        $vehicleData = [
            'make' => 'Nissan',
            'model' => 'Rogue',
            'year' => 2022,
            'mileage' => 24000,
            'body_type' => 'suv',
            'transmission' => 'automatic',
            'fuel_type' => 'gasoline',
            'engine_displacement_cc' => 2488,
            'exterior_color' => 'Gun Metallic',
            'is_from_auction' => false,
            'source_auction_url' => null,
            'auction_ends_at' => null,
        ];

        $listing->vehicleDetail()->updateOrCreate([], $vehicleData);

        if (Schema::hasColumn('vehicle_details', 'preview_image_url')) {
            $listing->vehicleDetail()->update([
                'preview_image_url' => 'https://placehold.co/800x600/e5e7eb/1f2937/png?text=Nissan+Rogue+SV',
            ]);
        }

        return $listing;
    }

    protected function callSeedOnce(string $seederClass): void
    {
        $this->info("↻ Seeding using {$seederClass}...");
        Artisan::call('db:seed', [
            '--class' => $seederClass,
            '--force' => true,
        ]);
    }
}
