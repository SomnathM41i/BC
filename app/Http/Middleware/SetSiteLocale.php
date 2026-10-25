<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets the language for the public website. English is the default and has
 * no URL prefix; Marathi pages live under /mr.
 */
class SetSiteLocale
{
    public function handle(Request $request, Closure $next, string $locale = 'en'): Response
    {
        $locale = in_array($locale, ['en', 'mr'], true) ? $locale : 'en';

        App::setLocale($locale);
        Session::put('site_locale', $locale);

        return $next($request);
    }
}
