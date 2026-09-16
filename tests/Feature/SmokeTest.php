<?php

// tests/Feature/SmokeTest.php

it('boots and redirects the root to the default locale', function () {
    // See LocaleRoutingTest for why the Accept-Language header must be
    // explicitly emptied to simulate a genuinely header-less request.
    $this->get('/', ['Accept-Language' => ''])->assertRedirect('/id');
});
