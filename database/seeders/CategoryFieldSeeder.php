<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryField;
use Illuminate\Database\Seeder;

class CategoryFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear the table before seeding
        CategoryField::query()->delete();

        // Step 1: Define all fields for all categories in a single, readable structure.
        // The array keys are now English slugs.
        $fieldsBySlug = [
            // --- 🚗 CARS ---
            'cars' => [
                ['name' => 'Brand', 'key' => 'brand', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Toyota", "BMW", "Mercedes-Benz", "Audi", "Volkswagen", "Hyundai", "Kia", "Nissan", "Honda", "Mazda", "Lexus", "Ford", "Chevrolet", "Mitsubishi", "Subaru", "Skoda", "Renault", "Peugeot", "Opel", "Volvo", "Porsche", "Lada (VAZ)", "UAZ", "GAZ"])],
                ['name' => 'Model', 'key' => 'model', 'type' => 'text', 'is_required' => true, 'options' => null],
                ['name' => 'Generation', 'key' => 'generation', 'type' => 'text', 'is_required' => false, 'options' => null],
                ['name' => 'Year', 'key' => 'year', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Mileage, km', 'key' => 'mileage', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used", "Damaged"])],
                ['name' => 'Customs cleared', 'key' => 'customs_cleared', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Yes", "No"])],
                ['name' => 'Body type', 'key' => 'body_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Sedan", "SUV", "Coupe", "Hatchback", "Station Wagon", "Convertible", "Minivan", "Liftback", "Pickup", "Limousine", "Van", "Minibus"])],
                ['name' => 'Color', 'key' => 'color', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["White", "Black", "Silver", "Gray", "Red", "Blue", "Brown", "Green", "Yellow", "Orange", "Gold", "Beige", "Purple", "Light Blue", "Pink"])],
                ['name' => 'Engine type', 'key' => 'engine_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Gasoline", "Diesel", "Hybrid", "Electric", "LPG", "LPG/Gasoline"])],
                ['name' => 'Engine volume, L', 'key' => 'engine_volume', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Engine power, hp', 'key' => 'engine_power', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Transmission', 'key' => 'transmission', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Manual", "Automatic", "Robotic", "CVT"])],
                ['name' => 'Drive type', 'key' => 'drive_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Front-wheel drive", "Rear-wheel drive", "All-wheel drive"])],
                ['name' => 'Steering wheel', 'key' => 'steering_wheel', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Left", "Right"])],
                ['name' => 'Number of owners', 'key' => 'owners', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["1", "2", "3 or more"])],
                ['name' => 'Vehicle passport', 'key' => 'pts', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Original", "Duplicate", "Electronic"])],
                ['name' => 'VIN', 'key' => 'vin', 'type' => 'text', 'is_required' => false, 'options' => null],
                ['name' => 'Exchange', 'key' => 'exchange', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Available", "Not interested"])],
            ],
            // --- 🏍️ MOTORCYCLES ---
            'motorcycles' => [
                ['name' => 'Type', 'key' => 'moto_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Motorcycle", "Scooter", "Moped", "ATV", "Snowmobile", "Jet ski"])],
                ['name' => 'Brand', 'key' => 'brand', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Honda", "Yamaha", "Suzuki", "Kawasaki", "Harley-Davidson", "BMW", "Ducati", "KTM", "Aprilia", "Triumph", "Ural", "IZH"])],
                ['name' => 'Model', 'key' => 'model', 'type' => 'text', 'is_required' => true, 'options' => null],
                ['name' => 'Year', 'key' => 'year', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Mileage, km', 'key' => 'mileage', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Engine volume, cm³', 'key' => 'engine_volume', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Engine type', 'key' => 'engine_type', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["2-stroke", "4-stroke", "Electric"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used", "Needs repair"])],
            ],
            // --- 🚚 TRUCKS ---
            'trucks' => [
                ['name' => 'Type', 'key' => 'truck_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Van", "Tented", "Flatbed", "Dump truck", "Refrigerator", "Tow truck", "Crane truck", "Tanker"])],
                ['name' => 'Brand', 'key' => 'brand', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["GAZ", "KamAZ", "MAZ", "ZIL", "UAZ", "Mercedes-Benz", "Volvo", "Scania", "MAN", "Isuzu", "Hyundai"])],
                ['name' => 'Model', 'key' => 'model', 'type' => 'text', 'is_required' => true, 'options' => null],
                ['name' => 'Year', 'key' => 'year', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Mileage, km', 'key' => 'mileage', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Load capacity, t', 'key' => 'capacity', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used", "Needs repair"])],
            ],
            // --- 🏠 APARTMENT SALE ---
            'apartments-sale' => [
                ['name' => 'Number of rooms', 'key' => 'rooms', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Studio", "1", "2", "3", "4", "5", "6+", "Open plan"])],
                ['name' => 'Property type', 'key' => 'property_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Resale", "New building"])],
                ['name' => 'Total area, m²', 'key' => 'total_area', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Living area, m²', 'key' => 'living_area', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Kitchen area, m²', 'key' => 'kitchen_area', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Floor', 'key' => 'floor', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Total floors', 'key' => 'total_floors', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Building type', 'key' => 'building_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Brick", "Panel", "Monolithic", "Block", "Wooden", "Stalinka"])],
                ['name' => 'Bathroom', 'key' => 'bathroom', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Combined", "Separate", "2 or more"])],
                ['name' => 'Balcony/loggia', 'key' => 'balcony', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Balcony", "Loggia", "Multiple", "None"])],
                ['name' => 'Renovation', 'key' => 'renovation', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["No renovation", "Cosmetic", "Euro-renovation", "Designer"])],
                ['name' => 'Parking', 'key' => 'parking', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Surface", "Underground", "Multi-level", "None"])],
            ],
            // --- 🏠 APARTMENT RENT ---
            'apartments-rent' => [
                ['name' => 'Number of rooms', 'key' => 'rooms', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Studio", "1", "2", "3", "4", "5+"])],
                ['name' => 'Rental period', 'key' => 'rent_period', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Long-term", "Daily", "For several months"])],
                ['name' => 'Total area, m²', 'key' => 'total_area', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Floor', 'key' => 'floor', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Total floors', 'key' => 'total_floors', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Furniture', 'key' => 'furniture', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Available", "None", "Partial"])],
                ['name' => 'Appliances', 'key' => 'appliances', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Available", "None", "Partial"])],
                ['name' => 'Pets allowed', 'key' => 'pets_allowed', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Allowed", "Not allowed"])],
                ['name' => 'Children allowed', 'key' => 'children_allowed', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Allowed", "Not allowed"])],
                ['name' => 'Utility bills', 'key' => 'utilities', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Included", "Not included"])],
                ['name' => 'Deposit', 'key' => 'deposit', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["None", "Required"])],
            ],
            // --- 🏡 HOUSES FOR SALE ---
            'houses-sale' => [
                ['name' => 'House type', 'key' => 'house_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["House", "Dacha", "Cottage", "Townhouse", "Part of a house"])],
                ['name' => 'House area, m²', 'key' => 'house_area', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Land area, acres', 'key' => 'land_area', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Number of rooms', 'key' => 'rooms', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Floors', 'key' => 'floors', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Wall material', 'key' => 'wall_material', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Brick", "Wood", "Blocks", "Panels", "Frame", "Monolithic"])],
                ['name' => 'Heating', 'key' => 'heating', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Gas", "Electric", "Solid fuel", "No heating"])],
                ['name' => 'Water supply', 'key' => 'water', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Central", "Well", "Water pump", "None"])],
                ['name' => 'Sewerage', 'key' => 'sewerage', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Central", "Septic tank", "Cesspool", "None"])],
                ['name' => 'Electricity', 'key' => 'electricity', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Available", "None", "Nearby"])],
            ],
            // --- 🌳 LAND PLOTS ---
            'land-plots' => [
                ['name' => 'Land purpose', 'key' => 'land_purpose', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Individual housing construction", "Gardening", "Farming", "Commercial"])],
                ['name' => 'Area, acres', 'key' => 'area', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Electricity', 'key' => 'electricity', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Available", "None", "Nearby", "Can be connected"])],
                ['name' => 'Water supply', 'key' => 'water', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Available", "None", "Nearby", "Well", "Water pump"])],
                ['name' => 'Gas', 'key' => 'gas', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Available", "None", "Nearby", "Can be connected"])],
                ['name' => 'Road', 'key' => 'road', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Asphalt", "Dirt road", "No road"])],
            ],
            // --- 🏢 COMMERCIAL REAL ESTATE ---
            'commercial-real-estate' => [
                ['name' => 'Type', 'key' => 'commercial_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Office", "Retail space", "Warehouse", "Industrial", "Public catering", "Garage", "Commercial land"])],
                ['name' => 'Transaction', 'key' => 'operation', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Sale", "Rent"])],
                ['name' => 'Area, m²', 'key' => 'area', 'type' => 'number', 'is_required' => true, 'options' => null],
                ['name' => 'Floor', 'key' => 'floor', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Parking', 'key' => 'parking', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Available", "None"])],
                ['name' => 'Separate entrance', 'key' => 'separate_entrance', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Available", "None"])],
            ],
            // --- 💻 LAPTOPS ---
            'laptops' => [
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used", "Refurbished"])],
                ['name' => 'Brand', 'key' => 'brand', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Apple", "Asus", "Acer", "Dell", "HP", "Lenovo", "MSI", "Huawei", "Samsung", "Xiaomi", "Microsoft"])],
                ['name' => 'Model', 'key' => 'model', 'type' => 'text', 'is_required' => false, 'options' => null],
                ['name' => 'Screen size, inches', 'key' => 'screen_size', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["13", "13.3", "14", "15", "15.6", "16", "17", "17.3"])],
                ['name' => 'Processor', 'key' => 'cpu', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Intel Core i3", "Intel Core i5", "Intel Core i7", "Intel Core i9", "AMD Ryzen 3", "AMD Ryzen 5", "AMD Ryzen 7", "AMD Ryzen 9", "Apple M1", "Apple M2", "Apple M3"])],
                ['name' => 'RAM, GB', 'key' => 'ram', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["4", "8", "16", "32", "64"])],
                ['name' => 'Storage type', 'key' => 'storage_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["SSD", "HDD", "SSD + HDD"])],
                ['name' => 'Storage size, GB', 'key' => 'storage_size', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["128", "256", "512", "1000", "2000"])],
                ['name' => 'Graphics card', 'key' => 'gpu', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Integrated", "NVIDIA", "AMD"])],
                ['name' => 'Operating System', 'key' => 'os', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Windows 11", "Windows 10", "macOS", "Linux", "No OS"])],
            ],
            // --- 📱 PHONES ---
            'phones' => [
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used", "Refurbished"])],
                ['name' => 'Brand', 'key' => 'brand', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Apple", "Samsung", "Xiaomi", "Huawei", "Honor", "Realme", "Oppo", "Vivo", "OnePlus", "Google", "Nokia", "Motorola"])],
                ['name' => 'Model', 'key' => 'model', 'type' => 'text', 'is_required' => true, 'options' => null],
                ['name' => 'Storage, GB', 'key' => 'storage', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["32", "64", "128", "256", "512", "1024"])],
                ['name' => 'RAM, GB', 'key' => 'ram', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["2", "3", "4", "6", "8", "12", "16"])],
                ['name' => 'Color', 'key' => 'color', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Black", "White", "Silver", "Gold", "Blue", "Red", "Green", "Other"])],
                ['name' => 'Package contents', 'key' => 'package', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Full", "Without box", "Phone only"])],
                ['name' => 'Warranty', 'key' => 'warranty', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Yes", "No"])],
            ],
            // --- 📱 TABLETS ---
            'tablets' => [
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
                ['name' => 'Brand', 'key' => 'brand', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Apple", "Samsung", "Xiaomi", "Huawei", "Lenovo", "Amazon", "Microsoft"])],
                ['name' => 'Model', 'key' => 'model', 'type' => 'text', 'is_required' => false, 'options' => null],
                ['name' => 'Screen size, inches', 'key' => 'screen_size', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["7", "8", "9", "10", "11", "12", "13"])],
                ['name' => 'Storage, GB', 'key' => 'storage', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["32", "64", "128", "256", "512", "1024"])],
                ['name' => 'Operating System', 'key' => 'os', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["iOS", "iPadOS", "Android", "Windows"])],
                ['name' => 'Connectivity', 'key' => 'connectivity', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Wi-Fi", "Wi-Fi + Cellular"])],
            ],
            // --- 📺 TVS ---
            'tvs' => [
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
                ['name' => 'Brand', 'key' => 'brand', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Samsung", "LG", "Sony", "Philips", "Xiaomi", "TCL", "Hisense"])],
                ['name' => 'Screen size, inches', 'key' => 'screen_size', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["32", "40", "43", "50", "55", "65", "75", "85"])],
                ['name' => 'Resolution', 'key' => 'resolution', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["HD", "Full HD", "4K Ultra HD", "8K"])],
                ['name' => 'Technology', 'key' => 'technology', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["LED", "QLED", "OLED", "NanoCell"])],
                ['name' => 'Smart TV', 'key' => 'smart_tv', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Yes", "No"])],
            ],
            // --- 📷 PHOTO EQUIPMENT ---
            'photo-equipment' => [
                ['name' => 'Type', 'key' => 'photo_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Camera", "Lens", "Video Camera", "Action Camera", "Drone"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
                ['name' => 'Brand', 'key' => 'brand', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Canon", "Nikon", "Sony", "Fujifilm", "Olympus", "Panasonic", "GoPro", "DJI"])],
                ['name' => 'Model', 'key' => 'model', 'type' => 'text', 'is_required' => false, 'options' => null],
            ],
            // --- 🎧 AUDIO EQUIPMENT ---
            'audio-equipment' => [
                ['name' => 'Type', 'key' => 'audio_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Headphones", "Speakers", "Soundbar", "Amplifier", "Acoustic System"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
                ['name' => 'Brand', 'key' => 'brand', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Apple", "Sony", "JBL", "Bose", "Samsung", "Xiaomi", "Sennheiser", "Marshall"])],
                ['name' => 'Model', 'key' => 'model', 'type' => 'text', 'is_required' => false, 'options' => null],
                ['name' => 'Connection type', 'key' => 'connection', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Bluetooth", "Wired", "Wi-Fi"])],
            ],
            // --- 🖥️ DESKTOP COMPUTERS ---
            'desktop-computers' => [
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
                ['name' => 'Type', 'key' => 'pc_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Pre-built", "System unit", "All-in-one"])],
                ['name' => 'Processor', 'key' => 'cpu', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Intel Core i3", "Intel Core i5", "Intel Core i7", "Intel Core i9", "AMD Ryzen 3", "AMD Ryzen 5", "AMD Ryzen 7", "AMD Ryzen 9"])],
                ['name' => 'RAM, GB', 'key' => 'ram', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["8", "16", "32", "64", "128"])],
                ['name' => 'Storage type', 'key' => 'storage_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["SSD", "HDD", "SSD + HDD"])],
                ['name' => 'Storage size, GB', 'key' => 'storage_size', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["256", "512", "1000", "2000", "4000"])],
                ['name' => 'Graphics card', 'key' => 'gpu', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Integrated", "NVIDIA GeForce", "AMD Radeon"])],
            ],
            // --- 🎮 GAME CONSOLES ---
            'game-consoles' => [
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
                ['name' => 'Type', 'key' => 'console_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["PlayStation 5", "PlayStation 4", "Xbox Series X/S", "Xbox One", "Nintendo Switch"])],
                ['name' => 'Storage, GB', 'key' => 'storage', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["500", "825", "1000", "2000"])],
                ['name' => 'Package contents', 'key' => 'package', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Full", "Without box", "Console only"])],
            ],
            // --- 👕 CLOTHING ---
            'clothing' => [
                ['name' => 'Type', 'key' => 'clothing_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Outerwear", "Suits", "Jeans", "Trousers", "Dresses", "Skirts", "T-shirts", "Shirts", "Sweaters", "Underwear"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used", "Excellent"])],
                ['name' => 'Gender', 'key' => 'gender', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Male", "Female", "Unisex"])],
                ['name' => 'Size', 'key' => 'size', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["XS", "S", "M", "L", "XL", "XXL", "XXXL", "42", "44", "46", "48", "50", "52", "54", "56", "58"])],
                ['name' => 'Brand', 'key' => 'brand', 'type' => 'text', 'is_required' => false, 'options' => null],
                ['name' => 'Material', 'key' => 'material', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Cotton", "Wool", "Silk", "Synthetics", "Linen"])],
                ['name' => 'Season', 'key' => 'season', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Summer", "Winter", "All-season"])],
            ],
            // --- 👟 SHOES ---
            'shoes' => [
                ['name' => 'Type', 'key' => 'shoes_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Sneakers", "Boots", "Shoes", "Sandals", "Slippers"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used", "Excellent"])],
                ['name' => 'Gender', 'key' => 'gender', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Male", "Female", "Unisex"])],
                ['name' => 'Size', 'key' => 'size', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["35", "36", "37", "38", "39", "40", "41", "42", "43", "44", "45", "46", "47", "48"])],
                ['name' => 'Brand', 'key' => 'brand', 'type' => 'text', 'is_required' => false, 'options' => null],
                ['name' => 'Material', 'key' => 'material', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Leather", "Suede", "Textile", "Synthetic"])],
                ['name' => 'Season', 'key' => 'season', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Summer", "Winter", "All-season"])],
            ],
            // --- 💍 ACCESSORIES ---
            'accessories' => [
                ['name' => 'Type', 'key' => 'accessory_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Bags", "Backpacks", "Wallets", "Belts", "Glasses", "Jewelry", "Scarves", "Gloves"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
                ['name' => 'Brand', 'key' => 'brand', 'type' => 'text', 'is_required' => false, 'options' => null],
            ],
            // --- ⌚ WATCHES ---
            'watches' => [
                ['name' => 'Type', 'key' => 'watch_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Wristwatch", "Smartwatch", "Pocket watch"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
                ['name' => 'Gender', 'key' => 'gender', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Men's", "Women's", "Unisex"])],
                ['name' => 'Brand', 'key' => 'brand', 'type' => 'text', 'is_required' => false, 'options' => null],
            ],
            // --- 🛋️ FURNITURE ---
            'furniture' => [
                ['name' => 'Type', 'key' => 'furniture_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Sofas", "Beds", "Wardrobes", "Tables", "Chairs", "Armchairs", "Dressers", "Sideboards"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
                ['name' => 'Material', 'key' => 'material', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Wood", "Chipboard", "MDF", "Metal", "Plastic", "Glass", "Fabric", "Leather"])],
            ],
            // --- 🏠 HOME APPLIANCES ---
            'home-appliances' => [
                ['name' => 'Type', 'key' => 'appliance_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Refrigerators", "Washing machines", "Dishwashers", "Vacuum cleaners", "Air conditioners", "Heaters", "Irons"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
                ['name' => 'Brand', 'key' => 'brand', 'type' => 'text', 'is_required' => false, 'options' => null],
                ['name' => 'Warranty', 'key' => 'warranty', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Yes", "No"])],
            ],
            // --- 🍳 KITCHEN APPLIANCES ---
            'kitchen-appliances' => [
                ['name' => 'Type', 'key' => 'kitchen_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Microwaves", "Coffee makers", "Multicookers", "Blenders", "Mixers", "Kettles", "Toasters"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
                ['name' => 'Brand', 'key' => 'brand', 'type' => 'text', 'is_required' => false, 'options' => null],
            ],
            // --- 🔨 REPAIR & CONSTRUCTION ---
            'repair-and-construction' => [
                ['name' => 'Type', 'key' => 'repair_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Power tools", "Hand tools", "Materials", "Plumbing", "Electrical"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
            ],
            // --- 🔧 TOOLS ---
            'tools' => [
                ['name' => 'Type', 'key' => 'tool_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Drills", "Screwdrivers", "Saws", "Grinders", "Drill bits", "Tool kits"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
                ['name' => 'Brand', 'key' => 'brand', 'type' => 'text', 'is_required' => false, 'options' => null],
            ],
            // --- 🌱 GARDEN & OUTDOORS ---
            'garden-and-outdoors' => [
                ['name' => 'Type', 'key' => 'garden_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Lawnmowers", "Trimmers", "Cultivators", "Tillers", "Pumps", "Tools", "Plants", "Seeds"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
            ],
            // --- 📚 BOOKS ---
            'books' => [
                ['name' => 'Genre', 'key' => 'genre', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Fiction", "Detectives", "Science Fiction", "Textbooks", "Children's books", "Comics", "Business"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used", "Excellent"])],
                ['name' => 'Author', 'key' => 'author', 'type' => 'text', 'is_required' => false, 'options' => null],
            ],
            // --- ⚽ SPORTS & LEISURE ---
            'sports-and-leisure' => [
                ['name' => 'Type', 'key' => 'sport_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Exercise equipment", "Bicycles", "Scooters", "Skis", "Snowboards", "Roller skates", "Sportswear", "Sports nutrition"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
            ],
            // --- 🎨 HOBBIES & CRAFTS ---
            'hobbies-and-crafts' => [
                ['name' => 'Type', 'key' => 'hobby_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Art supplies", "Handicrafts", "Collecting", "Modeling", "Fishing", "Hunting"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
            ],
            // --- 🎸 MUSICAL INSTRUMENTS ---
            'musical-instruments' => [
                ['name' => 'Type', 'key' => 'instrument_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Guitars", "Keyboards", "Wind instruments", "Drums", "Violins", "DJ equipment"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
                ['name' => 'Brand', 'key' => 'brand', 'type' => 'text', 'is_required' => false, 'options' => null],
            ],
            // --- 🎲 BOARD GAMES ---
            'board-games' => [
                ['name' => 'Type', 'key' => 'game_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Strategy", "Card games", "Family", "Children's", "Logic", "Role-playing"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
                ['name' => 'Age', 'key' => 'age', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["3+", "6+", "8+", "10+", "12+", "14+", "16+", "18+"])],
            ],
            // --- 👶 KIDS PRODUCTS ---
            'kids-products' => [
                ['name' => 'Type', 'key' => 'kids_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Strollers", "Car seats", "Cribs", "High chairs", "Clothing", "Shoes", "Playpens"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
                ['name' => 'Age', 'key' => 'age', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["0-6 months", "6-12 months", "1-3 years", "3-7 years", "7+ years"])],
            ],
            // --- 🧸 TOYS ---
            'toys' => [
                ['name' => 'Type', 'key' => 'toy_type', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Soft toys", "Construction sets", "Dolls", "Cars", "Educational", "Radio-controlled", "Interactive"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used"])],
                ['name' => 'Age', 'key' => 'age', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["0+", "1+", "3+", "5+", "7+", "10+"])],
            ],
            // --- 🐕 ANIMALS ---
            'animals' => [
                ['name' => 'Category', 'key' => 'animal_category', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Dogs", "Cats", "Birds", "Aquarium", "Rodents", "Other animals", "Pet supplies"])],
                ['name' => 'Breed', 'key' => 'breed', 'type' => 'text', 'is_required' => false, 'options' => null],
                ['name' => 'Age', 'key' => 'age', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Under 1 year", "1-3 years", "3-5 years", "5+ years"])],
                ['name' => 'Gender', 'key' => 'gender', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Male", "Female"])],
            ],
            // --- 🍞 FOOD & BEVERAGES ---
            'food-and-beverages' => [
                ['name' => 'Category', 'key' => 'product_category', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Meat", "Fish", "Dairy products", "Vegetables", "Fruits", "Beverages", "Bakery", "Sweets", "Ready meals"])],
                ['name' => 'Expiration date', 'key' => 'expiry_date', 'type' => 'text', 'is_required' => false, 'options' => null],
            ],
            // --- 💼 JOBS ---
            'jobs' => [
                ['name' => 'Industry', 'key' => 'job_sphere', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["IT", "Sales", "Marketing", "Accounting", "Construction", "Medicine", "Education", "Transport", "Production", "Catering", "Beauty", "Security"])],
                ['name' => 'Work schedule', 'key' => 'schedule', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Full-time", "Shift work", "Flexible schedule", "Remote work", "Rotational work", "Part-time"])],
                ['name' => 'Work experience', 'key' => 'experience', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Not required", "1-3 years", "3-5 years", "More than 5 years"])],
                ['name' => 'Salary from', 'key' => 'salary_from', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Salary to', 'key' => 'salary_to', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Employment type', 'key' => 'employment_type', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Full-time", "Part-time", "Project work", "Internship", "Volunteering"])],
            ],
            // --- 📄 RESUMES ---
            'resumes' => [
                ['name' => 'Industry', 'key' => 'job_sphere', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["IT", "Sales", "Marketing", "Accounting", "Construction", "Medicine", "Education", "Transport", "Production", "Catering", "Beauty", "Security"])],
                ['name' => 'Desired position', 'key' => 'position', 'type' => 'text', 'is_required' => true, 'options' => null],
                ['name' => 'Work experience', 'key' => 'experience', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["No experience", "Less than a year", "1-3 years", "3-5 years", "More than 5 years"])],
                ['name' => 'Work schedule', 'key' => 'schedule', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Full-time", "Shift work", "Flexible schedule", "Remote work", "Rotational work", "Part-time"])],
                ['name' => 'Desired salary', 'key' => 'salary', 'type' => 'number', 'is_required' => false, 'options' => null],
                ['name' => 'Education', 'key' => 'education', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Secondary", "Vocational", "Incomplete higher", "Higher", "Multiple higher", "Academic degree"])],
            ],
            // --- 🛠️ SERVICES ---
            'services' => [
                ['name' => 'Service category', 'key' => 'service_category', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Repair and construction", "Freight", "Legal", "Accounting", "IT services", "Tutoring", "Cleaning", "Events", "Photo and video", "Design", "Passenger transport"])],
                ['name' => 'Service type', 'key' => 'service_type', 'type' => 'text', 'is_required' => true, 'options' => null],
                ['name' => 'Price type', 'key' => 'price_type', 'type' => 'select', 'is_required' => false, 'options' => json_encode(["Negotiable", "Fixed", "Per hour", "Per day", "Per project"])],
            ],
            // --- 💅 BEAUTY & HEALTH ---
            'beauty-and-health' => [
                ['name' => 'Category', 'key' => 'beauty_category', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["Cosmetics", "Perfumery", "Care products", "Supplements", "Medical devices", "Massagers", "Orthopedics"])],
                ['name' => 'Condition', 'key' => 'condition', 'type' => 'select', 'is_required' => true, 'options' => json_encode(["New", "Used", "Sealed"])],
                ['name' => 'Brand', 'key' => 'brand', 'type' => 'text', 'is_required' => false, 'options' => null],
            ],
        ];

        // Step 2: Get all categories in a single, optimized query.
        // This avoids the N+1 problem and is much more performant.
        $slugs = array_keys($fieldsBySlug);
        $categories = Category::whereIn('slug', $slugs)->get()->keyBy('slug');

        // Step 3: Loop through the data structure and insert fields for each category.
        foreach ($fieldsBySlug as $slug => $fields) {
            // Check if a category with this slug was found in the database
            if (isset($categories[$slug])) {
                $categoryId = $categories[$slug]->id;

                // Add the 'category_id' to each field definition before inserting
                $fieldsWithCategoryId = array_map(function ($field) use ($categoryId) {
                    $field['category_id'] = $categoryId;
                    return $field;
                }, $fields);

                // Perform a bulk insert for all fields of the current category
                CategoryField::insert($fieldsWithCategoryId);
            }
        }
    }
}
