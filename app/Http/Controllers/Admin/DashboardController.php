<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function (Request $request, $next) {
            // Проверяем, имеет ли пользователь разрешение на просмотр аналитики
            if (!Auth::user()->hasPermission('view_analytics') && !Auth::user()->isAdmin()) {
                abort(403, 'У вас нет доступа к административной панели');
            }
            return $next($request);
        });
    }

    /**
     * Отображает главную страницу административной панели
     */
    public function index()
    {
        // Общая статистика
        $totalUsers = User::count();
        $totalListings = Listing::count();
        $pendingListings = Listing::where('status', 'pending')->count();

        // Активные пользователи за последние 24 часа
        $activeUsersToday = User::where('last_login_at', '>=', now()->startOfDay())->count();

        // Последние регистрации
        $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get();

        // Последние объявления
        $recentListings = Listing::with('user')->orderBy('created_at', 'desc')->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalListings',
            'pendingListings',
            'activeUsersToday',
            'recentUsers',
            'recentListings'
        ));
    }
}