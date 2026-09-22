<?php
// tests/Feature/Pages/StoryDetailPageTest.php

it('renders a story detail page in both locales, spine in order', function () {
    $html = $this->get('/id/cerita/rambu-sembilan-kilometer')->assertOk()->getContent();

    $positions = [];
    foreach (['Sembilan kilometer', 'Rambu berangkat pukul lima pagi', 'Saya ingin jadi guru', 'Mari mulai kemitraan'] as $needle) {
        $pos = strpos($html, $needle);
        expect($pos)->not->toBeFalse("expected to find [{$needle}]");
        $positions[] = $pos;
    }
    expect($positions)->toBe(collect($positions)->sort()->values()->all());

    $this->get('/en/stories/rambu-sembilan-kilometer')->assertOk()->assertSee('Nine kilometres, every morning.');
});

it('renders the other two profiles too, not just Rambu', function () {
    $this->get('/id/cerita/ibu-maria-bulu')->assertOk()->assertSee('Ibu Maria mengajar di Kupang');
    $this->get('/id/cerita/umbu-elektronika')->assertOk()->assertSee('Umbu mulai membongkar radio rusak');
});

it('never renders a surname for the minor subject', function () {
    // Content-model guarantee (PostData's 'name' is a single given name for
    // minors, by construction), plus a page-level check: the hero subhead
    // — the one place the page prints $post['name'] on its own — must be
    // exactly the bare name, never the name plus anything else appended.
    $post = \App\ViewModels\PostData::find('rambu-sembilan-kilometer');
    expect($post['name'])->toBe('Rambu')->and($post['name'])->not->toContain(' ');

    $html = $this->get('/id/cerita/rambu-sembilan-kilometer')->getContent();
    expect($html)->toContain('<p class="text-[19px] leading-relaxed text-ink-muted md:text-[21px]">Rambu</p>');
});

it('404s for a photo-essay slug and for an unknown slug', function () {
    // Photo essays have no lede/quote content — they're gallery-only.
    $this->get('/id/cerita/panen-raya-karuni')->assertNotFound();
    $this->get('/id/cerita/does-not-exist')->assertNotFound();
});

/*
 | The stories pages read the database, not a fixture (2026-09-20). Same three
 | guarantees the schools got: an edit shows up, the language switch carries a
 | reader to the other locale's slug of the same story, and what has no story
 | page 404s rather than half-rendering.
 */
it('renders the story from the database', function () {
    $post = \App\Models\Post::whereSlug('ibu-maria-bulu', 'id')->firstOrFail();
    $post->update([
        'title' => ['id' => 'Judul Diubah dari Basis Data', 'en' => 'Edited in the Database'],
        // The index renders each story's hook, the detail page its title.
        'hook' => ['id' => 'Kail diubah dari basis data.', 'en' => 'Hook edited in the database.'],
    ]);

    $this->get('/id/cerita/ibu-maria-bulu')->assertOk()->assertSee('Judul Diubah dari Basis Data');
    $this->get('/en/stories')->assertOk()->assertSee('Hook edited in the database.');
});

it('links the language switch to the other locale slug of the same story', function () {
    \App\Models\Post::whereSlug('ibu-maria-bulu', 'id')->firstOrFail()
        ->update(['slug' => ['id' => 'ibu-maria-bulu', 'en' => 'maria-bulu-head-teacher']]);

    $this->get('/id/cerita/ibu-maria-bulu')->assertOk()
        ->assertSee('hreflang="en" href="'.url('/en/stories/maria-bulu-head-teacher').'"', escape: false);

    $this->get('/en/stories/maria-bulu-head-teacher')->assertOk();
    $this->get('/en/stories/ibu-maria-bulu')->assertNotFound();
});

it('404s what has no story page of its own', function () {
    // A photo essay lives in the Gallery, and the profile posts behind a
    // school's People section have no story to tell yet: neither has a quote.
    $this->get('/id/cerita/panen-raya-karuni')->assertNotFound();
    $this->get('/id/cerita/yuliana-ndapa')->assertNotFound();
    $this->get('/id/cerita/tidak-ada')->assertNotFound();
});

it('keeps a draft story off the index and its own page', function () {
    \App\Models\Post::whereSlug('umbu-elektronika', 'id')->firstOrFail()->unpublish();

    $this->get('/id/cerita/umbu-elektronika')->assertNotFound();
    $this->get('/id/cerita')->assertOk()->assertDontSee('membongkar radio rusak');
});

/*
 | Spec §7: "missing locale renders the source language with a quiet inline
 | note — and that note is itself translated." Until now the English page fell
 | back to Indonesian silently, which is the one behaviour §7 rules out.
 |
 | Fallback runs requested-locale → default-locale (id), so these cover the
 | direction that exists. An English-first record has no Indonesian to fall
 | back to; see the note in docs/data-contract.md.
 */
it('shows a translated fallback note when the English story is missing', function () {
    \App\Models\Post::whereSlug('ibu-maria-bulu', 'id')->firstOrFail()->update([
        'title' => ['id' => 'Sebelas tahun merantau.', 'en' => null],
        'body' => ['id' => '<p>Ia pulang untuk mengajar.</p>', 'en' => null],
    ]);

    $this->get('/en/stories/ibu-maria-bulu')
        ->assertOk()
        ->assertSee('Not yet available in English. Showing the Indonesian original.')
        ->assertSee('Ia pulang untuk mengajar.', escape: false);
});

it('shows no note when the story is translated', function () {
    $this->get('/en/stories/ibu-maria-bulu')
        ->assertOk()
        ->assertDontSee('Not yet available in English');
});

it('shows no note on the Indonesian page, which is the source language', function () {
    $this->get('/id/cerita/ibu-maria-bulu')
        ->assertOk()
        ->assertDontSee('Not yet available in English')
        ->assertDontSee('Belum tersedia');
});

it('shows no note when the body is empty in every locale rather than untranslated', function () {
    \App\Models\Post::whereSlug('ibu-maria-bulu', 'id')->firstOrFail()->update([
        'body' => ['id' => null, 'en' => null],
    ]);

    $this->get('/en/stories/ibu-maria-bulu')
        ->assertOk()
        ->assertDontSee('Not yet available in English');
});
