<?php
// tests/Feature/Pages/HomePageTest.php

it('renders the home page in both locales', function () {
    $this->get('/id')->assertOk()->assertSee('Setiap anak berhak atas masa depan yang cerah.');
    $this->get('/en')->assertOk()->assertSee('Every child deserves a bright future.');
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
