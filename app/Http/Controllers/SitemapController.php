<?php

namespace App\Http\Controllers;

use App\Models\CarBrand;
use App\Models\Listing;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class SitemapController extends Controller
{
    /**
        * Простой sitemap: выдаёт последние объявления и несколько pSEO-лендингов.
        */
    public function index(): Response
    {
        $listings = Listing::query()
            ->active()
            ->latest()
            ->take(100)
            ->get(['id', 'updated_at']);

        $pseoPaths = $this->pseoPaths();

        $items = [];

        foreach ($listings as $listing) {
            $items[] = [
                'loc' => route('listings.show', $listing),
                'lastmod' => optional($listing->updated_at)->toAtomString(),
            ];
        }

        foreach ($pseoPaths as $path) {
            $items[] = [
                'loc' => URL::to('/pseo/' . ltrim($path, '/')),
                'lastmod' => Carbon::now()->toAtomString(),
            ];
        }

        $xml = view('sitemap.xml', ['items' => $items])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    private function pseoPaths(): array
    {
        $brands = CarBrand::query()
            ->orderByRaw('COALESCE(NULLIF(name_ru, \'\'), name_en)')
            ->take(20)
            ->get(['name_en', 'name_ru'])
            ->map(fn ($b) => $b->name_en ?: $b->name_ru)
            ->filter()
            ->values()
            ->all();

        $paths = [];

        foreach ($brands as $brand) {
            $slug = strtolower($brand);
            $paths[] = "$slug/under-10000";
            $paths[] = "diesel/$slug";
        }

        // Добавим несколько общих комбинаций
        $paths[] = 'suv/under-15000';
        $paths[] = 'hybrid/under-20000';
        $paths[] = 'electric';

        return array_unique($paths);
    }
}
