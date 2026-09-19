<?php
// tests/Feature/Pages/HomePageTest.php

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
