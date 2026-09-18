<?php
// tests/Feature/Pages/ProjectsPageTest.php

it('renders Projects in both locales, spine in order', function () {
    $html = $this->get('/id/proyek')->assertOk()->getContent();

    $positions = [];
    foreach (['Pekerjaan yang sedang berjalan.', 'Perpustakaan Karuni, tahap kedua.', 'sebelum pekerjaan dimulai', 'Tahap pembangunan, bukan target dana.', 'Mari mulai kemitraan'] as $needle) {
        $pos = strpos($html, $needle);
        expect($pos)->not->toBeFalse("expected to find [{$needle}]");
        $positions[] = $pos;
    }
    expect($positions)->toBe(collect($positions)->sort()->values()->all());

    $this->get('/en/projects')->assertOk()->assertSee('A construction stage, not a funding target.');
});

it('renders status as a qualitative sentence, never a number', function () {
    $html = $this->get('/id/proyek')->getContent();

    expect($html)->toContain(__('projects.detail.status', [], 'id'))
        ->and(__('projects.detail.status', [], 'id'))->not->toMatch('/\d/')
        ->and($html)->not->toContain('<progress')
        ->and($html)->not->toContain('role="progressbar"')
        ->and($html)->not->toMatch('/Rp\s?[\d.,]+/');
});
