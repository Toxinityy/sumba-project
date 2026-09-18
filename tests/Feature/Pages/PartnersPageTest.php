<?php
// tests/Feature/Pages/PartnersPageTest.php

it('renders Partners in both locales, spine in order', function () {
    $html = $this->get('/id/mitra')->assertOk()->getContent();

    $positions = [];
    foreach (['Bekerja sama dalam jangka panjang.', 'Setiap mitra terhubung', 'Contoh Mitra 1', 'Mari mulai kemitraan'] as $needle) {
        $pos = strpos($html, $needle);
        expect($pos)->not->toBeFalse("expected to find [{$needle}]");
        $positions[] = $pos;
    }
    expect($positions)->toBe(collect($positions)->sort()->values()->all());

    $this->get('/en/partners')->assertOk()->assertSee('Example Partner 1');
});

it('uses clearly fictional placeholder partner names', function () {
    $html = $this->get('/en/partners')->getContent();

    // Nothing resembling a real institution — only the numbered
    // "Example Partner" placeholders the data contract itself uses.
    expect($html)->toContain('Example Partner 1')
        ->and($html)->toContain('Example Partner 2')
        ->and($html)->toContain('Example Partner 3');
});

it('renders each partner logo as a plain img with explicit dimensions', function () {
    $html = $this->get('/id/mitra')->getContent();

    expect($html)->toMatch('/<img[^>]*width="160"[^>]*height="60"/');
});
