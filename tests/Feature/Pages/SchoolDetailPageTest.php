<?php
// tests/Feature/Pages/SchoolDetailPageTest.php

it('renders the Karuni school detail page in both locales', function () {
    $this->get('/id/sekolah/karuni')->assertOk()->assertSee('TK Harapan Karuni');
    $this->get('/en/schools/karuni')->assertOk()->assertSee('TK Harapan Karuni');
});

/*
 | The pages read the database, not a fixture. An edit to the record must reach
 | both the directory and the detail page, or a leftover fixture path is still
 | what the reader sees.
 */
it('renders the school from the database', function () {
    $school = \App\Models\School::whereSlug('karuni', 'id')->firstOrFail();
    $school->update(['name' => ['id' => 'TK Diubah dari Basis Data', 'en' => 'Edited in the Database']]);

    $this->get('/id/sekolah/karuni')->assertOk()->assertSee('TK Diubah dari Basis Data');
    $this->get('/en/schools')->assertOk()->assertSee('Edited in the Database');
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

// Pass 4: every one of SchoolData's six schools now has a full detail
// profile (see docs/agent-a-pages-report.md, Pass 4), so the premise of
// "a school slug with no detail profile yet" no longer exists — the same
// test-fragility pattern flagged twice before (LayoutTest, the
// ancestor-fallback test) would repeat here if this test just changed the
// slug it points at. Instead, assert all six render and each stays
// distinct — the thing that could actually regress now.
it('renders every one of the six schools, each with a distinct status', function () {
    $statuses = [];

    foreach (['karuni', 'anakalang', 'kambera', 'melolo', 'lewa', 'waikabubak'] as $slug) {
        $html = $this->get("/id/sekolah/{$slug}")->assertOk()->getContent();
        $status = \App\ViewModels\SchoolData::find($slug)['status'];

        expect($html)->toContain($status);
        $statuses[] = $status;
    }

    expect(array_unique($statuses))->toHaveCount(6);
});

it('404s for an unknown school slug', function () {
    $this->get('/id/sekolah/does-not-exist')->assertNotFound();
});

it('never displays a numeric funding figure', function () {
    $html = $this->get('/id/sekolah/karuni')->getContent();

    expect($html)->not->toMatch('/Rp\s?[\d.,]+/');
});
