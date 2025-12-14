<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $listings = Listing::where('status', 'published')
            ->latest('updated_at')
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        // Главная страница
        $xml .= '<url>';
        $xml .= '<loc>' . url('/') . '</loc>';
        $xml .= '<changefreq>daily</changefreq>';
        $xml .= '<priority>1.0</priority>';
        $xml .= '<lastmod>' . now()->toIso8601String() . '</lastmod>';
        $xml .= '</url>';
        
        // Страница объявлений
        $xml .= '<url>';
        $xml .= '<loc>' . url('/listings') . '</loc>';
        $xml .= '<changefreq>hourly</changefreq>';
        $xml .= '<priority>0.9</priority>';
        $xml .= '<lastmod>' . now()->toIso8601String() . '</lastmod>';
        $xml .= '</url>';
        
        // Каждое объявление
        foreach ($listings as $listing) {
            $xml .= '<url>';
            $xml .= '<loc>' . url('/listings/' . $listing->id) . '</loc>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '<lastmod>' . $listing->updated_at->toIso8601String() . '</lastmod>';
            $xml .= '</url>';
        }
        
        $xml .= '</urlset>';

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }
}
