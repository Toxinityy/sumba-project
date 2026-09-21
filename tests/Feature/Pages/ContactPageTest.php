<?php
// tests/Feature/Pages/ContactPageTest.php

// MINOR fix: enquiryMails() used to be declared at the global (file) scope —
// a second test file anywhere in the suite declaring the same name would be
// a fatal redeclare. Namespacing it to this file scopes the function so it
// can't collide; PHP still resolves the unqualified call below inside this
// same namespace.
namespace Tests\Feature\Pages\ContactPageTest;

use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;

/*
 | Mail::fake() is deliberately NOT used here: MailFake::raw() is a no-op, so
 | every assertion against it would pass whether or not the route sent
 | anything. phpunit.xml already sets MAIL_MAILER=array, whose transport keeps
 | the real message — recipient, reply-to and body included.
 */
function enquiryMails(): \Illuminate\Support\Collection
{
    return Mail::mailer()->getSymfonyTransport()->messages();
}

// Contact is no longer a page: the form closes the landing page, under an
// anchor named after the locale's old contact segment.
it('renders the contact form at the end of the landing page in both locales', function () {
    $id = $this->get('/id')->assertOk()->getContent();
    $en = $this->get('/en')->assertOk()->getContent();

    expect($id)->toContain('id="kontak"')->toContain('Mari bicara.')
        ->and($en)->toContain('id="contact"')->toContain('Let&#039;s talk.')
        // Last on the page: after the Next step section that links to it.
        ->and(strpos($id, 'id="kontak"'))->toBeGreaterThan(strpos($id, __('nextstep.heading', [], 'id')));
});

it('permanently redirects the old contact URLs to the landing-page anchor', function () {
    $this->get('/id/kontak')->assertStatus(301)->assertRedirect(url('/id').'#kontak');
    $this->get('/en/contact')->assertStatus(301)->assertRedirect(url('/en').'#contact');
});

it('replaces Contact with Home in the nav, and points Partner at the anchor', function () {
    preg_match('#<nav[^>]*>(.*?)</nav>#s', $this->get('/id/sekolah')->getContent(), $nav);

    expect($nav[1])->toContain('href="'.url('/id').'"')->toContain('Beranda')
        ->not->toContain('Kontak')
        ->and(strpos($nav[1], 'Beranda'))->toBeLessThan(strpos($nav[1], 'Sekolah'));

    $en = $this->get('/en/schools')->getContent();
    preg_match('#<nav[^>]*>(.*?)</nav>#s', $en, $nav);

    expect($nav[1])->toMatch('#href="'.preg_quote(url('/en'), '#').'"[^>]*>\s*Home\s*</a>#')
        ->not->toContain('Contact')
        ->and($en)->toContain('href="'.url('/en').'#contact"');
});

it('posts to the enquiry route in the same locale', function () {
    $this->get('/id')->assertSee('action="'.route('id.contact.send').'"', escape: false);
    $this->get('/en')->assertSee('action="'.route('en.contact.send').'"', escape: false);
});

// The submit button is the whole point of the page: a component that hard-codes
// type="button" silently turns this form into a no-op, because HTML keeps the
// FIRST of two duplicate attributes.
it('gives the form a real submit button', function () {
    $this->get('/en')->assertSee('type="submit"', escape: false);
});

it('delivers a valid enquiry and replies to the sender, not the server', function () {
    $this->post('/en/contact', [
        'name' => 'Dina Prasetyo',
        'organisation' => 'Nusantara Foundation',
        'email' => 'dina@example.org',
        'message' => 'We would like a proposal for a three-year school partnership.',
    ])->assertRedirect(url('/en').'#contact')->assertSessionHas('contact.sent');

    expect(enquiryMails())->toHaveCount(1);

    $mail = enquiryMails()->first()->getOriginalMessage();

    expect($mail->getTo()[0]->getAddress())->toBe(config('mail.contact_to'))
        ->and($mail->getReplyTo()[0]->getAddress())->toBe('dina@example.org')
        ->and($mail->getTextBody())->toContain('Nusantara Foundation')
        ->and($mail->getTextBody())->toContain('three-year school partnership');
});

it('rejects a submission that fills the honeypot', function () {
    $this->post('/en/contact', [
        'name' => 'Bot',
        'email' => 'bot@example.org',
        'message' => 'buy things',
        'website' => 'http://spam.example',
    ])->assertStatus(422);

    expect(enquiryMails())->toHaveCount(0);
});

it('requires a name, a valid email and a message', function () {
    // Back to the form's anchor, not the top of the landing page.
    $this->from('/en')
        ->post('/en/contact', ['name' => '', 'email' => 'not-an-email', 'message' => ''])
        ->assertRedirect(url('/en').'#contact')
        ->assertSessionHasErrors(['name', 'email', 'message']);

    expect(enquiryMails())->toHaveCount(0);
});

