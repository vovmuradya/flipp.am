<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function (Request $request, $next) {
            if (!Auth::user()->hasPermission('manage_settings') && !Auth::user()->isAdmin()) {
                abort(403, 'У вас нет доступа к настройкам');
            }
            return $next($request);
        });
    }

    /**
     * Отображает страницу настроек
     */
    public function index()
    {
        return view('admin.settings.index');
    }

    /**
     * Обновляет настройки приложения
     */
    public function update(Request $request)
    {
        // В реальном приложении настройки обычно хранятся в базе данных или .env файле
        // Для простоты в этом примере мы просто возвращаемся на страницу настроек
        
        return redirect()->route('admin.settings')->with('success', 'Настройки успешно обновлены');
    }

    /**
     * Очищает кэш приложения
     */
    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        
        return redirect()->back()->with('success', 'Кэш успешно очищен');
    }
}