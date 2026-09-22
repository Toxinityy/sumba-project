<?php

// tests/Feature/Models/SafeguardingTest.php
//
// Spec §9. These are the tests that matter most in this file set: each one
// stands for a way a child's identity leaks, and none of them is guarded by
// an editor remembering a rule.

use App\Models\Consent;
use App\Models\MediaAsset;
use App\Models\Post;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('refuses to store a surname on a post about a minor', function () {
    Post::factory()->aboutAMinor()->create(['subject_family_name' => 'Wulang']);
})->throws(DomainException::class);

it('allows an adult subject to be named in full with a role', function () {
    $post = Post::factory()->aboutAnAdult()->create();

    expect($post->subjectName())->toContain($post->subject_family_name);
});

it('never appends a surname when the subject is a minor', function () {
    $post = Post::factory()->aboutAMinor()->create();

    expect($post->subjectName())->toBe($post->subject_given_name);
});

it('refuses to store a surname on an asset depicting a minor', function () {
    MediaAsset::factory()->depictingMinor()->create(['subject_family_name' => 'Wulang']);
})->throws(DomainException::class);

it('refuses to store an asset depicting a minor with no consent record', function () {
    MediaAsset::factory()->create(['depicts_minor' => true]);
})->throws(DomainException::class);

it('requires a guardian on a minor consent record', function () {
    Consent::factory()->create(['guardian_name' => null]);
})->throws(DomainException::class);

it('publishes an asset that does not depict a minor without any consent record', function () {
    expect(MediaAsset::factory()->create()->isPublishable())->toBeTrue();
});

it('publishes an asset depicting a minor when web consent is current', function () {
    expect(MediaAsset::factory()->depictingMinor()->create()->isPublishable())->toBeTrue();
});

it('blocks an asset whose consent covers print but not the web', function () {
    // Consent for a printed newsletter is not consent for a public website.
    $consent = Consent::factory()->printOnly()->create();

    expect(MediaAsset::factory()->depictingMinor($consent)->create()->isPublishable())->toBeFalse();
});

it('blocks an asset whose consent is past its review date', function () {
    $consent = Consent::factory()->lapsed()->create();

    expect(MediaAsset::factory()->depictingMinor($consent)->create()->isPublishable())->toBeFalse();
});

it('blocks an asset whose consent has been withdrawn', function () {
    $consent = Consent::factory()->withdrawn()->create();

    expect(MediaAsset::factory()->depictingMinor($consent)->create()->isPublishable())->toBeFalse();
});

it('requires a minor’s assent and an effective grant date for web use', function () {
    $noAssent = Consent::factory()->create(['subject_assented' => false]);
    $futureGrant = Consent::factory()->create(['granted_on' => now()->addDay()->toDateString()]);

    expect($noAssent->coversWebUse())->toBeFalse()
        ->and($futureGrant->coversWebUse())->toBeFalse();
});

it('does not use an adult consent record to publish an image of a minor', function () {
    $adult = Consent::factory()->forAdult()->create();

    expect(MediaAsset::factory()->depictingMinor($adult)->create()->isPublishable())->toBeFalse();
});

it('blocks publishing an owner with media that lacks valid web consent', function () {
    $school = School::factory()->draft()->create();
    $consent = Consent::factory()->printOnly()->create();
    MediaAsset::factory()->depictingMinor($consent)->for($school, 'attachable')->create();

    expect(fn () => $school->update(['published_at' => now()]))->toThrow(DomainException::class);
    expect($school->fresh()->isPublished())->toBeFalse();
});

it('blocks attaching invalid child media to a published owner', function () {
    $school = School::factory()->create();
    $consent = Consent::factory()->printOnly()->create();

    expect(fn () => MediaAsset::factory()->depictingMinor($consent)
        ->for($school, 'attachable')->create())->toThrow(DomainException::class);
    expect($school->media()->count())->toBe(0);
});

it('removes already published content when its consent lapses', function () {
    $school = School::factory()->create();
    $consent = Consent::factory()->create();
    MediaAsset::factory()->depictingMinor($consent)->for($school, 'attachable')->create();

    $consent->update(['review_on' => now()->subDay()->toDateString()]);

    expect(School::published()->whereKey($school)->exists())->toBeFalse()
        ->and($school->fresh()->isPublished())->toBeFalse();
});

it('unpublishes everything using an asset the moment consent is withdrawn', function () {
    $consent = Consent::factory()->create();
    $school = School::factory()->create();
    $post = Post::factory()->aboutAMinor()->create();

    MediaAsset::factory()->depictingMinor($consent)->for($school, 'attachable')->create();
    MediaAsset::factory()->depictingMinor($consent)->for($post, 'attachable')->create();

    $consent->withdraw();

    // Immediately, not as a queued job: withdrawal that takes effect on the
    // next cron run is a published photograph of a child whose guardian has
    // said no.
    expect($school->fresh()->isPublished())->toBeFalse()
        ->and($post->fresh()->isPublished())->toBeFalse()
        ->and($consent->fresh()->coversWebUse())->toBeFalse();
});

it('rolls consent withdrawal back if unpublishing its content fails', function () {
    $consent = Consent::factory()->create();
    $school = School::factory()->create();
    MediaAsset::factory()->depictingMinor($consent)->for($school, 'attachable')->create();

    School::saving(function (School $record) {
        if ($record->published_at === null) {
            throw new RuntimeException('Simulated write failure');
        }
    });

    expect(fn () => $consent->withdraw())->toThrow(RuntimeException::class);
    expect($consent->fresh()->withdrawn_at)->toBeNull()
        ->and($school->fresh()->isPublished())->toBeTrue();
});

it('finds consent records due for review', function () {
    Consent::factory()->create(['review_on' => now()->addMonths(6)->toDateString()]);
    $expiring = Consent::factory()->create(['review_on' => now()->addWeek()->toDateString()]);

    $due = Consent::where('review_on', '<=', now()->addMonth())->pluck('id');

    expect($due->all())->toBe([$expiring->id]);
});
