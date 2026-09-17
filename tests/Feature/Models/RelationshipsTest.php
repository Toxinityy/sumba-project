<?php

// tests/Feature/Models/RelationshipsTest.php

use App\Models\Enums\PostKind;
use App\Models\Enums\ProjectStatus;
use App\Models\Enums\SchoolLevel;
use App\Models\Home;
use App\Models\MediaAsset;
use App\Models\Post;
use App\Models\Project;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('surfaces a post on the school it is about', function () {
    $school = School::factory()->create();
    Post::factory()->for($school, 'about')->create();
    Post::factory()->create();

    expect($school->posts)->toHaveCount(1);
});

it('lets a project belong to a home instead of a school', function () {
    $home = Home::factory()->create();
    $project = Project::factory()->for($home)->create();

    expect($project->home->is($home))->toBeTrue()
        ->and($project->school)->toBeNull()
        ->and($home->projects)->toHaveCount(1);
});

it('separates media by role and orders it by position', function () {
    $school = School::factory()->create();
    MediaAsset::factory()->role('hero')->for($school, 'attachable')->create();
    MediaAsset::factory()->role('gallery')->for($school, 'attachable')->create(['position' => 2]);
    MediaAsset::factory()->role('gallery')->for($school, 'attachable')->create(['position' => 1]);

    expect($school->mediaFor('gallery')->get())->toHaveCount(2)
        ->and($school->mediaFor('gallery')->pluck('position')->all())->toBe([1, 2])
        ->and($school->media)->toHaveCount(3);
});

it('knows when a project has both halves of an evidence pair', function () {
    $project = Project::factory()->create();
    MediaAsset::factory()->role('before')->for($project, 'attachable')->create();

    expect($project->hasEvidencePair())->toBeFalse();

    MediaAsset::factory()->role('after')->for($project, 'attachable')->create();

    expect($project->hasEvidencePair())->toBeTrue();
});

it('excludes drafts and future dates from published scopes', function () {
    School::factory()->create();
    School::factory()->draft()->create();
    School::factory()->create(['published_at' => now()->addWeek()]);

    expect(School::published()->count())->toBe(1);
});

it('casts the short enums rather than storing numbers', function () {
    $school = School::factory()->create(['level' => 'SMP']);
    $project = Project::factory()->create(['status' => 'complete']);
    $post = Post::factory()->create(['kind' => 'profile']);

    expect($school->fresh()->level)->toBe(SchoolLevel::Smp)
        ->and($project->fresh()->status)->toBe(ProjectStatus::Complete)
        ->and($post->fresh()->kind)->toBe(PostKind::Profile);
});

it('filters posts by kind', function () {
    Post::factory()->aboutAnAdult()->create();
    Post::factory()->create();

    expect(Post::ofKind(PostKind::Profile)->count())->toBe(1);
});