it('returns Indonesian validation feedback at the first invalid field', function () {
    $this->post('/id/kontak', [
        'name' => '',
        'organisation' => 'Yayasan Pendidikan',
        'email' => 'bukan-surel',
        'message' => '',
    ])->assertRedirect(url('/id').'#kontak')
        ->assertSessionHasErrors(['name', 'email', 'message'])
        ->assertSessionHasInput('organisation', 'Yayasan Pendidikan');

    $html = $this->withCookie(config('session.cookie'), session()->getId())
        ->get('/id')->assertOk()->getContent();

    expect($html)->toContain('Nama wajib diisi.', 'Masukkan alamat surel yang valid.')
        ->toMatch('/<input[^>]*id="name"[^>]*autofocus[^>]*>/s')
        ->toContain('aria-invalid="true" aria-describedby="name-error"');
});

it('explains an overlong English enquiry and focuses its message field', function () {
    $this->post('/en/contact', [
        'name' => 'Dina',
        'email' => 'dina@example.org',
        'message' => str_repeat('x', 5001),
    ])->assertRedirect(url('/en').'#contact')->assertSessionHasErrors('message');

    $html = $this->withCookie(config('session.cookie'), session()->getId())
        ->get('/en')->assertOk()->getContent();

    expect($html)->toContain('Message must be 5000 characters or fewer.')
        ->toMatch('/<textarea[^>]*id="message"[^>]*autofocus[^>]*>/s');
});

it('preserves the enquiry and offers localized recovery when delivery fails', function (string $locale, string $path, string $anchor) {
    Mail::mailer()->setSymfonyTransport(new class implements TransportInterface
    {
        public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
        {
            throw new TransportException('Simulated SMTP outage');
        }

        public function __toString(): string
        {
            return 'failing-test-transport';
        }
    });

    $this->post($path, [
        'name' => 'Dina', 'organisation' => 'School partners',
        'email' => 'dina@example.org', 'message' => 'Please send a school proposal.',
        'website' => '', 'unrelated' => 'do not retain',
    ])->assertRedirect(url("/{$locale}").$anchor)
        ->assertSessionHasErrors(['contact' => __('contact.form.failed', [], $locale)])
        ->assertSessionHasInput('message', 'Please send a school proposal.')
        ->assertSessionHasInput('name', 'Dina')
        ->assertSessionHasInput('organisation', 'School partners')
        ->assertSessionHasInput('email', 'dina@example.org')
        ->assertSessionMissing('_old_input.unrelated')
        ->assertSessionMissing('_old_input.website')
        ->assertSessionMissing('contact.sent');

    // Carry the redirect's session into the GET, as a browser does.
    $this->withCookie(config('session.cookie'), session()->getId())->get("/{$locale}")->assertOk()
        ->assertSee('role="alert"', false)
        ->assertSee('mailto:'.config('mail.contact_to'), false)
        ->assertSee(__('contact.form.failed', [], $locale))
        ->assertSee('Please send a school proposal.');
})->with([
    ['id', '/id/kontak', '#kontak'],
    ['en', '/en/contact', '#contact'],
]);

it('returns a throttled browser enquiry to its form without discarding text', function (string $locale, string $path, string $anchor) {
    $fields = ['name' => 'Dina', 'email' => 'dina@example.org', 'message' => 'Please send a school proposal.'];
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post($path, $fields)->assertRedirect();
    }

    $response = $this->post($path, $fields + ['unrelated' => 'do not retain']);
    $response->assertRedirect(url("/{$locale}").$anchor)
        ->assertHeader('Retry-After')
        ->assertSessionHasErrors('contact')
        ->assertSessionHasInput('message', $fields['message'])
        ->assertSessionMissing('_old_input.unrelated')
        ->assertSessionMissing('contact.sent');
    $seconds = $response->headers->get('Retry-After');
    expect((int) $seconds)->toBeGreaterThan(0)->toBeLessThanOrEqual(60);
    expect(enquiryMails())->toHaveCount(5);
    $this->withCookie(config('session.cookie'), session()->getId())->get("/{$locale}")->assertOk()
        ->assertSee(__('contact.form.throttled', ['seconds' => $seconds], $locale))
        ->assertSee('mailto:'.config('mail.contact_to'), false)
        ->assertSee($fields['message']);
})->with([
    ['id', '/id/kontak', '#kontak'],
    ['en', '/en/contact', '#contact'],
]);

it('keeps the JSON rate-limit status and retry header across locales', function () {
    $fields = ['name' => 'Dina', 'email' => 'dina@example.org', 'message' => 'Please send a school proposal.'];
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post('/en/contact', $fields)->assertRedirect();
    }
    $this->postJson('/id/kontak', $fields)->assertStatus(429)
        ->assertHeader('Retry-After')->assertJsonStructure(['message']);
    expect(enquiryMails())->toHaveCount(5);
});
