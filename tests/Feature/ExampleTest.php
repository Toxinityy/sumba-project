<?php

test('the application returns a successful response', function () {
    // Root redirects to a locale-prefixed URL now (see LocaleRoutingTest).
    $response = $this->get('/', ['Accept-Language' => '']);

    $response->assertRedirect('/id');
});
