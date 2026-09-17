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

it('renders the contact page in both locales', function () {
    $this->get('/id/kontak')->assertOk()->assertSee('Mari bicara.');
    $this->get('/en/contact')->assertOk()->assertSee("Let's talk.");
});

it('posts to the enquiry route in the same locale', function () {
    $this->get('/id/kontak')->assertSee('action="'.route('id.contact.send').'"', escape: false);
    $this->get('/en/contact')->assertSee('action="'.route('en.contact.send').'"', escape: false);
});

// The submit button is the whole point of the page: a component that hard-codes
// type="button" silently turns this form into a no-op, because HTML keeps the
// FIRST of two duplicate attributes.
it('gives the form a real submit button', function () {
    $this->get('/en/contact')->assertSee('type="submit"', escape: false);
});

it('delivers a valid enquiry and replies to the sender, not the server', function () {
    $this->post('/en/contact', [
        'name' => 'Dina Prasetyo',
        'organisation' => 'Nusantara Foundation',
        'email' => 'dina@example.org',
        'message' => 'We would like a proposal for a three-year school partnership.',
    ])->assertRedirect()->assertSessionHas('contact.sent');

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
    $this->from('/en/contact')
        ->post('/en/contact', ['name' => '', 'email' => 'not-an-email', 'message' => ''])
        ->assertRedirect('/en/contact')
        ->assertSessionHasErrors(['name', 'email', 'message']);

    expect(enquiryMails())->toHaveCount(0);
});
