<?php
namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionSeeder extends Seeder
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

        DB::table('regions')->truncate();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }

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
