<?php
// tests/Feature/Pages/SchoolsDirectoryPageTest.php

it('renders the schools directory in both locales', function () {
    $this->get('/id/sekolah')->assertOk()->assertSee('TK Harapan Karuni');
    $this->get('/en/schools')->assertOk()->assertSee('TK Harapan Karuni');
});

it('links every card to a real, locale-correct school detail url', function () {
    $this->get('/id/sekolah')
        ->assertSee('/id/sekolah/karuni', escape: false)
        ->assertSee('/id/sekolah/anakalang', escape: false);
});

it('never renders a numeric progress indicator or funding figure', function () {
    $html = $this->get('/id/sekolah')->getContent();

    expect($html)->not->toContain('<progress')
        ->and($html)->not->toContain('role="progressbar"')
        ->and($html)->not->toMatch('/Rp\s?[\d.,]+/');
});
