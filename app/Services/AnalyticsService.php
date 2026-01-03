<?php

namespace App\Services;

use App\Models\Visit;
use App\Models\User;
use App\Models\Listing;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Получает основную статистику за период
     */
    public function getBasicStats($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: now()->subDays(30);
        $endDate = $endDate ?: now();

        $visits = Visit::whereBetween('visited_at', [$startDate, $endDate])->count();
        $uniqueVisits = Visit::whereBetween('visited_at', [$startDate, $endDate])
            ->distinct('session_id')
            ->count('session_id');
        $newUsers = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $newListings = Listing::whereBetween('created_at', [$startDate, $endDate])->count();

        return [
            'total_visits' => $visits,
            'unique_visits' => $uniqueVisits,
            'new_users' => $newUsers,
            'new_listings' => $newListings,
        ];
    }

    /**
     * Получает статистику по трафику за период
     */
    public function getTrafficStats($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: now()->subDays(30);
        $endDate = $endDate ?: now();

        // Посещения по дням
        $dailyVisits = Visit::select(
                DB::raw('DATE(visited_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(visited_at)'))
            ->orderBy('date')
            ->get();

        // Посещения по типу устройства
        $deviceStats = Visit::select(
                'device_type',
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->get();

        // Посещения по браузерам
        $browserStats = Visit::select(
                'browser',
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->get();

        // Посещения по странам
        $countryStats = Visit::select(
                'country',
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->whereNotNull('country')
            ->groupBy('country')
            ->get();

        return [
            'daily_visits' => $dailyVisits,
            'device_stats' => $deviceStats,
            'browser_stats' => $browserStats,
            'country_stats' => $countryStats,
        ];
    }

    /**
     * Получает статистику по источникам трафика
     */
    public function getTrafficSources($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: now()->subDays(30);
        $endDate = $endDate ?: now();

        // Источники трафика
        $referrers = Visit::select(
                'referrer',
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->whereNotNull('referrer')
            ->groupBy('referrer')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // UTM источники
        $utmSources = Visit::select(
                'utm_source',
                DB::raw('COUNT(*) as count')
            )
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->whereNotNull('utm_source')
            ->groupBy('utm_source')
            ->orderBy('count', 'desc')
            ->get();

        return [
            'referrers' => $referrers,
            'utm_sources' => $utmSources,
        ];
    }

    /**
     * Получает статистику по популярным страницам
     */
    public function getPopularPages($startDate = null, $endDate = null, $limit = 10)
    {
        $startDate = $startDate ?: now()->subDays(30);
        $endDate = $endDate ?: now();

        $pages = Visit::select(
                'page_url',
                DB::raw('COUNT(*) as count'),
                DB::raw('COUNT(DISTINCT session_id) as unique_views')
            )
            ->whereBetween('visited_at', [$startDate, $endDate])
            ->groupBy('page_url')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->get();

        return $pages;
    }

    /**
     * Получает статистику по конверсиям
     */
    public function getConversionStats($startDate = null, $endDate = null)
    {
        $startDate = $startDate ?: now()->subDays(30);
        $endDate = $endDate ?: now();

        $visits = Visit::whereBetween('visited_at', [$startDate, $endDate])->count();
        $registrations = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $listingsCreated = Listing::whereBetween('created_at', [$startDate, $endDate])->count();

        $registrationConversion = $visits > 0 ? round(($registrations / $visits) * 100, 2) : 0;
        $listingConversion = $registrations > 0 ? round(($listingsCreated / $registrations) * 100, 2) : 0;

        return [
            'visits' => $visits,
            'registrations' => $registrations,
            'listings_created' => $listingsCreated,
            'registration_conversion_rate' => $registrationConversion,
            'listing_conversion_rate' => $listingConversion,
        ];
    }

    /**
     * Получает статистику в реальном времени
     */
    public function getRealTimeStats()
    {
        $lastHour = now()->subHour();
        $visitsLastHour = Visit::where('visited_at', '>=', $lastHour)->count();
        $activeUsers = Visit::where('visited_at', '>=', $lastHour)
            ->distinct('session_id')
            ->count('session_id');

        return [
            'visits_last_hour' => $visitsLastHour,
            'active_users' => $activeUsers,
        ];
    }
}