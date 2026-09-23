<?php

// tests/Feature/Models/PostSeederTest.php

use App\Models\Enums\PostKind;
use App\Models\Post;
use App\Models\School;
use Database\Seeders\PostSeeder;
use Database\Seeders\SchoolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SchoolSeeder::class);
    $this->seed(PostSeeder::class);
});

it('seeds the three stories and two photo essays', function () {
    expect(Post::published()->ofKind(PostKind::Profile)->whereNotNull('quote')->count())->toBe(3)
        ->and(Post::published()->ofKind(PostKind::PhotoEssay)->count())->toBe(2);
});

/*
 | The head teacher is in Karuni's People section AND is the voice that closes
 | the landing page. If the seeder made a second record for her, the portrait
 | and the quote would drift apart the first time someone edited one.
 */
it('fills out the head teacher instead of duplicating her', function () {
    $matches = Post::where('subject_given_name', 'Maria')
        ->whereHas('subjectSurname', fn ($surname) => $surname->where('family_name', 'Bulu'))
        ->get();

    expect($matches)->toHaveCount(1);

    $post = $matches->first();

    expect($post->trans('slug'))->toBe('ibu-maria-bulu')
        ->and($post->subjectName())->toBe('Ibu Maria Bulu')
        ->and($post->about)->toBeInstanceOf(School::class)
        ->and($post->about->trans('slug'))->toBe('karuni')
        // Her portrait came from the school seeder and is not duplicated.
        ->and($post->media()->count())->toBe(1);
});

it('leaves the other subjects off every school People section', function () {
    // Attaching a story to a school would add its subject to that school's
    // People section, which is a page change nobody asked for.
    foreach (['Rambu', 'Umbu'] as $given) {
        expect(Post::where('subject_given_name', $given)->whereNull('about_id')->count())->toBe(1);
    }

    expect(School::whereSlug('kambera', 'id')->firstOrFail()->toDetailArray()['people'])->toHaveCount(2);
});
