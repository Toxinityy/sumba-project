<?php
// tests/Feature/Pages/GalleryPageTest.php

it('renders the gallery in both locales', function () {
    $this->get('/id/galeri')->assertOk()->assertSee('Sumba, difoto sepanjang tahun.');
    $this->get('/en/gallery')->assertOk()->assertSee('Sumba, photographed throughout the year.');
});

it('lists the photo essays, not the story profiles', function () {
    $html = $this->get('/id/galeri')->getContent();

    expect($html)->toContain('Panen bersama di Karuni')
        ->and($html)->toContain('Hari pertama tahun ajaran baru')
        // Profiles belong on /cerita, not here — PostData::photoEssays()
        // must actually filter, not just alias ::recent().
        ->and($html)->not->toContain('Sembilan kilometer');
});

it('never displays a numeric funding figure', function () {
    $html = $this->get('/id/galeri')->getContent();

    expect($html)->not->toContain('<progress')
        ->and($html)->not->toMatch('/Rp\s?[\d.,]+/');
});
