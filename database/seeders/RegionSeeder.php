<?php
namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        // Для SQLite
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('regions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Создаём страну
        $country = Region::create([
            'name' => 'Армения',
            'slug' => 'armenia',
            'type' => 'country'
        ]);

        // Создаём города
        Region::create(['parent_id' => $country->id, 'name' => 'Երևան', 'slug' => 'yerevan', 'type' => 'city']);
        Region::create(['parent_id' => $country->id, 'name' => 'Գյումրի', 'slug' => 'gyumri', 'type' => 'city']);
        Region::create(['parent_id' => $country->id, 'name' => 'Վանաձոր', 'slug' => 'vanadzor', 'type' => 'city']);
        Region::create(['parent_id' => $country->id, 'name' => 'Արմավիր', 'slug' => 'armavir', 'type' => 'city']);
        Region::create(['parent_id' => $country->id, 'name' => 'Կոտայք', 'slug' => 'kotayk', 'type' => 'city']);
    }
}
