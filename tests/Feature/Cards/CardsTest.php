<?php
// tests/Feature/Cards/CardsTest.php

function cardImage(): array {
    return [
        'sources' => ['jpeg' => ['/img/s-800.jpg 800w']],
        'width' => 800,
        'height' => 1000,
        'alt' => 'Pupils in class',
    ];
}

it('renders a school card with its level badge and qualitative status', function () {
    $this->blade(
        '<x-cards.school href="/id/sekolah/karuni" level="TK" name="TK Harapan Karuni"
            location="Karuni, Sumba Barat Daya" need="A new reading room."
            status="Needs 4 more partners" :image="$image" />',
        ['image' => cardImage()]
    )->assertSee('TK')
     ->assertSee('TK Harapan Karuni')
     ->assertSee('Karuni, Sumba Barat Daya')
     ->assertSee('Needs 4 more partners');
});

it('never renders a progress bar or percentage on a school card', function () {
    $html = $this->blade(
        '<x-cards.school href="/x" level="SMP" name="N" location="L" need="Need."
            status="Needs 4 more partners" :image="$image" />',
        ['image' => cardImage()]
    )->__toString();

    // Spec decision 4. A bar frozen at 40% for six months damages credibility.
    expect($html)->not->toContain('<progress')
        ->and($html)->not->toContain('role="progressbar"')
        ->and($html)->not->toMatch('/\d+%/');
});

it('lets a status wrap instead of clipping longer Indonesian text', function () {
    $html = $this->blade(
        '<x-cards.school href="/x" level="SMA" name="N" location="L" need="Need."
            status="Sedang mencari mitra pendidik untuk tahun ajaran baru" :image="$image" />',
        ['image' => cardImage()]
    )->__toString();

    expect($html)->not->toContain('truncate')
        ->and($html)->not->toContain('whitespace-nowrap');
});

it('renders a story card with a portrait and a hook', function () {
    $this->blade(
        '<x-cards.story href="/id/cerita/rambu" name="Rambu"
            hook="She walked nine kilometres each morning." :image="$image" />',
        ['image' => cardImage()]
    )->assertSee('Rambu')
     ->assertSee('She walked nine kilometres each morning.')
     ->assertSee('aspect-[4/5]', escape: false);
});

it('shows an approximate conversion beside the rupiah cost on a tier', function () {
    $this->blade(
        '<x-cards.tier title="A classroom" cost="Rp 180.000.000"
            costApprox="approx. USD 11,000" description="One complete classroom."
            :image="$image" />',
        ['image' => cardImage()]
    )->assertSee('Rp 180.000.000')
     ->assertSee('approx. USD 11,000');
});
