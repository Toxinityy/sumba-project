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

it('puts one sun/moon theme toggle in the nav, right after the language switcher', function () {
    $html = $this->get('/id/sekolah')->getContent();

    // Exactly one toggle, carrying both icons — CSS decides which is visible.
    expect(substr_count($html, 'data-theme-toggle'))->toBe(1)
        ->and($html)->toContain('theme-toggle__moon')
        ->and($html)->toContain('theme-toggle__sun');

    // Inside the sticky header, and after the ID/EN switcher rather than
    // somewhere else in the bar.
    $headerStart = strpos($html, '<header');
    $headerEnd = strpos($html, '</header>');
    // Searched from the header onward: hreflang="en" also appears earlier, in
    // the <head>'s alternate-language link, which would make this ordering
    // check pass no matter where the toggle sat.
    $langSwitch = strpos($html, 'hreflang="en"', $headerStart);
    $toggle = strpos($html, 'data-theme-toggle');

    expect($toggle)->toBeGreaterThan($headerStart)
        ->and($toggle)->toBeLessThan($headerEnd)
        ->and($toggle)->toBeGreaterThan($langSwitch);
});

it('gives the theme toggle a real accessible name in both languages', function () {
    // An icon-only button with no name is announced as just "button".
    $this->get('/id/sekolah')->assertSee('aria-label="Mode gelap"', escape: false);
    $this->get('/en/schools')->assertSee('aria-label="Dark mode"', escape: false);
});

it('no longer renders the old floating three-button theme panel', function () {
    $this->get('/id/sekolah')
        ->assertDontSee('data-theme-btn', escape: false)
        ->assertDontSee('fixed bottom-4 right-4', escape: false);
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

it('offers a skip link targeting the main landmark, as the first focusable element', function () {
    $html = $this->get('/id/sekolah')->getContent();

    $bodyStart = strpos($html, '<body');
    $skipLinkPos = strpos($html, 'href="#main"');
    $navPos = strpos($html, '<x-site-nav');
    $navPos = $navPos === false ? strpos($html, '<header') : $navPos;

    expect($skipLinkPos)->not->toBeFalse()
        ->and($skipLinkPos)->toBeGreaterThan($bodyStart)
        ->and($skipLinkPos)->toBeLessThan($navPos)
        ->and($html)->toContain('<main id="main">');
});
