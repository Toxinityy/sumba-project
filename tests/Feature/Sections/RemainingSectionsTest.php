<?php
// tests/Feature/Sections/RemainingSectionsTest.php

it('leads the next step with the partnership action, not donate', function () {
    $html = $this->blade(
        '<x-sections.next-step heading="H" body="B" partnerHref="/id/kontak" giveHref="/id/dukung" />'
    )->__toString();

    // Spec §5: a CSR department cannot click Donate. It needs a proposal,
    // a budget line and a named contact.
    expect(strpos($html, '/id/kontak'))->toBeLessThan(strpos($html, '/id/dukung'));
});

it('offers both a partner and a give action', function () {
    $this->blade(
        '<x-sections.next-step heading="H" body="B" partnerHref="/id/kontak" giveHref="/id/dukung" />'
    )->assertSee('/id/kontak', escape: false)
     ->assertSee('/id/dukung', escape: false);
});

it('renders a facts list on the current need section', function () {
    $this->blade(
        '<x-sections.current-need heading="H" status="Needs 4 more partners" :facts="$facts">Body</x-sections.current-need>',
        ['facts' => [['key' => 'Opened', 'value' => '2009'], ['key' => 'Pupils', 'value' => '60']]]
    )->assertSee('Opened')
     ->assertSee('2009')
     ->assertSee('Needs 4 more partners');
});

it('lets buttons wrap rather than sizing them to English', function () {
    $html = $this->blade('<x-button href="/x">Bermitra dengan Kami</x-button>')->__toString();

    expect($html)->not->toContain('whitespace-nowrap')
        ->and($html)->not->toContain('truncate');
});

it('renders a directory grid of school cards', function () {
    $this->blade(
        '<x-sections.directory :schools="$schools" />',
        ['schools' => [[
            'href' => '/id/sekolah/karuni', 'level' => 'TK', 'name' => 'TK Harapan Karuni',
            'location' => 'Karuni', 'need' => 'A reading room.', 'status' => 'Needs 4 more partners',
            'image' => ['sources' => ['jpeg' => ['/i.jpg 800w']], 'width' => 800, 'height' => 1000, 'alt' => 'Pupils'],
        ]]]
    )->assertSee('TK Harapan Karuni');
});
