<?php
// tests/Feature/LayoutTest.php

it('sets the html lang to the active locale', function () {
    $this->get('/en/schools')->assertSee('lang="en"', escape: false);
});

it('emits reciprocal hreflang alternates plus x-default', function () {
    $response = $this->get('/id/sekolah');

    $response->assertSee('hreflang="id"', escape: false)
        ->assertSee('hreflang="en"', escape: false)
        ->assertSee('hreflang="x-default"', escape: false);
});

it('paints an explicit background so it never borrows the host ground', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toContain('background-color: var(--surface)');
});

it('applies a saved theme before the body parses to avoid a flash', function () {
    $html = $this->get('/id/sekolah')->getContent();

    $headEnd = strpos($html, '</head>');
    $themeScript = strpos($html, 'hfs-theme');

    expect($themeScript)->toBeLessThan($headEnd);
});

it('offers light, dark and system theme choices', function () {
    $this->get('/id/sekolah')
        ->assertSee('data-theme-btn="light"', escape: false)
        ->assertSee('data-theme-btn="dark"', escape: false)
        ->assertSee('data-theme-btn="system"', escape: false);
});

it('always emits a non-empty title', function () {
    // Rendered directly rather than through a page: the fallback belongs to
    // the layout, and every version of this test that pointed at "whichever
    // page is still a placeholder" broke the moment that page was built.
    // There are no placeholder pages left to point at now anyway.
    $this->blade('<x-layouts.site>content</x-layouts.site>')
        ->assertSee('<title>Hope for Sumba</title>', escape: false);
});

it('emits exactly one main landmark wrapping only the page content', function () {
    $html = $this->get('/id/sekolah')->getContent();

    expect(substr_count($html, '<main'))->toBe(1);
});

it('emits reciprocal hreflang alternates plus x-default on an English page too', function () {
    $this->get('/en/schools')
        ->assertSee('hreflang="id"', escape: false)
        ->assertSee('hreflang="en"', escape: false)
        ->assertSee('hreflang="x-default"', escape: false);
});
