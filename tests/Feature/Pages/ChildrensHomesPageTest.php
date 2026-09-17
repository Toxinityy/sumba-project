<?php
// tests/Feature/Pages/ChildrensHomesPageTest.php

it('renders the Children\'s Homes page in both locales', function () {
    $this->get('/id/rumah-anak')->assertOk()->assertSee('Rumah, bukan asrama.');
    $this->get('/en/childrens-homes')->assertOk()->assertSee('A home, not an institution.');
});

it('links to the safeguarding page in the reader\'s own locale', function () {
    $this->get('/id/rumah-anak')->assertSee('/id/perlindungan-anak', escape: false);
    $this->get('/en/childrens-homes')->assertSee('/en/safeguarding', escape: false);
});

// Spec §9: residential care is the stricter tier — the site never names a
// child in a home. The page has no child names to assert the absence of, so
// this asserts the rule the page must keep stating instead.
it('states that children in the homes are never named', function () {
    $this->get('/en/childrens-homes')->assertSee('never named on this site');
});

// I1: this is the highest-stakes rule in the project — a resident child's
// first name or the reason they are in care must NEVER appear on this page
// — and it was previously guarded only by the paragraph above, which stays
// true even if an editor pastes a child's given name into homes.* in
// lang/id.json or lang/en.json. These are negative assertions against the
// rendered page itself, so that regression is caught here, not discovered
// in production. If this test breaks, it means someone added exactly the
// content it exists to prevent — check the diff to lang/id.json / en.json
// for a resident child's name or a sentence explaining why a child is in
// care, do not just adjust the assertion.
it('never names a resident child or states why any child is in care', function () {
    // The two house-parent full names ARE permitted in full (adults may be
    // named with their role) — see docs/data-contract.md § Home and § Post.
    // Every OTHER occurrence of a Sumbanese given name used elsewhere on
    // this site as a resident/subject name must not appear on this page.
    $allowedFullNames = [
        'Bapak & Ibu Umbu Deta',
        'Bapak &amp; Ibu Umbu Deta', // Blade escapes "&" on output
        'Bapak & Ibu Ndara Kaka',
        'Bapak &amp; Ibu Ndara Kaka',
    ];

    // Extend this list as new Sumbanese given names are used elsewhere in
    // the site's content (post subjects, fixture data) — it exists so that
    // if any of them EVER shows up on this page outside a house-parent's
    // full name, the test fails.
    $givenNames = ['Rambu', 'Umbu', 'Tamu', 'Kahi', 'Ledu', 'Wangi', 'Deni', 'Yuni'];

    $reasonForCareVocabulary = [
        'orphan', 'orphaned', 'abandoned', 'yatim', 'piatu',
        'ditinggalkan', 'ditelantarkan', 'parents died', 'orang tuanya meninggal',
    ];

    foreach (['/id/rumah-anak', '/en/childrens-homes'] as $url) {
        $html = $this->get($url)->getContent();

        $stripped = str_replace($allowedFullNames, '', $html);

        foreach ($givenNames as $name) {
            expect(preg_match('/\b'.preg_quote($name, '/').'\b/i', $stripped))
                ->toBe(0, "Child-protection rule violated on {$url}: the given name "
                    ."\"{$name}\" appears on the Children's Homes page outside a "
                    .'house-parent full name. Children resident in a home are never '
                    .'named on this site (spec §9) — check for a name pasted into '
                    ."homes.* in lang/id.json or lang/en.json.");
        }

        foreach ($reasonForCareVocabulary as $phrase) {
            expect(stripos($html, $phrase))
                ->toBe(false, "Child-protection rule violated on {$url}: the phrase "
                    ."\"{$phrase}\" appears on the Children's Homes page. The site "
                    .'never states why a child is in care (spec §9) — check for an '
                    ."added sentence explaining a resident child's circumstances in "
                    .'homes.* in lang/id.json or lang/en.json.');
        }
    }
});
