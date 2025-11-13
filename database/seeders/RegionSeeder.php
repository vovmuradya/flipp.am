<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
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

        $country = Region::create([
            'name' => $this->localized('Հայաստան', 'Армения', 'Armenia'),
            'slug' => 'armenia',
            'type' => 'country',
        ]);

        Region::create([
            'parent_id' => $country->id,
            'name' => $this->localized('Երևան', 'Ереван', 'Yerevan'),
            'slug' => 'yerevan',
            'type' => 'city',
        ]);

        $provinces = [
            [
                'name' => $this->localized('Արագածոտն', 'Арагацотн', 'Aragatsotn'),
                'slug' => 'aragatsotn',
                'cities' => [
                    ['name' => $this->localized('Աշտարակ', 'Аштарак', 'Ashtarak'), 'slug' => 'ashtarak'],
                    ['name' => $this->localized('Ապարան', 'Апаран', 'Aparan'), 'slug' => 'aparan'],
                    ['name' => $this->localized('Թալին', 'Талин', 'Talin'), 'slug' => 'talin'],
                ],
            ],
            [
                'name' => $this->localized('Արարատ', 'Арарат', 'Ararat'),
                'slug' => 'ararat',
                'cities' => [
                    ['name' => $this->localized('Արտաշատ', 'Арташат', 'Artashat'), 'slug' => 'artashat'],
                    ['name' => $this->localized('Արարատ', 'Арарат', 'Ararat City'), 'slug' => 'ararat-city'],
                    ['name' => $this->localized('Մասիս', 'Масис', 'Masis'), 'slug' => 'masis'],
                    ['name' => $this->localized('Վեդի', 'Веди', 'Vedi'), 'slug' => 'vedi'],
                ],
            ],
            [
                'name' => $this->localized('Արմավիր', 'Армавир', 'Armavir'),
                'slug' => 'armavir',
                'cities' => [
                    ['name' => $this->localized('Արմավիր', 'Армавир', 'Armavir City'), 'slug' => 'armavir-city'],
                    ['name' => $this->localized('Էջմիածին', 'Эчмиадзин', 'Ejmiatsin'), 'slug' => 'ejmiatsin'],
                    ['name' => $this->localized('Մեծամոր', 'Мецамор', 'Metsamor'), 'slug' => 'metsamor'],
                ],
            ],
            [
                'name' => $this->localized('Գեղարքունիք', 'Гегаркуник', 'Gegharkunik'),
                'slug' => 'gegharkunik',
                'cities' => [
                    ['name' => $this->localized('Գավառ', 'Гавар', 'Gavar'), 'slug' => 'gavar'],
                    ['name' => $this->localized('Սևան', 'Севан', 'Sevan'), 'slug' => 'sevan'],
                    ['name' => $this->localized('Մարտունի', 'Мартуни', 'Martuni'), 'slug' => 'martuni'],
                    ['name' => $this->localized('Վարդենիս', 'Варденис', 'Vardenis'), 'slug' => 'vardenis'],
                    ['name' => $this->localized('Չամբարակ', 'Чамбарак', 'Chambarak'), 'slug' => 'chambarak'],
                ],
            ],
            [
                'name' => $this->localized('Կոտայք', 'Котайк', 'Kotayk'),
                'slug' => 'kotayk',
                'cities' => [
                    ['name' => $this->localized('Հրազդան', 'Раздан', 'Hrazdan'), 'slug' => 'hrazdan'],
                    ['name' => $this->localized('Աբովյան', 'Абовян', 'Abovyan'), 'slug' => 'abovyan'],
                    ['name' => $this->localized('Չարենցավան', 'Чаренцаван', 'Charentsavan'), 'slug' => 'charentsavan'],
                    ['name' => $this->localized('Նոր Հաճըն', 'Нор Ачн', 'Nor Hachn'), 'slug' => 'nor-hachn'],
                    ['name' => $this->localized('Եղվարդ', 'Егвард', 'Yeghvard'), 'slug' => 'yeghvard'],
                ],
            ],
            [
                'name' => $this->localized('Լոռի', 'Лори', 'Lori'),
                'slug' => 'lori',
                'cities' => [
                    ['name' => $this->localized('Վանաձոր', 'Ванадзор', 'Vanadzor'), 'slug' => 'vanadzor'],
                    ['name' => $this->localized('Ալավերդի', 'Алаверди', 'Alaverdi'), 'slug' => 'alaverdi'],
                    ['name' => $this->localized('Ստեփանավան', 'Степанаван', 'Stepanavan'), 'slug' => 'stepanavan'],
                    ['name' => $this->localized('Սպիտակ', 'Спитак', 'Spitak'), 'slug' => 'spitak'],
                    ['name' => $this->localized('Տաշիր', 'Ташир', 'Tashir'), 'slug' => 'tashir'],
                ],
            ],
            [
                'name' => $this->localized('Շիրակ', 'Ширак', 'Shirak'),
                'slug' => 'shirak',
                'cities' => [
                    ['name' => $this->localized('Գյումրի', 'Гюмри', 'Gyumri'), 'slug' => 'gyumri'],
                    ['name' => $this->localized('Արթիկ', 'Артик', 'Artik'), 'slug' => 'artik'],
                    ['name' => $this->localized('Մարալիկ', 'Маралик', 'Maralik'), 'slug' => 'maralik'],
                ],
            ],
            [
                'name' => $this->localized('Սյունիք', 'Сюник', 'Syunik'),
                'slug' => 'syunik',
                'cities' => [
                    ['name' => $this->localized('Կապան', 'Капан', 'Kapan'), 'slug' => 'kapan'],
                    ['name' => $this->localized('Գորիս', 'Горис', 'Goris'), 'slug' => 'goris'],
                    ['name' => $this->localized('Սիսիան', 'Сисиан', 'Sisian'), 'slug' => 'sisian'],
                    ['name' => $this->localized('Մեղրի', 'Мегри', 'Meghri'), 'slug' => 'meghri'],
                    ['name' => $this->localized('Ագարակ', 'Агарак', 'Agarak'), 'slug' => 'agarak'],
                ],
            ],
            [
                'name' => $this->localized('Տավուշ', 'Тавуш', 'Tavush'),
                'slug' => 'tavush',
                'cities' => [
                    ['name' => $this->localized('Իջևան', 'Иджеван', 'Ijevan'), 'slug' => 'ijevan'],
                    ['name' => $this->localized('Դիլիջան', 'Дилижан', 'Dilijan'), 'slug' => 'dilijan'],
                    ['name' => $this->localized('Բերդ', 'Берд', 'Berd'), 'slug' => 'berd'],
                    ['name' => $this->localized('Նոյեմբերյան', 'Ноемберян', 'Noyemberyan'), 'slug' => 'noyemberyan'],
                ],
            ],
            [
                'name' => $this->localized('Վայոց Ձոր', 'Вайоц Дзор', 'Vayots Dzor'),
                'slug' => 'vayots-dzor',
                'cities' => [
                    ['name' => $this->localized('Եղեգնաձոր', 'Ехегнадзор', 'Yeghignadzor'), 'slug' => 'yeghignadzor'],
                    ['name' => $this->localized('Վայք', 'Вайк', 'Vayk'), 'slug' => 'vayk'],
                    ['name' => $this->localized('Արենի', 'Арэни', 'Areni'), 'slug' => 'areni'],
                ],
            ],
        ];

        foreach ($provinces as $provinceData) {
            $province = Region::create([
                'parent_id' => $country->id,
                'name' => $provinceData['name'],
                'slug' => $provinceData['slug'],
                'type' => 'district',
            ]);

            foreach ($provinceData['cities'] as $cityData) {
                Region::create([
                    'parent_id' => $province->id,
                    'name' => $cityData['name'],
                    'slug' => $cityData['slug'],
                    'type' => 'city',
                ]);
            }
        }
    }

    private function localized(string $hy, string $ru, string $en): array
    {
        return [
            'hy' => $hy,
            'ru' => $ru,
            'en' => $en,
        ];
    }
}
