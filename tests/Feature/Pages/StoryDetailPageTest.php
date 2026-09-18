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
