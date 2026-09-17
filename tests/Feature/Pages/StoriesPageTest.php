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

// Story detail pages are deferred (spec §10), so every card points back at
// this index. The one thing that must not happen is a card linking to a
// route that doesn't exist — assert the href is the real, locale-correct URL.
it('gives every card a live, locale-correct href', function () {
    $this->get('/id/cerita')->assertSee('href="'.route('id.stories.index').'"', escape: false);
    $this->get('/en/stories')->assertSee('href="'.route('en.stories.index').'"', escape: false);
});
