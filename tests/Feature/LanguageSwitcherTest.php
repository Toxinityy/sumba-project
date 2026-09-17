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
 | Drive the check from config, not a hand-written list: every page defined
 | in locales.segments (plus the bare home route) must resolve to its
 | counterpart in every other locale, both directions. A switcher that works
 | for schools but silently 404s or query-strings safeguarding is exactly
 | the bug this task exists to prevent.
 */
it('resolves every named route to its counterpart in every other locale', function () {
    $names = array_merge(['home'], array_map(
        fn (string $segment) => in_array($segment, ['schools', 'homes', 'stories'], true)
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
 | ("homes.index"), not the homepage — a reader on an Indonesian detail page
 | clicking EN should land in the English section's index, not on the
 | English homepage. Register a route that exists only under "id" to force
 | that fallback path: "schools" won't do any more, because
 | App\ViewModels\SchoolData::find() (Agent A's fixture layer) now backs a
 | genuine "schools.show" in both locales, so picking "homes" instead (which
 | has no "show" route yet) keeps this test exercising the "not yet
 | translated" case rather than a route that now genuinely resolves.
 */
it('falls back to the nearest ancestor route when the exact counterpart is missing', function () {
    Route::get('/id/rumah-anak/{home}', fn () => 'home detail')
        ->name('id.homes.show');

    $this->get('/id/rumah-anak/some-home');

    expect(LocalizedUrl::forLocale('en'))
        ->toBe(route('en.homes.index'))
        ->not->toContain('?');
});

it('falls back to the locale home only when nothing in the ancestor chain resolves', function () {
    Route::get('/id/orphan/deep/leaf', fn () => 'orphan')
        ->name('id.orphan.deep.leaf');

    $this->get('/id/orphan/deep/leaf');

    expect(LocalizedUrl::forLocale('en'))->toBe(url('/en'));
});
