<?php
// tests/Feature/Pages/AboutPageTest.php

it('renders the About page in both locales', function () {
    $this->get('/id/tentang')->assertOk()->assertSee('Dimulai dari satu ruang kelas beratap seng.');
    $this->get('/en/about')->assertOk()->assertSee('It started with one classroom under a tin roof.');
});

it('walks the spine: story, founder, mission, vision, people', function () {
    $this->get('/en/about')
        ->assertSee('Our story')
        ->assertSee('Founder')
        ->assertSee('Mission &amp; vision', escape: false)
        ->assertSee('A Sumba that educates its own children.')
        ->assertSee('Reynold — Founder');
});

it('never renders a numeric funding figure', function () {
    $html = $this->get('/id/tentang')->getContent();

    expect($html)->not->toContain('<progress')
        ->and($html)->not->toContain('role="progressbar"')
        ->and($html)->not->toMatch('/Rp\s?[\d.,]+/');
});
