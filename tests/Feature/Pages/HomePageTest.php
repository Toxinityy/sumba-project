<?php
// tests/Feature/Pages/HomePageTest.php

use App\Models\Post;
use App\Models\School;

it('renders the home page in both locales', function () {
    // The masthead now splits at the accented word, which is set in Newsreader
    // italic at the accent colour, so the sentence is no longer one contiguous
    // run of text in the markup. Both halves plus the <em> are asserted, which
    // is a stricter check than the single assertSee this replaced.
    $this->get('/id')->assertOk()
        ->assertSee('Setiap anak berhak atas masa depan yang')
        ->assertSee('<em>cerah.</em>', escape: false);

    $this->get('/en')->assertOk()
        ->assertSee('Every child deserves a')
        ->assertSee('<em>bright future.</em>', escape: false);
});

it('shows the stat band, the featured school and story teasers', function () {
    $this->get('/id')
        ->assertSee('612')
        ->assertSee('TK Harapan Karuni')
        ->assertSee('Rambu');
});

it('never renders a first-and-last name for a minor story subject', function () {
    // 'Rambu' and 'Umbu' are first-name-only by construction in PostData;
    // this guards against a future edit concatenating a surname on.
    $html = $this->get('/id')->getContent();

    expect($html)->not->toContain('Rambu ')
        ->and($html)->not->toContain('Umbu ');
});

it('keeps both homepages usable before editorial content is published', function () {
    Post::query()->update(['published_at' => null]);
    School::query()->update(['published_at' => null]);

    foreach (['id', 'en'] as $locale) {
        $html = $this->get("/{$locale}")->assertOk()->getContent();

        expect($html)->toContain('href="'.\App\Support\LocalizedUrl::contact($locale).'"')
            ->toContain('href="'.route("{$locale}.schools.index").'"')
            ->not->toContain('class="ed-voice', 'class="ed-lead-school');
    }
});

it('omits the featured quote and school after their records are withdrawn', function () {
    Post::whereSlug('ibu-maria-bulu')->update(['published_at' => null]);
    School::whereSlug('anakalang')->update(['published_at' => null]);

    $html = $this->get('/id')->assertOk()->getContent();

    expect($html)->not->toContain('class="ed-voice', 'class="ed-lead-school')
        ->toContain('href="'.route('id.schools.index').'"');
});
