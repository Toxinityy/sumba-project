<?php
// tests/Feature/LanguageSwitcherTest.php

use App\Support\LocalizedUrl;
use Illuminate\Support\Facades\Route;

it('resolves the equivalent page in the other locale, not the homepage', function () {
    $this->get('/id/sekolah');

    expect(LocalizedUrl::forLocale('en'))->toEndWith('/en/schools');
});

it('resolves back again symmetrically', function () {
    $this->get('/en/schools');

    expect(LocalizedUrl::forLocale('id'))->toEndWith('/id/sekolah');
});

it('returns the current url when asked for the current locale', function () {
    $this->get('/id/tentang');

    expect(LocalizedUrl::forLocale('id'))->toEndWith('/id/tentang');
});

it('lists an alternate for every supported locale', function () {
    $this->get('/id/kontak');

    expect(LocalizedUrl::alternates())
        ->toHaveKeys(['id', 'en'])
        ->and(LocalizedUrl::alternates()['en'])->toEndWith('/en/contact');
});

it('renders a switcher linking to the equivalent page', function () {
    $this->get('/id/sekolah')
        ->assertSee('/en/schools', escape: false);
});

/*
 | Task 4 attaches a `locale` route default to every locale route (so the
 | setlocale middleware can read $request->route('locale')). That default
 | shows up in Route::current()->parameters() as locale => <CURRENT locale>.
 | If that leaked into route($target, ...) unstripped, it would ride along
 | as a stray "?locale=id" query string on every switcher link. Assert it
 | never does.
 */
it('never leaks a stray locale query string into the resolved url', function () {
    $this->get('/id/sekolah');

    $url = LocalizedUrl::forLocale('en');

    expect($url)->toEndWith('/en/schools')
        ->and($url)->not->toContain('?');
});

/*
 | The four folded pages used to define their segments inline in
 | routes/web.php, so they escaped the config-driven check below. One source
 | of truth: every translated segment lives in config/locales.php.
 */
it('keeps every translated segment in config, including the folded pages', function () {
    expect(config('locales.segments'))
        ->toHaveKeys(['gallery', 'partners', 'impact', 'projects']);
});

/*
 | Drive the check from config, not a hand-written list: every page defined
 | in locales.segments (plus the bare home route) must resolve to its
 | counterpart in every other locale, both directions. A switcher that works
 | for schools but silently 404s or query-strings safeguarding is exactly
 | the bug this task exists to prevent.
 */
it('resolves every named route to its counterpart in every other locale', function () {
    $names = array_merge(['home'], array_map(
        fn (string $segment) => in_array($segment, ['schools', 'homes', 'stories', 'gallery'], true)
            ? "{$segment}.index"
            : $segment,
        array_keys(config('locales.segments'))
    ));

    $locales = config('locales.supported');

    foreach ($locales as $fromLocale) {
        foreach ($names as $name) {
            $this->get(route("{$fromLocale}.{$name}"));

            foreach ($locales as $toLocale) {
                $expected = route("{$toLocale}.{$name}");
                $actual = LocalizedUrl::forLocale($toLocale);

                expect($actual)
                    ->toBe($expected)
                    ->and($actual)->not->toContain('?');
            }
        }
    }
});

/*
 | The exact-counterpart lookup 404s in the target locale for a route that
 | only exists in the current one (e.g. a detail page not yet translated).
 | It must fall back to the nearest ANCESTOR route in the target locale
 | ("<section>.index"), not the homepage — a reader on an Indonesian detail
 | page clicking EN should land in the English section's index, not on the
 | English homepage.
 |
 | This used to force that path by naming a real section ("schools", then
 | "homes") that had no ".show" route YET — which broke a second time the
 | moment Children's Homes detail pages got built, exactly as it broke the
 | first time schools detail pages did. Both routes below are registered
 | here, under a segment name ("zzz_test_stub") that isn't in
 | config('locales.segments') and never will be — the fixture no longer
 | depends on a real page staying unbuilt.
 */
it('falls back to the nearest ancestor route when the exact counterpart is missing', function () {
    Route::get('/id/zzz-test-stub', fn () => 'stub index')->name('id.zzz_test_stub.index');
    Route::get('/en/zzz-test-stub', fn () => 'stub index')->name('en.zzz_test_stub.index');
    Route::get('/id/zzz-test-stub/{leaf}', fn () => 'stub leaf')->name('id.zzz_test_stub.leaf');

    // RouteServiceProvider only refreshes the name lookup once, from an
    // app->booted() callback that already ran before this test body — a
    // route named after that point (any ->name() call here) is invisible
    // to Route::has()/route() until the lookup is rebuilt by hand.
    app('router')->getRoutes()->refreshNameLookups();

    $this->get('/id/zzz-test-stub/some-leaf');

    expect(LocalizedUrl::forLocale('en'))
        ->toBe(route('en.zzz_test_stub.index'))
        ->not->toContain('?');
});

it('falls back to the locale home only when nothing in the ancestor chain resolves', function () {
    Route::get('/id/orphan/deep/leaf', fn () => 'orphan')
        ->name('id.orphan.deep.leaf');

    $this->get('/id/orphan/deep/leaf');

    expect(LocalizedUrl::forLocale('en'))->toBe(url('/en'));
});
