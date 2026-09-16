<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
 | The root redirects rather than serving content, so there is exactly one
 | canonical URL per page per locale. Accept-Language only decides WHICH
 | locale; it never causes the same URL to serve two different languages.
 */
Route::get('/', function (Request $request) {
    $preferred = $request->getPreferredLanguage(['id', 'en']);

    return redirect('/'.($preferred === 'en' ? 'en' : config('locales.default')));
});

foreach (config('locales.supported') as $locale) {
    $segments = collect(config('locales.segments'))
        ->map(fn (array $paths) => $paths[$locale]);

    // Each locale gets its own literal prefix (rather than a single {locale}
    // wildcard) because the path segments themselves are translated, so the
    // route table differs per locale, not just the prefix. The locale is
    // attached as a route default below so the setlocale middleware — which
    // reads $request->route('locale') — can see it and 404 unknown locales
    // instead of silently falling back.
    Route::prefix($locale)
        ->middleware('setlocale')
        ->name("{$locale}.")
        ->group(function () use ($segments, $locale) {
            Route::view('/', 'pages.home')->name('home')->defaults('locale', $locale);

            Route::view($segments['about'], 'pages.about')->name('about')->defaults('locale', $locale);
            Route::view($segments['schools'], 'pages.schools')->name('schools.index')->defaults('locale', $locale);
            Route::view($segments['homes'], 'pages.homes')->name('homes.index')->defaults('locale', $locale);
            Route::view($segments['stories'], 'pages.stories')->name('stories.index')->defaults('locale', $locale);
            Route::view($segments['give'], 'pages.give')->name('give')->defaults('locale', $locale);
            Route::view($segments['contact'], 'pages.contact')->name('contact')->defaults('locale', $locale);
            Route::view($segments['safeguarding'], 'pages.safeguarding')->name('safeguarding')->defaults('locale', $locale);
        });
}

// A component gallery, not a public page. Registered outside production so
// the design system can be reviewed on staging without appearing on the site.
if (! app()->environment('production')) {
    Route::view('/gallery', 'gallery')->name('gallery');
}
