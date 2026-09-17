<?php
// tests/Feature/Pages/GetInvolvedPageTest.php

it('renders the Get Involved page in both locales', function () {
    $this->get('/id/dukung')->assertOk()->assertSee('Ruang kelas');
    $this->get('/en/get-involved')->assertOk()->assertSee('A classroom');
});

it('shows an approximate USD figure only on the English locale', function () {
    $this->get('/id/dukung')->assertDontSee('approx. USD', escape: false);
    $this->get('/en/get-involved')->assertSee('approx. USD 11,000', escape: false);
});

// MINOR fix: this was named "shows all six tiers" but only asserted one
// tier title plus the three Ways headings. Now it actually checks all six.
it('shows all six tiers and the three partnership routes', function () {
    $this->get('/id/dukung')
        ->assertSee('Ruang kelas')
        ->assertSee('Laboratorium')
        ->assertSee('Gaji guru satu tahun')
        ->assertSee('Perpustakaan')
        ->assertSee('Dua puluh laptop')
        ->assertSee('Beasiswa satu murid')
        ->assertSee('Perusahaan')
        ->assertSee('Gereja')
        ->assertSee('Relawan');
});

// MINOR fix: this asserted the absence of a <progress> tag / role="progressbar"
// on a page with no component that has ever rendered either — no code path
// here could fail it, counterfeit coverage per the CLAUDE.md funding rule.
// The rule this page can actually violate is "status is qualitative, never a
// number" (docs/data-contract.md § School / SponsorshipTier) — assert that
// instead, against the real qualitative status string the page renders.
it('renders giving mechanics as a qualitative status, never a number', function () {
    $html = $this->get('/id/dukung')->getContent();

    expect($html)->not->toContain('<progress')
        ->and($html)->not->toContain('role="progressbar"')
        ->and($html)->toContain(__('give.how.status', [], 'id'))
        ->and(__('give.how.status', [], 'id'))->not->toMatch('/\d/');
});
