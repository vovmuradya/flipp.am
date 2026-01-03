<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Listing;
use App\Models\Message;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;

        $this->middleware('auth');
        $this->middleware(function (Request $request, $next) {
            if (!Auth::user()->hasPermission('view_analytics') && !Auth::user()->isAdmin()) {
                abort(403, 'У вас нет доступа к аналитике');
            }
            return $next($request);
        });
    }

    /**
     * Отображает страницу аналитики
     */
    public function index()
    {
        // Общая статистика
        $totalUsers = User::count();
        $totalListings = Listing::count();
        $activeListings = Listing::where('status', 'active')->count();
        $pendingListings = Listing::where('status', 'pending')->count();

        // Статистика за последний месяц
        $usersLastMonth = User::where('created_at', '>=', now()->subMonth())->count();
        $listingsLastMonth = Listing::where('created_at', '>=', now()->subMonth())->count();

        // Активные пользователи за последние 24 часа
        $activeUsersToday = User::where('last_login_at', '>=', now()->startOfDay())->count();

        // Последние регистрации
        $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get();

        // Последние объявления
        $recentListings = Listing::with('user')->orderBy('created_at', 'desc')->limit(5)->get();

        // Статистика по трафику
        $trafficStats = $this->analyticsService->getTrafficStats();
        $basicStats = $this->analyticsService->getBasicStats();
        $realTimeStats = $this->analyticsService->getRealTimeStats();

        return view('admin.analytics.index', compact(
            'totalUsers',
            'totalListings',
            'activeListings',
            'pendingListings',
            'usersLastMonth',
            'listingsLastMonth',
            'activeUsersToday',
            'recentUsers',
            'recentListings',
            'trafficStats',
            'basicStats',
            'realTimeStats'
        ));
    }

    /**
     * Возвращает данные для графиков
     */
    public function getChartData(Request $request)
    {
        $period = $request->get('period', 'month'); // 'week', 'month', 'year'

        switch ($period) {
            case 'week':
                $startDate = now()->subWeek();
                $interval = 'day';
                break;
            case 'year':
                $startDate = now()->subYear();
                $interval = 'month';
                break;
            default: // month
                $startDate = now()->subMonth();
                $interval = 'day';
                break;
        }

        // Регистрации пользователей
        $userRegistrations = User::select(
                DB::raw("DATE(created_at) as date"),
                DB::raw("COUNT(*) as count")
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy('date')
            ->get();

        // Создание объявлений
        $listingCreations = Listing::select(
                DB::raw("DATE(created_at) as date"),
                DB::raw("COUNT(*) as count")
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy('date')
            ->get();

        // Посещения
        $visits = \App\Models\Visit::select(
                DB::raw("DATE(visited_at) as date"),
                DB::raw("COUNT(*) as count")
            )
            ->where('visited_at', '>=', $startDate)
            ->groupBy(DB::raw("DATE(visited_at)"))
            ->orderBy('date')
            ->get();

        return response()->json([
            'userRegistrations' => $userRegistrations,
            'listingCreations' => $listingCreations,
            'visits' => $visits,
            'period' => $period
        ]);
    }

    /**
     * Отображает страницу детальной аналитики
     */
    public function detailed(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        $trafficStats = $this->analyticsService->getTrafficStats($startDate, $endDate);
        $trafficSources = $this->analyticsService->getTrafficSources($startDate, $endDate);
        $popularPages = $this->analyticsService->getPopularPages($startDate, $endDate);
        $conversionStats = $this->analyticsService->getConversionStats($startDate, $endDate);
        $basicStats = $this->analyticsService->getBasicStats($startDate, $endDate);

        return view('admin.analytics.detailed', compact(
            'trafficStats',
            'trafficSources',
            'popularPages',
            'conversionStats',
            'basicStats',
            'startDate',
            'endDate'
        ));
    }
}