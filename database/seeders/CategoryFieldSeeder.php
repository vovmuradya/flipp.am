<?php
namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryField;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryFieldSeeder extends Seeder
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

        DB::table('category_category_field')->truncate();
        CategoryField::query()->truncate();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        // 2. Ваша большая структура данных со всеми полями для всех категорий
        $fieldsBySlug = [
            // --- 🚗 CARS ---
            'cars' => [
                // ИЗМЕНЕНИЕ: УДАЛЕН ЖЕСТКИЙ СПИСОК ОПЦИЙ ДЛЯ МАРКИ
                ['name' => 'Марка', 'key' => 'brand', 'type' => 'select', 'is_required' => true, 'options' => null],
                // --------------------------------------------------
                ['name' => 'Модель', 'key' => 'model', 'type' => 'text', 'is_required' => true, 'options' => null],
                ['name' => 'Поколение', 'key' => 'generation', 'type' => 'text', 'is_required' => false, 'options' => null],
                ['name' => 'Год выпуска', 'key' => 'year', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Пробег, км', 'key' => 'mileage', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у", "Поврежденный"])],
                ['name' => 'Растаможен', 'key' => 'customs_cleared', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Да", "Нет"])],
                ['name' => 'Тип кузова', 'key' => 'body_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Седан", "Внедорожник", "Купе", "Хэтчбек", "Универсал", "Кабриолет", "Минивэн", "Лифтбек", "Пикап", "Лимузин", "Фургон", "Микроавтобус"])],
                ['name' => 'Цвет', 'key' => 'color', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Белый", "Черный", "Серебристый", "Серый", "Красный", "Синий", "Коричневый", "Зеленый", "Желтый", "Оранжевый", "Золотой", "Бежевый", "Фиолетовый", "Голубой", "Розовый"])],
                ['name' => 'Тип двигателя', 'key' => 'engine_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Бензин", "Дизель", "Гибрид", "Электро", "Газ", "Газ/Бензин"])],
                ['name' => 'Объем двигателя, л', 'key' => 'engine_volume', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Мощность двигателя, л.с.', 'key' => 'engine_power', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Коробка передач', 'key' => 'transmission', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Механика", "Автомат", "Робот", "Вариатор"])],
                ['name' => 'Привод', 'key' => 'drive_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Передний", "Задний", "Полный"])],
                ['name' => 'Руль', 'key' => 'steering_wheel', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Левый", "Правый"])],
                ['name' => 'Количество владельцев', 'key' => 'owners', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["1", "2", "3 и более"])],
                ['name' => 'ПТС', 'key' => 'pts', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Оригинал", "Дубликат", "Электронный"])],
                ['name' => 'VIN', 'key' => 'vin', 'type' => 'text', 'is_required' => false, 'options' => null],
                ['name' => 'Обмен', 'key' => 'exchange', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Возможен", "Не интересует"])],
            ],
            // --- 🏍️ MOTORCYCLES ---
            'motorcycles' => [
                ['name' => 'Тип', 'key' => 'moto_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Мотоцикл", "Скутер", "Мопед", "Квадроцикл", "Снегоход", "Гидроцикл"])],
                ['name' => 'Марка', 'key' => 'brand', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Honda", "Yamaha", "Suzuki", "Kawasaki", "Harley-Davidson", "BMW", "Ducati", "KTM", "Aprilia", "Triumph", "Урал", "ИЖ"])],
                ['name' => 'Модель', 'key' => 'model', 'type' => 'text', 'is_required' => true, 'options' => null],
                ['name' => 'Год выпуска', 'key' => 'year', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Пробег, км', 'key' => 'mileage', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Объем двигателя, см³', 'key' => 'engine_volume', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Тип двигателя', 'key' => 'engine_type', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["2-тактный", "4-тактный", "Электрический"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у", "Требует ремонта"])],
            ],
            // --- 🚚 TRUCKS ---
            'trucks' => [
                ['name' => 'Тип', 'key' => 'truck_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Фургон", "Тентованный", "Бортовой", "Самосвал", "Рефрижератор", "Эвакуатор", "Кран-манипулятор", "Цистерна"])],
                ['name' => 'Марка', 'key' => 'brand', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["ГАЗ", "КамАЗ", "МАЗ", "ЗИЛ", "УАЗ", "Mercedes-Benz", "Volvo", "Scania", "MAN", "Isuzu", "Hyundai"])],
                ['name' => 'Модель', 'key' => 'model', 'type' => 'text', 'is_required' => true, 'options' => null],
                ['name' => 'Год выпуска', 'key' => 'year', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Пробег, км', 'key' => 'mileage', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Грузоподъемность, т', 'key' => 'capacity', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у", "Требует ремонта"])],
            ],
            // --- 🏠 APARTMENT SALE ---
            'apartments-sale' => [
                ['name' => 'Количество комнат', 'key' => 'rooms', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Студия", "1", "2", "3", "4", "5", "6+", "Свободная планировка"])],
                ['name' => 'Тип жилья', 'key' => 'property_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Вторичка", "Новостройка"])],
                ['name' => 'Общая площадь, м²', 'key' => 'total_area', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Жилая площадь, м²', 'key' => 'living_area', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Площадь кухни, м²', 'key' => 'kitchen_area', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Этаж', 'key' => 'floor', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Этажность', 'key' => 'total_floors', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Тип дома', 'key' => 'building_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Кирпичный", "Панельный", "Монолитный", "Блочный", "Деревянный", "Сталинка"])],
                ['name' => 'Санузел', 'key' => 'bathroom', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Совмещенный", "Раздельный", "2 и более"])],
                ['name' => 'Балкон/лоджия', 'key' => 'balcony', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Балкон", "Лоджия", "Несколько", "Нет"])],
                ['name' => 'Ремонт', 'key' => 'renovation', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Без ремонта", "Косметический", "Евроремонт", "Дизайнерский"])],
                ['name' => 'Парковка', 'key' => 'parking', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Наземная", "Подземная", "Многоуровневая", "Нет"])],
            ],
            // --- 🏠 APARTMENT RENT ---
            'apartments-rent' => [
                ['name' => 'Количество комнат', 'key' => 'rooms', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Студия", "1", "2", "3", "4", "5+"])],
                ['name' => 'Срок аренды', 'key' => 'rent_period', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Длительный", "Посуточно", "На несколько месяцев"])],
                ['name' => 'Общая площадь, м²', 'key' => 'total_area', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Этаж', 'key' => 'floor', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Этажность', 'key' => 'total_floors', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Мебель', 'key' => 'furniture', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Есть", "Нет", "Частично"])],
                ['name' => 'Техника', 'key' => 'appliances', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Есть", "Нет", "Частично"])],
                ['name' => 'Можно с животными', 'key' => 'pets_allowed', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Разрешено", "Не разрешено"])],
                ['name' => 'Можно с детьми', 'key' => 'children_allowed', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Разрешено", "Не разрешено"])],
                ['name' => 'Коммунальные платежи', 'key' => 'utilities', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Включены", "Не включены"])],
                ['name' => 'Залог', 'key' => 'deposit', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Нет", "Требуется"])],
            ],
            // --- 🏡 HOUSES FOR SALE ---
            'houses-sale' => [
                ['name' => 'Тип дома', 'key' => 'house_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Дом", "Дача", "Коттедж", "Таунхаус", "Часть дома"])],
                ['name' => 'Площадь дома, м²', 'key' => 'house_area', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Площадь участка, сот.', 'key' => 'land_area', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Количество комнат', 'key' => 'rooms', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Этажей', 'key' => 'floors', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Материал стен', 'key' => 'wall_material', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Кирпич", "Дерево", "Блоки", "Панели", "Каркасный", "Монолитный"])],
                ['name' => 'Отопление', 'key' => 'heating', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Газовое", "Электрическое", "Твердое топливо", "Нет отопления"])],
                ['name' => 'Водоснабжение', 'key' => 'water', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Центральное", "Скважина", "Колодец", "Нет"])],
                ['name' => 'Канализация', 'key' => 'sewerage', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Центральная", "Септик", "Выгребная яма", "Нет"])],
                ['name' => 'Электричество', 'key' => 'electricity', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Есть", "Нет", "Рядом"])],
            ],
            // --- 🌳 LAND PLOTS ---
            'land-plots' => [
                ['name' => 'Назначение земли', 'key' => 'land_purpose', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["ИЖС", "Садоводство", "Фермерское", "Коммерческое"])],
                ['name' => 'Площадь, сот.', 'key' => 'area', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Электричество', 'key' => 'electricity', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Есть", "Нет", "Рядом", "Можно подключить"])],
                ['name' => 'Водоснабжение', 'key' => 'water', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Есть", "Нет", "Рядом", "Скважина", "Колодец"])],
                ['name' => 'Газ', 'key' => 'gas', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Есть", "Нет", "Рядом", "Можно подключить"])],
                ['name' => 'Дорога', 'key' => 'road', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Асфальт", "Грунтовая", "Нет дороги"])],
            ],
            // --- 🏢 COMMERCIAL REAL ESTATE ---
            'commercial-real-estate' => [
                ['name' => 'Тип', 'key' => 'commercial_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Офис", "Торговое помещение", "Склад", "Производственное", "Общепит", "Гараж", "Коммерческий участок"])],
                ['name' => 'Сделка', 'key' => 'operation', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Продажа", "Аренда"])],
                ['name' => 'Площадь, м²', 'key' => 'area', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Этаж', 'key' => 'floor', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Парковка', 'key' => 'parking', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Есть", "Нет"])],
                ['name' => 'Отдельный вход', 'key' => 'separate_entrance', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Есть", "Нет"])],
            ],
            // --- 💻 LAPTOPS ---
            'laptops' => [
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у", "Восстановленный"])],
                ['name' => 'Марка', 'key' => 'brand', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Apple", "Asus", "Acer", "Dell", "HP", "Lenovo", "MSI", "Huawei", "Samsung", "Xiaomi", "Microsoft"])],
                ['name' => 'Модель', 'key' => 'model', 'type' => 'text', 'is_required' => false, 'options' => null],
                ['name' => 'Диагональ экрана, дюймы', 'key' => 'screen_size', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["13", "13.3", "14", "15", "15.6", "16", "17", "17.3"])],
                ['name' => 'Процессор', 'key' => 'cpu', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Intel Core i3", "Intel Core i5", "Intel Core i7", "Intel Core i9", "AMD Ryzen 3", "AMD Ryzen 5", "AMD Ryzen 7", "AMD Ryzen 9", "Apple M1", "Apple M2", "Apple M3"])],
                ['name' => 'ОЗУ, ГБ', 'key' => 'ram', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["4", "8", "16", "32", "64"])],
                ['name' => 'Тип накопителя', 'key' => 'storage_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["SSD", "HDD", "SSD + HDD"])],
                ['name' => 'Объем накопителя, ГБ', 'key' => 'storage_size', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["128", "256", "512", "1000", "2000"])],
                ['name' => 'Видеокарта', 'key' => 'gpu', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Встроенная", "NVIDIA", "AMD"])],
                ['name' => 'Операционная система', 'key' => 'os', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Windows 11", "Windows 10", "macOS", "Linux", "Без ОС"])],
            ],
            // --- 📱 PHONES ---
            'phones' => [
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у", "Восстановленный"])],
                ['name' => 'Марка', 'key' => 'brand', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Apple", "Samsung", "Xiaomi", "Huawei", "Honor", "Realme", "Oppo", "Vivo", "OnePlus", "Google", "Nokia", "Motorola"])],
                ['name' => 'Модель', 'key' => 'model', 'type' => 'text', 'is_required' => true, 'options' => null],
                ['name' => 'Память, ГБ', 'key' => 'storage', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["32", "64", "128", "256", "512", "1024"])],
                ['name' => 'ОЗУ, ГБ', 'key' => 'ram', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["2", "3", "4", "6", "8", "12", "16"])],
                ['name' => 'Цвет', 'key' => 'color', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Черный", "Белый", "Серебристый", "Золотой", "Синий", "Красный", "Зеленый", "Другой"])],
                ['name' => 'Комплектация', 'key' => 'package', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Полная", "Без коробки", "Только телефон"])],
                ['name' => 'Гарантия', 'key' => 'warranty', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Есть", "Нет"])],
            ],
            // --- 📱 TABLETS ---
            'tablets' => [
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у"])],
                ['name' => 'Марка', 'key' => 'brand', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Apple", "Samsung", "Xiaomi", "Huawei", "Lenovo", "Amazon", "Microsoft"])],
                ['name' => 'Модель', 'key' => 'model', 'type' => 'text', 'is_required' => false, 'options' => null],
                ['name' => 'Диагональ экрана, дюймы', 'key' => 'screen_size', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["7", "8", "9", "10", "11", "12", "13"])],
                ['name' => 'Память, ГБ', 'key' => 'storage', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["32", "64", "128", "256", "512", "1024"])],
                ['name' => 'Операционная система', 'key' => 'os', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["iOS", "iPadOS", "Android", "Windows"])],
                ['name' => 'Подключение', 'key' => 'connectivity', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Wi-Fi", "Wi-Fi + Cellular"])],
            ],
            // --- 📺 TVS ---
            'tvs' => [
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у"])],
                ['name' => 'Марка', 'key' => 'brand', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Samsung", "LG", "Sony", "Philips", "Xiaomi", "TCL", "Hisense"])],
                ['name' => 'Диагональ экрана, дюймы', 'key' => 'screen_size', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["32", "40", "43", "50", "55", "65", "75", "85"])],
                ['name' => 'Разрешение', 'key' => 'resolution', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["HD", "Full HD", "4K Ultra HD", "8K"])],
                ['name' => 'Технология', 'key' => 'technology', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["LED", "QLED", "OLED", "NanoCell"])],
                ['name' => 'Smart TV', 'key' => 'smart_tv', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Да", "Нет"])],
            ],
            // --- 📷 PHOTO EQUIPMENT ---
            'photo-equipment' => [
                ['name' => 'Тип', 'key' => 'photo_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Фотоаппарат", "Объектив", "Видеокамера", "Экшн-камера", "Дрон"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у"])],
                ['name' => 'Марка', 'key' => 'brand', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Canon", "Nikon", "Sony", "Fujifilm", "Olympus", "Panasonic", "GoPro", "DJI"])],
                ['name' => 'Модель', 'key' => 'model', 'type' => 'text', 'is_required' => false, 'options' => null],
            ],
            // --- 🎧 AUDIO EQUIPMENT ---
            'audio-equipment' => [
                ['name' => 'Тип', 'key' => 'audio_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Наушники", "Колонки", "Саундбар", "Усилитель", "Акустическая система"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у"])],
                ['name' => 'Марка', 'key' => 'brand', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Apple", "Sony", "JBL", "Bose", "Samsung", "Xiaomi", "Sennheiser", "Marshall"])],
                ['name' => 'Модель', 'key' => 'model', 'type' => 'text', 'is_required' => false, 'options' => null],
                ['name' => 'Тип подключения', 'key' => 'connection', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Bluetooth", "Проводное", "Wi-Fi"])],
            ],
            // --- 🖥️ DESKTOP COMPUTERS ---
            'desktop-computers' => [
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у"])],
                ['name' => 'Тип', 'key' => 'pc_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Готовый ПК", "Системный блок", "Моноблок"])],
                ['name' => 'Процессор', 'key' => 'cpu', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Intel Core i3", "Intel Core i5", "Intel Core i7", "Intel Core i9", "AMD Ryzen 3", "AMD Ryzen 5", "AMD Ryzen 7", "AMD Ryzen 9"])],
                ['name' => 'ОЗУ, ГБ', 'key' => 'ram', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["8", "16", "32", "64", "128"])],
                ['name' => 'Тип накопителя', 'key' => 'storage_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["SSD", "HDD", "SSD + HDD"])],
                ['name' => 'Объем накопителя, ГБ', 'key' => 'storage_size', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["256", "512", "1000", "2000", "4000"])],
                ['name' => 'Видеокарта', 'key' => 'gpu', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Встроенная", "NVIDIA GeForce", "AMD Radeon"])],
            ],
            // --- 🎮 GAME CONSOLES ---
            'game-consoles' => [
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у"])],
                ['name' => 'Тип', 'key' => 'console_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["PlayStation 5", "PlayStation 4", "Xbox Series X/S", "Xbox One", "Nintendo Switch"])],
                ['name' => 'Память, ГБ', 'key' => 'storage', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["500", "825", "1000", "2000"])],
                ['name' => 'Комплектация', 'key' => 'package', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Полная", "Без коробки", "Только консоль"])],
            ],
            // --- 👕 CLOTHING ---
            'clothing' => [
                ['name' => 'Тип одежды', 'key' => 'clothing_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Верхняя одежда", "Костюмы", "Джинсы", "Брюки", "Платья", "Юбки", "Футболки", "Рубашки", "Свитеры", "Нижнее белье"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новая", "Б/у", "Отличное"])],
                ['name' => 'Пол', 'key' => 'gender', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Мужской", "Женский", "Унисекс"])],
                ['name' => 'Размер', 'key' => 'size', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["XS", "S", "M", "L", "XL", "XXL", "XXXL", "42", "44", "46", "48", "50", "52", "54", "56", "58"])],
                ['name' => 'Марка', 'key' => 'brand', 'type' => 'text', 'is_required' => false, 'options' => null],
                ['name' => 'Материал', 'key' => 'material', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Хлопок", "Шерсть", "Шелк", "Синтетика", "Лен"])],
                ['name' => 'Сезон', 'key' => 'season', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Лето", "Зима", "Всесезонная"])],
            ],
            // --- 👟 SHOES ---
            'shoes' => [
                ['name' => 'Тип обуви', 'key' => 'shoes_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Кроссовки", "Ботинки", "Туфли", "Сандалии", "Тапочки"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новая", "Б/у", "Отличное"])],
                ['name' => 'Пол', 'key' => 'gender', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Мужской", "Женский", "Унисекс"])],
                ['name' => 'Размер', 'key' => 'size', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["35", "36", "37", "38", "39", "40", "41", "42", "43", "44", "45", "46", "47", "48"])],
                ['name' => 'Марка', 'key' => 'brand', 'type' => 'text', 'is_required' => false, 'options' => null],
                ['name' => 'Материал', 'key' => 'material', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Кожа", "Замша", "Текстиль", "Синтетика"])],
                ['name' => 'Сезон', 'key' => 'season', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Лето", "Зима", "Всесезонная"])],
            ],
            // --- 💍 ACCESSORIES ---
            'accessories' => [
                ['name' => 'Тип аксессуара', 'key' => 'accessory_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Сумки", "Рюкзаки", "Кошельки", "Ремни", "Очки", "Украшения", "Шарфы", "Перчатки"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у"])],
                ['name' => 'Марка', 'key' => 'brand', 'type' => 'text', 'is_required' => false, 'options' => null],
            ],
            // --- ⌚ WATCHES ---
            'watches' => [
                ['name' => 'Тип часов', 'key' => 'watch_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Наручные", "Смарт-часы", "Карманные"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у"])],
                ['name' => 'Пол', 'key' => 'gender', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Мужские", "Женские", "Унисекс"])],
                ['name' => 'Марка', 'key' => 'brand', 'type' => 'text', 'is_required' => false, 'options' => null],
            ],
            // --- 🛋️ FURNITURE ---
            'furniture' => [
                ['name' => 'Тип мебели', 'key' => 'furniture_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Диваны", "Кровати", "Шкафы", "Столы", "Стулья", "Кресла", "Комоды", "Серванты"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новая", "Б/у"])],
                ['name' => 'Материал', 'key' => 'material', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Дерево", "ДСП", "МДФ", "Металл", "Пластик", "Стекло", "Ткань", "Кожа"])],
            ],
            // --- 🏠 HOME APPLIANCES ---
            'home-appliances' => [
                ['name' => 'Тип бытовой техники', 'key' => 'appliance_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Холодильники", "Стиральные машины", "Посудомоечные машины", "Пылесосы", "Кондиционеры", "Обогреватели", "Утюги"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новая", "Б/у"])],
                ['name' => 'Марка', 'key' => 'brand', 'type' => 'text', 'is_required' => false, 'options' => null],
                ['name' => 'Гарантия', 'key' => 'warranty', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Есть", "Нет"])],
            ],
            // --- 🍳 KITCHEN APPLIANCES ---
            'kitchen-appliances' => [
                ['name' => 'Тип кухонной техники', 'key' => 'kitchen_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Микроволновые печи", "Кофеварки", "Мультиварки", "Блендеры", "Миксеры", "Чайники", "Тостеры"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новая", "Б/у"])],
                ['name' => 'Марка', 'key' => 'brand', 'type' => 'text', 'is_required' => false, 'options' => null],
            ],
            // --- 🔨 REPAIR & CONSTRUCTION ---
            'repair-and-construction' => [
                ['name' => 'Тип для ремонта', 'key' => 'repair_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Электроинструмент", "Ручной инструмент", "Стройматериалы", "Сантехника", "Электрика"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у"])],
            ],
            // --- 🔧 TOOLS ---
            'tools' => [
                ['name' => 'Тип инструмента', 'key' => 'tool_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Дрели", "Шуруповерты", "Пилы", "Шлифовальные машины", "Наборы бит", "Наборы инструментов"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у"])],
                ['name' => 'Марка', 'key' => 'brand', 'type' => 'text', 'is_required' => false, 'options' => null],
            ],
            // --- 🌱 GARDEN & OUTDOORS ---
            'garden-and-outdoors' => [
                ['name' => 'Тип для сада', 'key' => 'garden_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Газонокосилки", "Триммеры", "Культиваторы", "Мотоблоки", "Насосы", "Инструменты", "Растения", "Семена"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у"])],
            ],
            // --- 📚 BOOKS ---
            'books' => [
                ['name' => 'Жанр', 'key' => 'genre', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Художественная литература", "Детективы", "Фантастика", "Учебники", "Детские книги", "Комиксы", "Бизнес"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новая", "Б/у", "Отличное"])],
                ['name' => 'Автор', 'key' => 'author', 'type' => 'text', 'is_required' => false, 'options' => null],
            ],
            // --- ⚽ SPORTS & LEISURE ---
            'sports-and-leisure' => [
                ['name' => 'Тип товара', 'key' => 'sport_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Тренажеры", "Велосипеды", "Самокаты", "Лыжи", "Сноуборды", "Ролики", "Спортивная одежда", "Спортивное питание"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у"])],
            ],
            // --- 🎨 HOBBIES & CRAFTS ---
            'hobbies-and-crafts' => [
                ['name' => 'Тип для хобби', 'key' => 'hobby_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Товары для творчества", "Рукоделие", "Коллекционирование", "Моделирование", "Рыбалка", "Охота"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у"])],
            ],
            // --- 🎸 MUSICAL INSTRUMENTS ---
            'musical-instruments' => [
                ['name' => 'Тип инструмента', 'key' => 'instrument_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Гитары", "Клавишные", "Духовые", "Ударные", "Скрипки", "DJ оборудование"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у"])],
                ['name' => 'Марка', 'key' => 'brand', 'type' => 'text', 'is_required' => false, 'options' => null],
            ],
            // --- 🎲 BOARD GAMES ---
            'board-games' => [
                ['name' => 'Тип игры', 'key' => 'game_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Стратегии", "Карточные", "Семейные", "Детские", "Логические", "Ролевые"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новая", "Б/у"])],
                ['name' => 'Возраст', 'key' => 'age', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["3+", "6+", "8+", "10+", "12+", "14+", "16+", "18+"])],
            ],
            // --- 👶 KIDS PRODUCTS ---
            'kids-products' => [
                ['name' => 'Тип товара', 'key' => 'kids_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Коляски", "Автокресла", "Кроватки", "Стульчики для кормления", "Одежда", "Обувь", "Манежи"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у"])],
                ['name' => 'Возраст', 'key' => 'age', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["0-6 мес", "6-12 мес", "1-3 года", "3-7 лет", "7+ лет"])],
            ],
            // --- 🧸 TOYS ---
            'toys' => [
                ['name' => 'Тип игрушки', 'key' => 'toy_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Мягкие игрушки", "Конструкторы", "Куклы", "Машинки", "Развивающие", "Радиоуправляемые", "Интерактивные"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новая", "Б/у"])],
                ['name' => 'Возраст', 'key' => 'age', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["0+", "1+", "3+", "5+", "7+", "10+"])],
            ],
            // --- 🐕 ANIMALS ---
            'animals' => [
                ['name' => 'Категория', 'key' => 'animal_category', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Собаки", "Кошки", "Птицы", "Аквариум", "Грызуны", "Другие животные", "Товары для животных"])],
                ['name' => 'Порода', 'key' => 'breed', 'type' => 'text', 'is_required' => false, 'options' => null],
                ['name' => 'Возраст', 'key' => 'age', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["До 1 года", "1-3 года", "3-5 лет", "5+ лет"])],
                ['name' => 'Пол', 'key' => 'gender', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Самец", "Самка"])],
            ],
            // --- 🍞 FOOD & BEVERAGES ---
            'food-and-beverages' => [
                ['name' => 'Категория продукта', 'key' => 'product_category', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Мясо", "Рыба", "Молочные продукты", "Овощи", "Фрукты", "Напитки", "Хлебобулочные", "Сладости", "Готовые блюда"])],
                ['name' => 'Срок годности', 'key' => 'expiry_date', 'type' => 'text', 'is_required' => false, 'options' => null],
            ],
            // --- 💼 JOBS ---
            'jobs' => [
                ['name' => 'Сфера деятельности', 'key' => 'job_sphere', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["IT", "Продажи", "Маркетинг", "Бухгалтерия", "Строительство", "Медицина", "Образование", "Транспорт", "Производство", "Общепит", "Красота", "Охрана"])],
                ['name' => 'График работы', 'key' => 'schedule', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Полный день", "Сменный график", "Гибкий график", "Удаленная работа", "Вахтовый метод", "Неполный день"])],
                ['name' => 'Опыт работы', 'key' => 'experience', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Не требуется", "1-3 года", "3-5 лет", "Более 5 лет"])],
                ['name' => 'Зарплата от', 'key' => 'salary_from', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Зарплата до', 'key' => 'salary_to', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Тип занятости', 'key' => 'employment_type', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Полная занятость", "Частичная занятость", "Проектная работа", "Стажировка", "Волонтерство"])],
            ],
            // --- 📄 RESUMES ---
            'resumes' => [
                ['name' => 'Сфера деятельности', 'key' => 'job_sphere', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["IT", "Продажи", "Маркетинг", "Бухгалтерия", "Строительство", "Медицина", "Образование", "Транспорт", "Производство", "Общепит", "Красота", "Охрана"])],
                ['name' => 'Желаемая должность', 'key' => 'position', 'type' => 'text', 'is_required' => true, 'options' => null],
                ['name' => 'Опыт работы', 'key' => 'experience', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Без опыта", "Меньше года", "1-3 года", "3-5 лет", "Более 5 лет"])],
                ['name' => 'График работы', 'key' => 'schedule', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Полный день", "Сменный график", "Гибкий график", "Удаленная работа", "Вахтовый метод", "Неполный день"])],
                ['name' => 'Желаемая зарплата', 'key' => 'salary', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Образование', 'key' => 'education', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Среднее", "Среднее специальное", "Неполное высшее", "Высшее", "Два и более высших", "Ученая степень"])],
            ],
            // --- 🛠️ SERVICES ---
            'services' => [
                ['name' => 'Категория услуги', 'key' => 'service_category', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Ремонт и строительство", "Грузоперевозки", "Юридические", "Бухгалтерские", "IT услуги", "Репетиторство", "Уборка", "Мероприятия", "Фото и видео", "Дизайн", "Пассажирские перевозки"])],
                ['name' => 'Тип услуги', 'key' => 'service_type', 'type' => 'text', 'is_required' => true, 'options' => null],
                ['name' => 'Тип цены', 'key' => 'price_type', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Договорная", "Фиксированная", "За час", "За день", "За проект"])],
            ],
            // --- 💅 BEAUTY & HEALTH ---
            'beauty-and-health' => [
                ['name' => 'Категория', 'key' => 'beauty_category', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Косметика", "Парфюмерия", "Средства по уходу", "Биологические добавки", "Медтехника", "Массажеры", "Ортопедия"])],
                ['name' => 'Состояние', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Новый", "Б/у", "В запечатанной упаковке"])],
                ['name' => 'Марка', 'key' => 'brand', 'type' => 'text', 'is_required' => false, 'options' => null],
            ],
        ];

        $uniqueFields = [];
        foreach ($fieldsBySlug as $categoryFields) {
            foreach ($categoryFields as $field) {
                // Используем 'key' поля как ключ массива, чтобы автоматически убрать дубликаты
                $uniqueFields[$field['key']] = $field;
            }
        }

        // 4. Вставляем уникальные поля в базу данных
        // Функция array_values() преобразует ассоциативный массив в простой, который нужен для insert
        if (!empty($uniqueFields)) {
            CategoryField::insert(array_values($uniqueFields));
        }

        // 5. Теперь, когда все поля созданы, привязываем их к категориям
        $categories = Category::whereIn('slug', array_keys($fieldsBySlug))->get()->keyBy('slug');
        $createdFields = CategoryField::all()->keyBy('key');

        foreach ($fieldsBySlug as $slug => $fieldsData) {
            if (isset($categories[$slug])) {
                $category = $categories[$slug];
                $idsToAttach = [];
                foreach ($fieldsData as $fieldData) {
                    if (isset($createdFields[$fieldData['key']])) {
                        $idsToAttach[] = $createdFields[$fieldData['key']]->id;
                    }
                }
                // Привязываем все поля к категории за один раз
                if (!empty($idsToAttach)) {
                    $category->fields()->sync($idsToAttach);
                }
            }
        }
    }
}
