<?php
// tests/Feature/Pages/StoriesPageTest.php

it('renders the stories index in both locales', function () {
    $this->get('/id/cerita')->assertOk()->assertSee('Orang-orang di balik angka.');
    $this->get('/en/stories')->assertOk()->assertSee('The people behind the numbers.');
});

it('lists every story, not just the three the home page shows', function () {
    $this->get('/en/stories')
        ->assertSee('Rambu')
        ->assertSee('Ibu Maria Bulu')
        ->assertSee('Umbu');
});

// Pass 4: story detail pages now exist (stories.show), so each card's href
// points at its own page rather than back at the index — assert the real,
// locale-correct per-post URL, and that visiting it actually renders.
it('gives every card a live, locale-correct href to its own detail page', function () {
    $this->get('/id/cerita')->assertSee('href="'.route('id.stories.show', 'rambu-sembilan-kilometer').'"', escape: false);
    $this->get('/en/stories')->assertSee('href="'.route('en.stories.show', 'rambu-sembilan-kilometer').'"', escape: false);

    $this->get(route('id.stories.show', 'rambu-sembilan-kilometer'))->assertOk()->assertSee('Sembilan kilometer');
});
