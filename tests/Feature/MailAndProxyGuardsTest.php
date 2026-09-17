<?php
// tests/Feature/MailAndProxyGuardsTest.php
//
// C1: a boot-time guard that refuses to serve in production when the mail
// setup is still the placeholder — see App\Providers\AppServiceProvider.
// I2: proves the client IP is read from the forwarded header now that
// bootstrap/app.php trusts the Cloudflare edge as a proxy.

use App\Providers\AppServiceProvider;

it('refuses to boot in production while CONTACT_TO is still the placeholder', function () {
    config(['mail.contact_to' => 'halo@contoh.org', 'mail.default' => 'smtp']);
    app()['env'] = 'production';

    expect(fn () => (new AppServiceProvider(app()))->boot())
        ->toThrow(RuntimeException::class, 'CONTACT_TO');
});

it('refuses to boot in production while MAIL_MAILER is still log', function () {
    config(['mail.contact_to' => 'real@hopeforsumba.org', 'mail.default' => 'log']);
    app()['env'] = 'production';

    expect(fn () => (new AppServiceProvider(app()))->boot())
        ->toThrow(RuntimeException::class, 'MAIL_MAILER');
});

it('boots fine in production once both are configured for real', function () {
    config(['mail.contact_to' => 'real@hopeforsumba.org', 'mail.default' => 'smtp']);
    app()['env'] = 'production';

    (new AppServiceProvider(app()))->boot();
})->throwsNoExceptions();

it('does not guard outside production, even with the placeholder values', function () {
    config(['mail.contact_to' => 'halo@contoh.org', 'mail.default' => 'log']);
    // Default testing environment is not "production".

    (new AppServiceProvider(app()))->boot();
})->throwsNoExceptions();

it('reads the client IP from the X-Forwarded-For header, not the proxy socket', function () {
    // Register a throwaway route through the real HTTP kernel, so the
    // global TrustProxies middleware (configured via trustProxies(at: '*')
    // in bootstrap/app.php) actually runs. Without that trust, Laravel
    // would report $request->ip() as the proxy's own socket address
    // (REMOTE_ADDR) for every visitor — the Cloudflare edge IP in
    // production — which is exactly what makes throttle:5,1 on the contact
    // route a self-inflicted lockout for every genuine enquirer.
    \Illuminate\Support\Facades\Route::get('/__test-client-ip', fn () => request()->ip());

    $response = $this->withServerVariables([
        'REMOTE_ADDR' => '10.0.0.1', // the "proxy" — must NOT be reported as the client
    ])->get('/__test-client-ip', ['X-Forwarded-For' => '203.0.113.7']);

    $response->assertOk()->assertSee('203.0.113.7');
});
