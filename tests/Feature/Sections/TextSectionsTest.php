<?php
// tests/Feature/Sections/TextSectionsTest.php

it('constrains lede prose to the 68ch measure', function () {
    $this->blade('<x-sections.lede heading="A heading">Body copy here.</x-sections.lede>')
        ->assertSee('max-w-prose', escape: false);
});

it('renders a stat value and its as-of date', function () {
    $this->blade(
        '<x-sections.stat-band :stats="$stats" />',
        ['stats' => [['value' => '612', 'label' => 'Children in school', 'asOf' => 'August 2026']]]
    )->assertSee('612')
     ->assertSee('Children in school')
     ->assertSee('August 2026');
});

it('sets stat numerals in tabular figures so columns align', function () {
    $this->blade(
        '<x-sections.stat-band :stats="$stats" />',
        ['stats' => [['value' => '14', 'label' => 'Schools', 'asOf' => '2026']]]
    )->assertSee('tabular-nums', escape: false);
});

it('renders no progress bar or percentage anywhere in a stat band', function () {
    $html = $this->blade(
        '<x-sections.stat-band :stats="$stats" />',
        ['stats' => [['value' => '14', 'label' => 'Schools', 'asOf' => '2026']]]
    )->__toString();

    // Spec decision 4: no numeric funding display, ever.
    expect($html)->not->toContain('<progress')
        ->and($html)->not->toContain('role="progressbar"')
        ->and($html)->not->toMatch('/\d+%/');
});

it('renders a quote with its attribution', function () {
    $this->blade('<x-sections.quote attribution="Maria Bulu">Words spoken.</x-sections.quote>')
        ->assertSee('Words spoken.')
        ->assertSee('Maria Bulu')
        ->assertSee('<blockquote', escape: false);
});
