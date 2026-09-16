<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class LocalizedUrl
{
    /**
     * The equivalent URL of the current route in another locale.
     *
     * Route names are prefixed by locale ("id.schools.index"), so swapping the
     * prefix yields the sibling route. Falls back to that locale's home rather
     * than throwing, because a missing route must never break page rendering.
     */
    public static function forLocale(string $locale): string
    {
        $current = Route::currentRouteName();

        if ($current === null) {
            return url("/{$locale}");
        }

        $withoutLocale = preg_replace('/^[a-z]{2}\./', '', $current);
        $target = "{$locale}.{$withoutLocale}";

        if (! Route::has($target)) {
            return url("/{$locale}");
        }

        // Route::current()->parameters() returns every bound value for the
        // current route, including ones that only exist as route defaults
        // (Task 4's ->defaults('locale', $locale), and Route::view()'s own
        // 'view'/'status' defaults) rather than real {segments} in the URI.
        // None of those belong to the TARGET route — it has (or will supply)
        // its own defaults — and since these routes have no {segment} in
        // their URI to absorb them, passing them through would ride along
        // as a stray "?locale=id&view=..." query string instead of being
        // silently overridden. Keep only parameters that are genuine URI
        // placeholders on the current route (e.g. a future {slug}), so real
        // dynamic segments still carry across while every default is left
        // for the target route to supply on its own.
        $parameters = array_intersect_key(
            Route::current()->parameters(),
            array_flip(Route::current()->parameterNames())
        );

        return route($target, $parameters);
    }

    /** @return array<string, string> locale => absolute URL, for hreflang. */
    public static function alternates(): array
    {
        $out = [];
        foreach (config('locales.supported') as $locale) {
            $out[$locale] = self::forLocale($locale);
        }

        return $out;
    }
}
