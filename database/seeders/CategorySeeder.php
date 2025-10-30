<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Универсальная поддержка MySQL и SQLite
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }

        DB::table('categories')->truncate();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        // 1. Создание родительских категорий
        $transport = Category::create([
            'name' => ['ru' => 'Транспорт', 'en' => 'Transport', 'hy' => 'Տրանսպորտ'],
            'slug' => 'transport'
        ]);
        $realty = Category::create([
            'name' => ['ru' => 'Недвижимость', 'en' => 'Real Estate', 'hy' => 'Անշարժ գույք'],
            'slug' => 'realty'
        ]);
        $electronics = Category::create([
            'name' => ['ru' => 'Электроника', 'en' => 'Electronics', 'hy' => 'Էլեկտրոնիկա'],
            'slug' => 'electronics'
        ]);
        $personalItems = Category::create([
            'name' => ['ru' => 'Личные вещи', 'en' => 'Personal Items', 'hy' => 'Անձնական իրեր'],
            'slug' => 'personal-items'
        ]);
        $homeAndGarden = Category::create([
            'name' => ['ru' => 'Для дома и дачи', 'en' => 'Home & Garden', 'hy' => 'Տան և այգու համար'],
            'slug' => 'home-and-garden'
        ]);
        $hobbies = Category::create([
            'name' => ['ru' => 'Хобби и отдых', 'en' => 'Hobbies & Leisure', 'hy' => 'Հոբբի և հանգիստ'],
            'slug' => 'hobbies-and-leisure'
        ]);
        $animals = Category::create([
            'name' => ['ru' => 'Животные', 'en' => 'Animals', 'hy' => 'Կենդանիներ'],
            'slug' => 'animals'
        ]);
        $food = Category::create([
            'name' => ['ru' => 'Продукты питания', 'en' => 'Food', 'hy' => 'Սննդամթերք'],
            'slug' => 'food'
        ]);
        $jobs = Category::create([
            'name' => ['ru' => 'Работа', 'en' => 'Jobs', 'hy' => 'Աշխատանք'],
            'slug' => 'jobs'
        ]);
        $services = Category::create([
            'name' => ['ru' => 'Услуги', 'en' => 'Services', 'hy' => 'Ծառայություններ'],
            'slug' => 'services'
        ]);

        // 2. Создание дочерних категорий (привязываем к ID родителя)

        // Transport
        Category::create(['parent_id' => $transport->id, 'name' => ['ru' => 'Автомобили', 'en' => 'Cars', 'hy' => 'Ավտոմեքենաներ'], 'slug' => 'cars']);
        Category::create(['parent_id' => $transport->id, 'name' => ['ru' => 'Мотоциклы', 'en' => 'Motorcycles', 'hy' => 'Մոտոցիկլներ'], 'slug' => 'motorcycles']);
        Category::create(['parent_id' => $transport->id, 'name' => ['ru' => 'Грузовики', 'en' => 'Trucks', 'hy' => 'Բեռնատարներ'], 'slug' => 'trucks']);

        // Realty
        Category::create(['parent_id' => $realty->id, 'name' => ['ru' => 'Продажа квартир', 'en' => 'Apartments for Sale', 'hy' => 'Բնակարանների վաճառք'], 'slug' => 'apartments-sale']);
        Category::create(['parent_id' => $realty->id, 'name' => ['ru' => 'Аренда квартир', 'en' => 'Apartments for Rent', 'hy' => 'Բնակարանների վարձույթ'], 'slug' => 'apartments-rent']);

        // ... (и так далее для всех ваших категорий) ...

        $this->command->info('Категории с переводами успешно созданы!');
    }
}
