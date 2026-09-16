<?php

use App\Services\Images\VariantGenerator;

beforeEach(function () {
    $this->source = base_path('tests/fixtures/sample-hero.jpg');

    if (! file_exists($this->source)) {
        require_once base_path('tests/fixtures/build-fixture.php');
        sumba_build_hero_fixture($this->source);
    }

    $this->generator = app(VariantGenerator::class);
});

it('produces a file at the requested width', function () {
    $variant = $this->generator->generate($this->source, 1200, 'jpeg', 200_000);

    expect($variant->width)->toBe(1200)
        ->and(file_exists($variant->path))->toBeTrue();
});

it('keeps a hero variant inside the 200KB budget', function () {
    $variant = $this->generator->generate($this->source, 1600, 'jpeg', 200_000);

    expect($variant->bytes)->toBeLessThanOrEqual(200_000);
});

it('steps quality down rather than overshooting the budget', function () {
    $generous = $this->generator->generate($this->source, 1600, 'jpeg', 200_000);
    $tight    = $this->generator->generate($this->source, 1600, 'jpeg', 40_000);

    expect($tight->quality)->toBeLessThan($generous->quality);
});

it('flags when it hit the quality floor instead of silently shipping mush', function () {
    $variant = $this->generator->generate($this->source, 2400, 'jpeg', 5_000);

    expect($variant->hitQualityFloor)->toBeTrue();
});

it('reports the height it actually produced', function () {
    $variant = $this->generator->generate($this->source, 800, 'jpeg', 200_000);

    expect($variant->height)->toBeGreaterThan(0);
});
