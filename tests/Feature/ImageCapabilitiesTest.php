<?php

use App\Services\Images\ImageCapabilities;
use Illuminate\Support\Facades\Cache;

// Placed in tests/Feature (not tests/Unit) because tests/Pest.php only binds
// Tests\TestCase into the "Feature" directory. ImageCapabilities itself has
// no constructor dependencies, but report() calls the Cache facade, which
// needs a booted container — a plain Unit test (PHPUnit's bare TestCase)
// has none. Rather than widen tests/Pest.php's uses() for one class, the
// test lives where the container it needs is already wired up.

beforeEach(function () {
    // report() caches for an hour. Belt-and-braces against leakage even
    // though Laravel's TestCase boots a fresh Application (and therefore a
    // fresh array cache store) per test method already — see the
    // "does not read a stale cached report" test below, which proves that
    // isolation is real rather than assumed.
    Cache::forget('image-capabilities');
});

it('always supports jpeg', function () {
    expect(app(ImageCapabilities::class)->supports('jpeg'))->toBeTrue();
});

it('always ends the chain with jpeg so something always encodes', function () {
    $chain = app(ImageCapabilities::class)->bestChain();

    expect($chain)->not->toBeEmpty()
        ->and(end($chain))->toBe('jpeg');
});

it('orders the chain best-first', function () {
    $chain = app(ImageCapabilities::class)->bestChain();
    $rank = ['avif' => 0, 'webp' => 1, 'jpeg' => 2];

    $ranks = array_map(fn ($f) => $rank[$f], $chain);
    $sorted = $ranks;
    sort($sorted);

    expect($ranks)->toBe($sorted);
});

it('reports on every known format', function () {
    expect(app(ImageCapabilities::class)->report())
        ->toHaveKeys(['avif', 'webp', 'jpeg']);
});

it('never claims a format the runtime cannot encode', function () {
    $caps = app(ImageCapabilities::class);

    if ($caps->supports('webp')) {
        expect(function_exists('imagewebp') || extension_loaded('imagick'))->toBeTrue();
    }
    if ($caps->supports('avif')) {
        expect(function_exists('imageavif') || extension_loaded('imagick'))->toBeTrue();
    }
})->skip(fn () => ! extension_loaded('gd') && ! extension_loaded('imagick'), 'no image extension');

it('reflects this machine\'s actual encoding support, not a pinned assumption', function () {
    // The report must match live function_exists()/extension_loaded()
    // detection on whichever runtime happens to be running the suite. A
    // hardcoded ['avif' => true, 'webp' => true, 'jpeg' => true] here pins
    // this developer's machine into the suite: a CI runner whose GD lacks
    // AVIF (common with shivammathur/setup-php and older libgd) would go
    // red with nothing actually wrong.
    $report = app(ImageCapabilities::class)->report();

    $expectedAvif = extension_loaded('imagick')
        ? in_array('AVIF', array_map('strtoupper', \Imagick::queryFormats()), true)
        : function_exists('imageavif');

    $expectedWebp = extension_loaded('imagick')
        ? in_array('WEBP', array_map('strtoupper', \Imagick::queryFormats()), true)
        : function_exists('imagewebp');

    expect($report)->toBe([
        'avif' => $expectedAvif,
        'webp' => $expectedWebp,
        'jpeg' => true,
    ]);
})->skip(fn () => ! extension_loaded('gd') && ! extension_loaded('imagick'), 'no image extension');

it('actually reads through the cache rather than recomputing every call', function () {
    // Prove report() is genuinely cache-backed: seed the cache with a
    // deliberately wrong value and confirm it comes back out, rather than
    // the real (correct, on this machine) detection result. If report()
    // ignored the cache this assertion would fail.
    $fake = ['avif' => false, 'webp' => false, 'jpeg' => true];
    Cache::put('image-capabilities', $fake, 3600);

    expect(app(ImageCapabilities::class)->report())->toBe($fake)
        ->and(app(ImageCapabilities::class)->supports('avif'))->toBeFalse()
        ->and(app(ImageCapabilities::class)->bestChain())->toBe(['jpeg']);
});

it('does not read a stale cached report left by a previous test', function () {
    // The previous test seeded the cache with avif/webp forced false and
    // never cleared it. If that leaked across test methods, this test would
    // see the fake value here too. It doesn't, because Laravel's base
    // TestCase boots a fresh Application (and therefore a fresh array cache
    // store) per test, and beforeEach() above also forgets the key.
    $report = app(ImageCapabilities::class)->report();

    expect($report)->not->toBe(['avif' => false, 'webp' => false, 'jpeg' => true]);
});

it('selects Imagick when it is loaded, GD otherwise', function () {
    $driver = app(ImageCapabilities::class)->driver();

    if (extension_loaded('imagick')) {
        expect($driver)->toBeInstanceOf(\Intervention\Image\Drivers\Imagick\Driver::class);
    } else {
        expect($driver)->toBeInstanceOf(\Intervention\Image\Drivers\Gd\Driver::class);
    }
});

it('bases format detection on the selected driver, not the union of both libraries', function () {
    // If Imagick is loaded it is the selected driver, so a format it can't
    // encode must not be reported as supported just because GD could.
    // (This machine's actual combination is asserted above; this proves the
    // *rule*, independent of which library happens to be present here.)
    $caps = app(ImageCapabilities::class);
    $report = $caps->report();

    if (extension_loaded('imagick')) {
        $imagickFormats = array_map('strtoupper', \Imagick::queryFormats());
        expect($report['avif'])->toBe(in_array('AVIF', $imagickFormats, true));
        expect($report['webp'])->toBe(in_array('WEBP', $imagickFormats, true));
    } else {
        expect($report['avif'])->toBe(function_exists('imageavif'));
        expect($report['webp'])->toBe(function_exists('imagewebp'));
    }
})->skip(fn () => ! extension_loaded('gd') && ! extension_loaded('imagick'), 'no image extension');

it('always ends bestChain with jpeg even if supports(jpeg) were somehow false', function () {
    // Exercise the belt-and-braces append for real, not just assume it: force
    // supports() to lie and say jpeg is unsupported, then confirm bestChain()
    // still appends it. A partial mock lets bestChain()'s own logic run
    // unmodified while only supports() is stubbed.
    $caps = Mockery::mock(ImageCapabilities::class)->makePartial();
    $caps->shouldReceive('supports')->with('jpeg')->andReturn(false);
    $caps->shouldReceive('supports')->with('avif')->andReturn(false);
    $caps->shouldReceive('supports')->with('webp')->andReturn(false);

    $chain = $caps->bestChain();

    expect($chain)->toBe(['jpeg']);
});
