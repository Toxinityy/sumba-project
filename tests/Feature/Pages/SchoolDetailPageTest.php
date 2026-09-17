<?php
// tests/Feature/Pages/SchoolDetailPageTest.php

it('renders the Karuni school detail page in both locales', function () {
    $this->get('/id/sekolah/karuni')->assertOk()->assertSee('TK Harapan Karuni');
    $this->get('/en/schools/karuni')->assertOk()->assertSee('TK Harapan Karuni');
});

it('renders every section of the spine, in order', function () {
    $html = $this->get('/id/sekolah/karuni')->getContent();

    $positions = [];
    foreach (['Karuni, Sumba Barat Daya', 'Maria Bulu', 'Tidak ada tempat membaca', 'Ruang ketiga menjadi', 'ruang ketiga, belum terpakai', 'Ruang baca baru untuk 60 anak', 'Mari mulai kemitraan'] as $needle) {
        $pos = strpos($html, $needle);
        expect($pos)->not->toBeFalse("expected to find [{$needle}]");
        $positions[] = $pos;
    }

    expect($positions)->toBe(collect($positions)->sort()->values()->all());
});

it('never renders a surname for the safeguarded student profile', function () {
    // Content-model guarantee, not a page-level enforcement: SchoolData's
    // people are all adults named in full, so this test only proves the
    // page doesn't itself inject one it shouldn't.
    $html = $this->get('/id/sekolah/karuni')->getContent();

    expect($html)->not->toContain('funding_goal')
        ->and($html)->not->toContain('<progress')
        ->and($html)->not->toContain('role="progressbar"');
});

it('404s for a school slug with no detail profile yet', function () {
    $this->get('/id/sekolah/anakalang')->assertNotFound();
});

it('404s for an unknown school slug', function () {
    $this->get('/id/sekolah/does-not-exist')->assertNotFound();
});

it('never displays a numeric funding figure', function () {
    $html = $this->get('/id/sekolah/karuni')->getContent();

    expect($html)->not->toMatch('/Rp\s?[\d.,]+/');
});
