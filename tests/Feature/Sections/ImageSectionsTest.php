<?php
// tests/Feature/Sections/ImageSectionsTest.php

function fakeImage(): array {
    return [
        'sources' => ['jpeg' => ['/img/x-1600.jpg 1600w']],
        'width' => 1600,
        'height' => 900,
        'alt' => 'Children walking to school',
    ];
}

it('loads the hero image eagerly because it is the LCP element', function () {
    $this->blade(
        '<x-sections.hero heading="H" subhead="S" :image="$image" />',
        ['image' => fakeImage()]
    )->assertSee('loading="eager"', escape: false)
     ->assertSee('fetchpriority="high"', escape: false);
});

it('lazy-loads images below the fold', function () {
    $this->blade(
        '<x-sections.context label="L" heading="H" :image="$image">Body</x-sections.context>',
        ['image' => fakeImage()]
    )->assertSee('loading="lazy"', escape: false);
});

it('renders a dated caption on each half of an evidence pair', function () {
    $this->blade(
        '<x-sections.evidence :before="$before" :after="$after" />',
        [
            'before' => fakeImage() + ['caption' => 'March 2026 - unused room'],
            'after'  => fakeImage() + ['caption' => 'August 2026 - shelving in place'],
        ]
    )->assertSee('March 2026 - unused room')
     ->assertSee('August 2026 - shelving in place');
});

it('renders portraits in the 4:5 ratio the brief specifies', function () {
    $this->blade(
        '<x-sections.people :portraits="$portraits" />',
        ['portraits' => [fakeImage() + ['name' => 'Rambu']]]
    )->assertSee('aspect-[4/5]', escape: false);
});

it('carries the dignity rule where an author will see it', function () {
    $source = file_get_contents(
        resource_path('views/components/sections/context.blade.php')
    );

    // Spec §5: this rule belongs in the codebase, not only in a brief.
    expect(strtolower($source))->toContain('circumstance');
});
