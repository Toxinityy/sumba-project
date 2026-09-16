<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        // An unknown locale is a 404, not a silent fallback: a wrong prefix
        // serving content would create duplicate indexable URLs.
        abort_unless(in_array($locale, config('locales.supported'), true), 404);

        app()->setLocale($locale);

        return $next($request);
    }
}
