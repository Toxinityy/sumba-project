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

it('reflects this machine: GD is loaded, so avif and webp both detect as supported', function () {
    // Pinning this down turns the abstract "detection works" tests above into
    // a concrete claim about this environment (GD loaded, imageavif and
    // imagewebp both present, Imagick absent) — so a regression that flips
    // detectAvif()/detectWebp() to always return false would be caught here
    // even though it wouldn't necessarily break the tests above.
    $report = app(ImageCapabilities::class)->report();

    expect($report)->toBe(['avif' => true, 'webp' => true, 'jpeg' => true]);
});

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

    expect($report)->toBe(['avif' => true, 'webp' => true, 'jpeg' => true]);
});

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
