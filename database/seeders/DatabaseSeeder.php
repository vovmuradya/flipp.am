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
            RegionSeeder::class,
            CategorySeeder::class,
            CategoryFieldSeeder::class, // <-- ДОБАВЬТЕ ЭТУ СТРОКУ
            DemoDataSeeder::class,
        ]);
    }
}
