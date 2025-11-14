<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Support\Facades\DB;

class VehicleCategoryResolver
{
    /**
     * Resolve default category id for vehicle listings.
     */
    public static function resolve(): ?int
    {
        static $cachedId = null;

        if ($cachedId !== null) {
            return $cachedId;
        }

        $preferredSlugs = ['cars', 'vehicles', 'transport-cars'];
        foreach ($preferredSlugs as $slug) {
            $category = Category::where('slug', $slug)->first();
            if ($category) {
                return $cachedId = (int) $category->id;
            }
        }

        $transport = Category::where('slug', 'transport')->first();
        if ($transport) {
            $child = $transport->children()->orderBy('id')->first();
            if ($child) {
                return $cachedId = (int) $child->id;
            }
        }

        $bootstrapped = self::bootstrap($transport);
        if ($bootstrapped) {
            return $cachedId = (int) $bootstrapped->id;
        }

        $fallback = Category::orderBy('id')->first();

        return $cachedId = $fallback ? (int) $fallback->id : null;
    }

    protected static function bootstrap(?Category $transport = null): ?Category
    {
        return DB::transaction(function () use ($transport) {
            $transportCategory = $transport ?? Category::firstOrCreate(
                ['slug' => 'transport'],
                [
                    'name' => [
                        'ru' => 'Транспорт',
                        'en' => 'Transport',
                        'hy' => 'Տրանսպորտ',
                    ],
                    'is_active' => true,
                ]
            );

            return Category::firstOrCreate(
                ['slug' => 'cars'],
                [
                    'parent_id' => $transportCategory->id,
                    'name' => [
                        'ru' => 'Автомобили',
                        'en' => 'Cars',
                        'hy' => 'Ավտոմեքենաներ',
                    ],
                    'is_active' => true,
                ]
            );
        });
    }
}
