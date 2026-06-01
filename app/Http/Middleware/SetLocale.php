<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /** Supported UI locales. Extend by adding more lang/ files. */
    public const SUPPORTED = ['en', 'sn'];

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->session()->get('locale')
            ?? $request->getPreferredLanguage(self::SUPPORTED)
            ?? config('app.locale', 'en');

        if (! in_array($locale, self::SUPPORTED, true)) $locale = 'en';
        App::setLocale($locale);
        return $next($request);
    }
}
