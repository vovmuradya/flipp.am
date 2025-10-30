<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class MainSeeder extends Seeder
{
    public function run()
    {
        // Создаем категорию Transport
        Category::create([
            'id' => 1,
            'name' => [
                'ru' => 'Транспорт',
                'en' => 'Transport',
                'hy' => 'Տրանսպորտ'
            ],
            'slug' => 'transport',
            'is_active' => true
        ]);

        // Создаем несколько тестовых брендов
        $this->call([
            CarBrandSeeder::class,
        ]);
    }
}
