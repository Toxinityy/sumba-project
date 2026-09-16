<?php
// tests/Feature/SmokeTest.php

it('boots and serves a response', function () {
    $this->get('/')->assertStatus(200);
});
