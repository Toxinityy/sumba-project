<?php
// tests/Feature/LocaleConfigTest.php
//
// config/app.php ('locale') and config/locales.php ('default') used to be
// two independent sources of truth that could disagree — see docs/deployment.md's
// note on APP_LOCALE. This asserts they can't drift apart again, plus the
// user-facing symptom that disagreement caused: <html lang> not matching the
// URL's locale prefix.

it('single-sources the default locale from config/locales.php', function () {
    expect(config('app.locale'))->toBe(config('locales.default'));
});

it('emits lang="id" for a page under the /id/ prefix', function () {
    $this->get('/id/sekolah')->assertSee('lang="id"', escape: false);
});

it('emits lang="en" for a page under the /en/ prefix', function () {
    $this->get('/en/schools')->assertSee('lang="en"', escape: false);
});
