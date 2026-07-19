<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Prompt 21: Set the application locale from the session on every request.
 *
 * Supports: 'en' (English) and 'bn' (Bangla).
 * The locale is stored in the session by the LanguageSwitcherController.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = Session::get('locale', config('app.locale', 'en'));

        // Only allow supported locales — default to 'en' otherwise
        if (! in_array($locale, ['en', 'bn'])) {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
