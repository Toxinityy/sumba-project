<?php
// tests/Feature/Pages/SafeguardingPageTest.php

it('renders the safeguarding page in both locales', function () {
    $this->get('/id/perlindungan-anak')->assertOk()->assertSee('Bagaimana kami melindungi anak-anak di situs ini.');
    $this->get('/en/safeguarding')->assertOk()->assertSee('How we protect the children on this site.');
});

// Spec §9 names four things this page must say publicly. A CSR reviewer
// looks for exactly these, so losing one in an edit should fail the suite.
it('states consent, EXIF stripping, withdrawal and a route to removal', function () {
    $this->get('/en/safeguarding')
        ->assertSee('requires written consent')
        ->assertSee('Location data is stripped')
        ->assertSee('withdrawn at any time')
        ->assertSee(url('/en').'#contact', escape: false);
});
