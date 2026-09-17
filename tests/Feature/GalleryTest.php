<?php
// tests/Feature/GalleryTest.php

it('renders every section without error', function () {
    $this->get('/gallery')->assertOk();
});

it('shows all fifteen sections', function () {
    $response = $this->get('/gallery');

    foreach ([
        'hero', 'lede', 'people', 'context', 'work', 'stat-band',
        'evidence', 'quote', 'stories', 'directory', 'directory-tier',
        'ways', 'form', 'current-need', 'next-step', 'partners',
    ] as $section) {
        $response->assertSee("data-section=\"{$section}\"", escape: false);
    }
});

/*
 | The brief's original version of this test set app()['env'] = 'production'
 | AFTER the route was already registered during test bootstrap, then
 | asserted the route WAS present anyway, with a comment excusing the
 | contradiction. Routes are registered once, when routes/web.php runs at
 | boot; flipping the env container value inside a test does not re-run that
 | file, so the assertion proved nothing about production behaviour either
 | way.
 |
 | The honest check available without re-bootstrapping the framework mid-test
 | is to verify the guard actually exists in the route file: an
 | `app()->environment('production')` check wraps the /gallery registration.
 | That's what actually keeps the route out of production, and it's what
 | this test now asserts.
 */
it('guards the gallery route registration behind a non-production check', function () {
    $source = file_get_contents(base_path('routes/web.php'));

    expect($source)->toContain("! app()->environment('production')");

    // The guard has to be positioned around the gallery registration, not
    // just present anywhere in the file.
    $galleryLine = strpos($source, "Route::view('/gallery'");
    $guardLine = strpos($source, "! app()->environment('production')");

    expect($galleryLine)->not->toBeFalse()
        ->and($guardLine)->not->toBeFalse()
        ->and($guardLine)->toBeLessThan($galleryLine);
});

it('is registered in the testing environment', function () {
    expect(collect(app('router')->getRoutes())->contains(
        fn ($route) => $route->uri() === 'gallery'
    ))->toBeTrue();
});

it('never renders a progress bar anywhere in the system', function () {
    $html = $this->get('/gallery')->getContent();

    expect($html)->not->toContain('<progress')
        ->and($html)->not->toContain('role="progressbar"');
});

it('sets no min-width wider than a phone screen', function () {
    $html = $this->get('/gallery')->getContent();

    // NOTE: this project's sections use Tailwind's min-w-11 scale utility,
    // not arbitrary min-w-[NNNpx] values, so this regex currently matches
    // nothing and the assertion below passes vacuously. Kept as a guard
    // against a future arbitrary min-width creeping in.
    preg_match_all('/min-w-\[(\d+)px\]/', $html, $matches);
    expect($matches[1])->toBeArray();

    foreach ($matches[1] ?? [] as $width) {
        expect((int) $width)->toBeLessThanOrEqual(400);
    }

    // This one DOES catch real horizontal-overflow risk: any arbitrary
    // pixel width (not just min-width) wider than a phone screen forces
    // the page wider than its viewport. Laravel's default welcome.blade.php
    // has exactly this pattern (w-[438px]) - the gallery must not.
    preg_match_all('/\bw-\[(\d+)px\]/', $html, $widthMatches);

    foreach ($widthMatches[1] ?? [] as $width) {
        expect((int) $width)->toBeLessThanOrEqual(400);
    }
});
