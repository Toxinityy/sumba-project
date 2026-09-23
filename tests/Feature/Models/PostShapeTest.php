<?php

// tests/Feature/Models/PostShapeTest.php

use App\Models\Enums\PostKind;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function profilePost(array $overrides = []): Post
{
    return Post::factory()->create([
        'kind' => PostKind::Profile,
        'slug' => ['id' => 'rambu-sembilan-kilometer', 'en' => 'rambu-nine-kilometres'],
        'title' => ['id' => 'Sembilan kilometer, setiap pagi.', 'en' => 'Nine kilometres, every morning.'],
        'hook' => ['id' => 'Berjalan sembilan kilometer setiap pagi.', 'en' => 'She walked nine kilometres each morning.'],
        'body' => ['id' => '<p>Rambu berangkat pukul lima pagi.</p>', 'en' => '<p>Rambu leaves at five in the morning.</p>'],
        'quote' => ['id' => '“Saya ingin jadi guru.”', 'en' => '“I want to be a teacher.”'],
        'subject_given_name' => 'Rambu',
        // Set on purpose: a minor is a given name alone, so neither an
        // honorific nor a surname may reach a page, whatever is stored.
        'subject_honorific' => 'Ibu',
        'subject_is_minor' => true,
        'subject_role' => ['id' => 'Kelas akhir, SMA Harapan Kambera', 'en' => 'Final year, SMA Harapan Kambera'],
        'published_at' => '2026-08-14',
        ...$overrides,
    ]);
}

it('produces the contract card shape in both locales', function (string $locale) {
    app()->setLocale($locale);
    $card = profilePost()->toCardArray();

    // `href` is the page's job (docs/data-contract.md § Post), as for School.
    expect(array_keys($card))
        ->toBe(['slug', 'kind', 'name', 'title', 'hook', 'image', 'published_at'])
        ->and($card['kind'])->toBe('profile')
        ->and($card['published_at'])->toBe('2026-08-14');

    $expected = $locale === 'id'
        ? ['rambu-sembilan-kilometer', 'Sembilan kilometer, setiap pagi.']
        : ['rambu-nine-kilometres', 'Nine kilometres, every morning.'];

    expect([$card['slug'], $card['title']])->toBe($expected);
})->with(['id', 'en']);

it('adds body and the quote block on the detail shape', function () {
    $detail = profilePost()->toDetailArray();

    expect(array_keys($detail))
        ->toBe(['slug', 'kind', 'name', 'title', 'hook', 'image', 'published_at', 'body', 'quote'])
        ->and($detail['quote'])->toBe([
            'text' => '“Saya ingin jadi guru.”',
            // Attribution and role are the post's own subject fields, so a
            // quote can never name a child differently from the post.
            'attribution' => 'Rambu',
            'role' => 'Kelas akhir, SMA Harapan Kambera',
        ]);
});

it('never lets a quote carry a minor past their given name', function () {
    // The model refuses a surname on a minor at all (spec §9); the point here
    // is that the quote block reads through that rule rather than around it.
    $quote = profilePost()->toDetailArray()['quote'];

    expect($quote['attribution'])->toBe('Rambu')->not->toContain(' ');
});

it('carries an honorific for an adult, and none for a child', function () {
    $adult = profilePost([
        'subject_given_name' => 'Maria',
        'subject_honorific' => 'Ibu',
        'subject_is_minor' => false,
    ]);
    // Adult surnames are their own table now (spec §9).
    $adult->subjectSurname()->create(['family_name' => 'Bulu']);

    expect($adult->subjectName())->toBe('Ibu Maria Bulu')
        ->and(profilePost()->subjectName())->toBe('Rambu');
});

it('knows photo essays as a kind, shaped 3:2', function () {
    $essay = profilePost([
        'kind' => PostKind::PhotoEssay,
        'title' => ['id' => 'Panen bersama di Karuni', 'en' => 'A shared harvest in Karuni'],
        'subject_given_name' => null,
        'subject_is_minor' => false,
    ]);

    // An essay has no subject, so `name` falls back to its own title — the
    // contract's "name means different things per kind".
    expect($essay->toCardArray()['name'])->toBe('Panen bersama di Karuni')
        ->and(PostKind::PhotoEssay->aspectRatio())->toBe('3:2')
        ->and(PostKind::Profile->aspectRatio())->toBe('4:5');
});
