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
