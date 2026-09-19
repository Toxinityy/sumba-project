<?php
// tests/Feature/Pages/ContactPageTest.php

// MINOR fix: enquiryMails() used to be declared at the global (file) scope —
// a second test file anywhere in the suite declaring the same name would be
// a fatal redeclare. Namespacing it to this file scopes the function so it
// can't collide; PHP still resolves the unqualified call below inside this
// same namespace.
namespace Tests\Feature\Pages\ContactPageTest;

use Illuminate\Support\Facades\Mail;

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
