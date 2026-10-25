<?php

namespace App\Support\Site;

use Illuminate\Support\Facades\Route;

/**
 * Locale-aware URL helpers for the public website.
 *
 * English lives at the root (/about) and Marathi under /mr (/mr/about).
 * Route names are identical in both groups, with the Marathi ones prefixed
 * by "mr." — these helpers pick the right one for the active locale.
 */
final class SiteRoutes
{
    public static function url(string $name, array $parameters = []): string
    {
        $localized = self::localeName($name);

        return Route::has($localized)
            ? route($localized, $parameters)
            : route($name, $parameters);
    }

    /**
     * The same page in the other language, keeping route parameters and
     * the current query string (portfolio filters, for example).
     */
    public static function switchUrl(string $locale): string
    {
        $route = request()->route();
        $current = $route?->getName();

        if ($current === null) {
            return self::urlForLocale('home', $locale);
        }

        $base = str_starts_with($current, 'mr.') ? substr($current, 3) : $current;

        // Only pages that exist in both languages can be switched.
        if (! Route::has($base)) {
            return self::urlForLocale('home', $locale);
        }

        $parameters = $route->parameters();
        $query = request()->query();

        if ($query !== []) {
            $parameters += $query;
        }

        return self::urlForLocale($base, $locale, $parameters);
    }

    public static function urlForLocale(string $name, string $locale, array $parameters = []): string
    {
        $localized = $locale === 'en' ? $name : "{$locale}.{$name}";

        return Route::has($localized)
            ? route($localized, $parameters)
            : route($name, $parameters);
    }

    public static function locale(): string
    {
        return app()->getLocale() === 'mr' ? 'mr' : 'en';
    }

    /**
     * Whether the current page is one of the given (unprefixed) route names.
     */
    public static function isActive(string ...$names): bool
    {
        $current = request()->route()?->getName();

        if ($current === null) {
            return false;
        }

        $base = str_starts_with($current, 'mr.') ? substr($current, 3) : $current;

        return in_array($base, $names, true);
    }

    private static function localeName(string $name): string
    {
        $locale = self::locale();

        return $locale === 'en' ? $name : "{$locale}.{$name}";
    }
}
