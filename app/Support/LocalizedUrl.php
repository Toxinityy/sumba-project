<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

class LocalizedUrl
{
    /**
     * The equivalent URL of the current route in another locale.
     *
     * Route names are prefixed by locale ("id.schools.index"), so swapping the
     * prefix yields the sibling route. If the exact counterpart doesn't exist
     * (e.g. a detail page like "en.schools.show" not yet defined), we walk up
     * to the nearest ancestor route in the target locale ("en.schools.index")
     * rather than jumping to the homepage — a reader on an Indonesian school
     * page clicking EN should land in the English schools section, not on the
     * English homepage. The locale home is the final backstop only if nothing
     * in that chain resolves.
     */
    public static function forLocale(string $locale): string
    {
        $current = Route::currentRouteName();

        // There is genuinely no current route to derive an equivalent from
        // here (e.g. called outside a request), so the locale home is the
        // right answer, not a fallback of last resort within a chain.
        if ($current === null) {
            return url("/{$locale}");
        }

        $withoutLocale = preg_replace('/^[a-z]{2}\./', '', $current);
        $segments = explode('.', $withoutLocale);

        while ($segments !== []) {
            $base = implode('.', $segments);
            $target = "{$locale}.{$base}";

            if (Route::has($target)) {
                return route($target, self::parametersFor($target, $locale));
            }

            // A popped segment is a SECTION, not a page — "schools", not
            // "schools.show" — and every section's landing page is named
            // "<section>.index" (Task 4's convention), never the bare
            // section name. So before popping further, try that section's
            // index route: "en.schools.show" (missing) pops to "schools",
            // which resolves via "en.schools.index".
            if (end($segments) !== 'index') {
                $indexTarget = "{$target}.index";

                if (Route::has($indexTarget)) {
                    return route($indexTarget, self::parametersFor($indexTarget, $locale));
                }
            }

            array_pop($segments);
        }

        // Nothing in the chain resolved at all — last-resort backstop.
        return url("/{$locale}");
    }

    /**
     * Route::current()->parameters() returns every bound value for the
     * current route, including ones that only exist as route defaults
     * (Task 4's ->defaults('locale', $locale), and Route::view()'s own
     * 'view'/'status'/'data'/'headers' defaults) rather than real {segments}
     * in the URI. None of those belong to the TARGET route — it has (or will
     * supply) its own defaults — so passing them through unfiltered would
     * ride along as a stray query string instead of being silently
     * overridden. We also can't just keep every genuine placeholder from the
     * CURRENT route: an ancestor fallback target (e.g. "schools.index") may
     * accept fewer placeholders than the current route (e.g. "schools.show"
     * with {school}), and any placeholder the target doesn't declare would
     * itself become a stray query string. So intersect the current route's
     * genuine placeholders with the ones the target route actually declares.
     */
    private static function parametersFor(string $target, string $locale): array
    {
        $currentParameters = array_intersect_key(
            Route::current()->parameters(),
            array_flip(Route::current()->parameterNames())
        );

        $targetParameterNames = Route::getRoutes()->getByName($target)->parameterNames();

        // A bound model carries its slug per locale (spec §7), so it is
        // forwarded as the TARGET locale's slug for the same record. Passing
        // the model itself would put its id in the URL; passing this
        // locale's slug 404s the moment the two locales' slugs differ.
        return array_map(
            fn ($value) => $value instanceof Model && method_exists($value, 'trans')
                ? $value->trans('slug', $locale)
                : $value,
            array_intersect_key($currentParameters, array_flip($targetParameterNames)),
        );
    }

    /**
     * The contact form lives at the end of the landing page, not on a page of
     * its own: /id#kontak, /en#contact. The fragment is the locale's contact
     * segment, so the old /id/kontak and /en/contact read the same.
     */
    public static function contact(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return route("{$locale}.home").'#'.config('locales.segments.contact')[$locale];
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
