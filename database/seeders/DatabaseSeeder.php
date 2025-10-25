<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Создаем администратора только если его нет
        User::firstOrCreate(
            ['email' => 'admin@flipp.am'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'), // пароль: password
                'role' => 'admin',
            ]
        );

        // Вызываем остальные сидеры
        $this->call([
            CategorySeeder::class,
            RegionSeeder::class,
            CategoryFieldSeeder::class,
            CarBrandSeeder::class,     // <-- Вы это уже сделали
            CarModelSeeder::class,     // <-- ДОБАВЬТЕ ЭТУ СТРОКУ
            CarGenerationSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
