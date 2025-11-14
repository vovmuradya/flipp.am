<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        $transportId = Category::where('slug', 'transport')->value('id');
        if (!$transportId) {
            return;
        }

        $entries = [
            [
                'slug' => 'auto-parts',
                'name' => [
                    'ru' => 'Автозапчасти',
                    'en' => 'Auto Parts',
                    'hy' => 'Ավտոպահեստամասեր',
                ],
            ],
            [
                'slug' => 'tires',
                'name' => [
                    'ru' => 'Шины и диски',
                    'en' => 'Tires & Wheels',
                    'hy' => 'Անիվներ և անվադողեր',
                ],
            ],
        ];

        foreach ($entries as $data) {
            Category::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'parent_id' => $transportId,
                    'name' => $data['name'],
                    'is_active' => true,
                ]
            );
        }
    }

    public function down(): void
    {
        Category::whereIn('slug', ['auto-parts', 'tires'])->delete();
    }
};
