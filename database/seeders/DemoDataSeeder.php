<?php
namespace Database\Seeders;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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

        // Создаем 50 объявлений
        for ($i = 0; $i < 50; $i++) {
            $title = fake()->sentence(3);
            Listing::create([
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
            ]);
        }
    }
}
