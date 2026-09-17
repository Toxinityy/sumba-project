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

// MINOR fix: this checked for <progress>/role="progressbar"/a bare "Rp"
// figure on a page that has never had a code path producing any of the
// three — About renders no SchoolData, TierData or any other entity with a
// cost or status field, so the assertion could not fail before or after any
// real change to this page. It stays as a project-wide regression guard
// instead: About must keep containing zero currency-shaped or numeric
// funding markup, full stop, which is at least true today and would catch
// someone later wiring a tier or school fixture into this page by mistake.
it('never renders a numeric funding figure or progress indicator', function () {
    $html = $this->get('/id/tentang')->getContent();

    expect($html)->not->toContain('<progress')
        ->and($html)->not->toContain('role="progressbar"')
        ->and($html)->not->toMatch('/Rp\s?[\d.,]+/')
        ->and($html)->not->toContain('funding_goal')
        ->and($html)->not->toContain('amount_raised');
});
