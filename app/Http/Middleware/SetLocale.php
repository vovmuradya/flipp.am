<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $supported = Config::get('app.supported_locales', []);
        $fallback = Config::get('app.locale');
        $locale = Session::get('app_locale', $fallback);

        if (! in_array($locale, $supported, true)) {
            $locale = $fallback;
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
