<?php

// tests/Feature/LocaleRoutingTest.php

it('redirects the root to the default locale', function () {
    // Symfony's Request::create() (used by the test HTTP client) injects a
    // synthetic 'en-us,en;q=0.5' Accept-Language header whenever one isn't
    // explicitly supplied, so a bare $this->get('/') would not exercise a
    // genuinely header-less request — it would instead exercise "browser
    // prefers English", which correctly redirects to /en. Explicitly send
    // an empty header to simulate the real no-header case.
    $this->get('/', ['Accept-Language' => ''])->assertRedirect('/id');
});

it('redirects the root to English when Accept-Language clearly prefers it', function () {
    $this->get('/', ['Accept-Language' => 'en-GB,en;q=0.9'])
        ->assertRedirect('/en');
});

it('serves the Indonesian segment for schools', function () {
    $this->get('/id/sekolah')->assertOk();
});

it('serves the English segment for schools', function () {
    $this->get('/en/schools')->assertOk();
});

it('does not serve the English segment under the Indonesian prefix', function () {
    $this->get('/id/schools')->assertNotFound();
});

it('does not serve content under an unsupported locale prefix', function () {
    // This only proves no route responds under /fr/ — with no {locale}
    // catch-all route registered, this 404 comes from Laravel's ordinary
    // "no route matched" path, not from SetLocale's own rejection logic.
    // See SetLocaleMiddlewareTest for a test of the middleware's contract.
    $this->get('/fr/sekolah')->assertNotFound();
});

it('sets the application locale from the prefix', function () {
    $this->get('/en/schools');
    expect(app()->getLocale())->toBe('en');
});

it('sets the html lang attribute to the active locale', function () {
    $this->get('/id/sekolah')->assertSee('lang="id"', escape: false);
});
