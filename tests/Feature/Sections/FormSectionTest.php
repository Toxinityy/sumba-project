<?php
// tests/Feature/Sections/FormSectionTest.php

it('renders a real label bound to each field by id', function () {
    $html = $this->blade(
        '<x-sections.form :fields="$fields" submitLabel="Send" />',
        ['fields' => [
            ['name' => 'name', 'label' => 'Full name', 'type' => 'text'],
            ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'rows' => 6],
        ]]
    )->__toString();

    expect($html)->toContain('<label for="name"')
        ->and($html)->toContain('id="name"')
        ->and($html)->toContain('<label for="message"')
        ->and($html)->toContain('id="message"')
        ->and($html)->toContain('<textarea')
        ->and($html)->toContain('rows="6"');
});

it('gives every field and the submit button a 44px touch target', function () {
    $html = $this->blade(
        '<x-sections.form :fields="$fields" submitLabel="Send" />',
        ['fields' => [['name' => 'email', 'label' => 'Email', 'type' => 'email']]]
    )->__toString();

    expect($html)->toContain('min-h-11')
        ->and($html)->toContain('type="submit"');
});

it('never sizes a label to fit the English text', function () {
    $html = $this->blade(
        '<x-sections.form :fields="$fields" submitLabel="Kirim"/>',
        ['fields' => [['name' => 'org', 'label' => 'Nama organisasi Anda (opsional)', 'type' => 'text']]]
    )->__toString();

    expect($html)->not->toContain('truncate')
        ->and($html)->not->toContain('whitespace-nowrap')
        ->and($html)->not->toMatch('/\bw-\[\d+px\]/');
});

it('has no baked-in action or method, so a bare render posts nowhere real', function () {
    $html = $this->blade(
        '<x-sections.form :fields="$fields" submitLabel="Send" />',
        ['fields' => [['name' => 'name', 'label' => 'Name', 'type' => 'text']]]
    )->__toString();

    expect($html)->toContain('<form')
        ->and($html)->not->toContain('action=')
        ->and($html)->not->toContain('method="POST"');
});

it('lets a caller wire a real action and method through attributes', function () {
    $html = $this->blade(
        '<x-sections.form :fields="$fields" submitLabel="Send" method="POST" action="/contact" />',
        ['fields' => [['name' => 'name', 'label' => 'Name', 'type' => 'text']]]
    )->__toString();

    expect($html)->toContain('action="/contact"')
        ->and($html)->toContain('method="POST"');
});

it('shows a caller-supplied validation error tied to the field with aria-describedby', function () {
    $this->withSession(['_old_input' => ['name' => '']])
        ->get('/');

    // Simulate an error bag the way a redirect-back-with-errors would supply it.
    view()->share('errors', new Illuminate\Support\ViewErrorBag(
        (new Illuminate\Support\MessageBag)->setFormat(':message')
    ));

    $bag = new Illuminate\Support\ViewErrorBag;
    $bag = $bag->put('default', new Illuminate\Support\MessageBag(['name' => 'The name field is required.']));
    view()->share('errors', $bag);

    $html = $this->blade(
        '<x-sections.form :fields="$fields" submitLabel="Send" />',
        ['fields' => [['name' => 'name', 'label' => 'Name', 'type' => 'text']]]
    )->__toString();

    expect($html)->toContain('aria-describedby="name-error"')
        ->and($html)->toContain('The name field is required.');
});
