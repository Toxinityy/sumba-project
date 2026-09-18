<?php
// tests/Feature/Pages/ImpactPageTest.php

it('renders Impact in both locales, spine in order', function () {
    $html = $this->get('/id/dampak')->assertOk()->getContent();

    $positions = [];
    foreach (['Perubahan yang bisa diverifikasi.', '612', 'Rambu', 'sebelum renovasi', 'Mari mulai kemitraan'] as $needle) {
        $pos = strpos($html, $needle);
        expect($pos)->not->toBeFalse("expected to find [{$needle}]");
        $positions[] = $pos;
    }
    expect($positions)->toBe(collect($positions)->sort()->values()->all());

    $this->get('/en/impact')->assertOk()->assertSee('Change that can be verified.');
});

it('dates both sides of the evidence pair', function () {
    $html = $this->get('/id/dampak')->getContent();

    expect($html)->toContain('Januari 2026')
        ->and($html)->toContain('Agustus 2026');
});

it('never displays a numeric funding figure', function () {
    $html = $this->get('/id/dampak')->getContent();

    expect($html)->not->toContain('<progress')
        ->and($html)->not->toMatch('/Rp\s?[\d.,]+/');
});
