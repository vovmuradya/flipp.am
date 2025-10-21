<?php
namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create parent categories
        $transport = Category::create(['name' => 'Транспорт', 'slug' => 'transport']);
        $realty = Category::create(['name' => 'Недвижимость', 'slug' => 'realty']);
        $electronics = Category::create(['name' => 'Электроника', 'slug' => 'electronics']);
        $personalItems = Category::create(['name' => 'Личные вещи', 'slug' => 'personal-items']);
        $homeAndGarden = Category::create(['name' => 'Для дома и дачи', 'slug' => 'home-and-garden']);
        $hobbies = Category::create(['name' => 'Хобби и отдых', 'slug' => 'hobbies-and-leisure']);
        $animals = Category::create(['name' => 'Животные', 'slug' => 'animals']);
        $food = Category::create(['name' => 'Продукты питания', 'slug' => 'food']);
        $jobs = Category::create(['name' => 'Работа', 'slug' => 'jobs']);
        $services = Category::create(['name' => 'Услуги', 'slug' => 'services']);

        // Create child categories
        // Transport
        Category::create(['parent_id' => $transport->id, 'name' => 'Автомобили', 'slug' => 'cars']);
        Category::create(['parent_id' => $transport->id, 'name' => 'Мотоциклы', 'slug' => 'motorcycles']);
        Category::create(['parent_id' => $transport->id, 'name' => 'Грузовики', 'slug' => 'trucks']);

        // Realty
        Category::create(['parent_id' => $realty->id, 'name' => 'Продажа квартир', 'slug' => 'apartments-sale']);
        Category::create(['parent_id' => $realty->id, 'name' => 'Аренда квартир', 'slug' => 'apartments-rent']);
        Category::create(['parent_id' => $realty->id, 'name' => 'Продажа домов', 'slug' => 'houses-sale']);
        Category::create(['parent_id' => $realty->id, 'name' => 'Земельные участки', 'slug' => 'land-plots']);
        Category::create(['parent_id' => $realty->id, 'name' => 'Коммерческая недвижимость', 'slug' => 'commercial-real-estate']);

        // Electronics
        Category::create(['parent_id' => $electronics->id, 'name' => 'Ноутбуки', 'slug' => 'laptops']);
        Category::create(['parent_id' => $electronics->id, 'name' => 'Телефоны', 'slug' => 'phones']);
        Category::create(['parent_id' => $electronics->id, 'name' => 'Планшеты', 'slug' => 'tablets']);
        Category::create(['parent_id' => $electronics->id, 'name' => 'Телевизоры', 'slug' => 'tvs']);
        Category::create(['parent_id' => $electronics->id, 'name' => 'Фототехника', 'slug' => 'photo-equipment']);
        Category::create(['parent_id' => $electronics->id, 'name' => 'Аудиотехника', 'slug' => 'audio-equipment']);
        Category::create(['parent_id' => $electronics->id, 'name' => 'Настольные компьютеры', 'slug' => 'desktop-computers']);
        Category::create(['parent_id' => $electronics->id, 'name' => 'Игровые приставки', 'slug' => 'game-consoles']);

        // Personal Items
        Category::create(['parent_id' => $personalItems->id, 'name' => 'Одежда', 'slug' => 'clothing']);
        Category::create(['parent_id' => $personalItems->id, 'name' => 'Обувь', 'slug' => 'shoes']);
        Category::create(['parent_id' => $personalItems->id, 'name' => 'Аксессуары', 'slug' => 'accessories']);
        Category::create(['parent_id' => $personalItems->id, 'name' => 'Часы', 'slug' => 'watches']);
        Category::create(['parent_id' => $personalItems->id, 'name' => 'Красота и здоровье', 'slug' => 'beauty-and-health']);
        Category::create(['parent_id' => $personalItems->id, 'name' => 'Товары для детей', 'slug' => 'kids-products']);
        Category::create(['parent_id' => $personalItems->id, 'name' => 'Игрушки', 'slug' => 'toys']);

        // Home and Garden
        Category::create(['parent_id' => $homeAndGarden->id, 'name' => 'Мебель', 'slug' => 'furniture']);
        Category::create(['parent_id' => $homeAndGarden->id, 'name' => 'Бытовая техника', 'slug' => 'home-appliances']);
        Category::create(['parent_id' => $homeAndGarden->id, 'name' => 'Техника для кухни', 'slug' => 'kitchen-appliances']);
        Category::create(['parent_id' => $homeAndGarden->id, 'name' => 'Ремонт и строительство', 'slug' => 'repair-and-construction']);
        Category::create(['parent_id' => $homeAndGarden->id, 'name' => 'Инструменты', 'slug' => 'tools']);
        Category::create(['parent_id' => $homeAndGarden->id, 'name' => 'Сад и огород', 'slug' => 'garden-and-outdoors']);

        // Hobbies and Leisure
        Category::create(['parent_id' => $hobbies->id, 'name' => 'Книги', 'slug' => 'books']);
        Category::create(['parent_id' => $hobbies->id, 'name' => 'Спорт и отдых', 'slug' => 'sports-and-leisure']);
        Category::create(['parent_id' => $hobbies->id, 'name' => 'Хобби и творчество', 'slug' => 'hobbies-and-crafts']);
        Category::create(['parent_id' => $hobbies->id, 'name' => 'Музыкальные инструменты', 'slug' => 'musical-instruments']);
        Category::create(['parent_id' => $hobbies->id, 'name' => 'Настольные игры', 'slug' => 'board-games']);

        // Animals is a parent category, we will create a child for it
        Category::create(['parent_id' => $animals->id, 'name' => 'Животные', 'slug' => 'pets']);

        // Child categories with corrected, unique slugs
        Category::create(['parent_id' => $food->id, 'name' => 'Продукты', 'slug' => 'food-products']);
        Category::create(['parent_id' => $jobs->id, 'name' => 'Вакансии', 'slug' => 'vacancies']);
        Category::create(['parent_id' => $jobs->id, 'name' => 'Резюме', 'slug' => 'resumes']);
        Category::create(['parent_id' => $services->id, 'name' => 'Предложение услуг', 'slug' => 'service-offers']);
    }
}
