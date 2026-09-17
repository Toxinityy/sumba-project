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

it('shows all six tiers and the three partnership routes', function () {
    $this->get('/id/dukung')
        ->assertSee('Beasiswa satu murid')
        ->assertSee('Perusahaan')
        ->assertSee('Gereja')
        ->assertSee('Relawan');
});

it('never renders a numeric progress indicator', function () {
    $html = $this->get('/id/dukung')->getContent();

    expect($html)->not->toContain('<progress')
        ->and($html)->not->toContain('role="progressbar"');
});
