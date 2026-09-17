<?php
// tests/Feature/Sections/WaysSectionTest.php

it('renders three audience-segmented columns', function () {
    $this->blade(
        '<x-sections.ways :ways="$ways" />',
        ['ways' => [
            ['heading' => 'Companies', 'body' => 'Long-term CSR programmes.'],
            ['heading' => 'Churches', 'body' => 'Congregational partnership.'],
            ['heading' => 'Volunteers', 'body' => 'Teacher training and construction.'],
        ]]
    )->assertSee('Companies')
     ->assertSee('Churches')
     ->assertSee('Volunteers')
     ->assertSee('Congregational partnership.');
});

it('stacks the ways columns to one on mobile with no fixed widths', function () {
    $html = $this->blade(
        '<x-sections.ways :ways="$ways" />',
        ['ways' => [['heading' => 'H', 'body' => 'B']]]
    )->__toString();

    // grid-cols-3 is gated behind md:, so mobile (no breakpoint match) gets
    // the implicit single-column grid default.
    expect($html)->toContain('md:grid-cols-3')
        ->and($html)->not->toContain('whitespace-nowrap')
        ->and($html)->not->toContain('truncate')
        ->and($html)->not->toMatch('/\bw-\[\d+px\]/');
});

it('renders an optional label and heading above the columns', function () {
    $this->blade(
        '<x-sections.ways label="L" heading="H" :ways="$ways" />',
        ['ways' => [['heading' => 'Companies', 'body' => 'B']]]
    )->assertSee('L')
     ->assertSee('H');
});
