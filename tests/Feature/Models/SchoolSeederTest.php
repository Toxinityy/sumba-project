<?php

// tests/Feature/Models/SchoolSeederTest.php

use App\Models\Post;
use App\Models\School;
use Database\Seeders\PostSeeder;
use Database\Seeders\SchoolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('seeds six published schools', function () {
    $this->seed(SchoolSeeder::class);

    expect(School::published()->count())->toBe(6);
});

/*
 | Seeding is an import, not a sync. Before 2026-09-22 a second run made a
 | second set of six schools, their projects, their portraits and their media
 | — and `composer setup` plus any redeploy that reseeds would have done
 | exactly that on a live database.
 */
it('does not duplicate anything when the seeders are run again', function () {
    $this->seed(SchoolSeeder::class);
    $this->seed(PostSeeder::class);

    $before = [
        'schools' => School::count(),
        'posts' => Post::count(),
        'projects' => DB::table('projects')->count(),
        'media' => DB::table('media_assets')->count(),
        'surnames' => DB::table('subject_surnames')->count(),
    ];

    // The control: the counts must be non-trivial, or this passes on an
    // empty database and proves nothing.
    expect($before['schools'])->toBeGreaterThan(0)
        ->and($before['posts'])->toBeGreaterThan(0);

    $this->seed(SchoolSeeder::class);
    $this->seed(PostSeeder::class);

    expect([
        'schools' => School::count(),
        'posts' => Post::count(),
        'projects' => DB::table('projects')->count(),
        'media' => DB::table('media_assets')->count(),
        'surnames' => DB::table('subject_surnames')->count(),
    ])->toBe($before);
});

it('leaves an editor\'s changes alone when the seeders are run again', function () {
    $this->seed(SchoolSeeder::class);

    School::whereSlug('karuni', 'id')->firstOrFail()
        ->update(['lede' => ['id' => 'Diedit oleh Vera.', 'en' => 'Edited by Vera.']]);

    // The reseed must not put the placeholder copy back over her edit.
    $this->seed(SchoolSeeder::class);

    expect(School::whereSlug('karuni', 'id')->firstOrFail()->trans('lede'))
        ->toBe('Diedit oleh Vera.');
});
