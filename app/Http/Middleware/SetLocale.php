<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the active UI locale for every web request.
 *
 * Priority: ?lang= query param  ->  session  ->  config('app.locale').
 * The chosen locale is persisted in the session so it survives navigation.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('reva.locales', ['ar', 'en']);

        // 1) Explicit switch via ?lang=ar|en (used by the navbar switcher).
        if ($request->has('lang') && in_array($request->query('lang'), $supported, true)) {
            Session::put('locale', $request->query('lang'));
        }

        // 2) Fall back to the session, then the app default.
        $locale = Session::get('locale', config('app.locale'));

        if (! in_array($locale, $supported, true)) {
            $locale = config('app.fallback_locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
