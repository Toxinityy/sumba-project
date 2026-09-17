<?php

// tests/Feature/Models/FactoriesTest.php

use App\Models\Consent;
use App\Models\Home;
use App\Models\MediaAsset;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Project;
use App\Models\School;
use App\Models\SponsorshipTier;
use App\Models\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('persists a valid model', function (string $model) {
    expect($model::factory()->create()->exists)->toBeTrue();
})->with([
    Consent::class, MediaAsset::class, School::class, Home::class,
    Project::class, Post::class, SponsorshipTier::class, Partner::class, Stat::class,
]);

it('stores a tier price as whole rupiah with nothing tracking it', function () {
    $tier = SponsorshipTier::factory()->create()->fresh();

    expect($tier->cost)->toBe(180_000_000)
        // A per-tier price is permitted; a fundraising total is not. If any of
        // these columns ever appears, the no-numeric-funding rule has been
        // broken somewhere upstream of this assertion.
        ->and(array_keys($tier->getAttributes()))
        ->not->toContain('raised', 'goal', 'target', 'percent_funded', 'amount_raised');
});
