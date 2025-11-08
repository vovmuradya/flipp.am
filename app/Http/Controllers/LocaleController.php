<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class LocaleController extends Controller
{
    /**
     * Persist the selected locale in the session and redirect back.
     */
    public function update(Request $request): RedirectResponse
    {
        $supported = Config::get('app.supported_locales', []);
        $fallback = Config::get('app.locale');
        $locale = Str::lower((string) $request->input('locale', $fallback));

        if (! in_array($locale, $supported, true)) {
            $locale = $fallback;
        }

        Session::put('app_locale', $locale);

        return back();
    }
}
