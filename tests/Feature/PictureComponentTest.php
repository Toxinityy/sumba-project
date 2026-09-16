<?php
// tests/Feature/PictureComponentTest.php

it('emits a source per available format, best first', function () {
    $view = $this->blade(
        '<x-picture :sources="$sources" :width="1600" :height="900" alt="A classroom" />',
        ['sources' => [
            'avif' => ['/img/a-800.avif 800w', '/img/a-1600.avif 1600w'],
            'webp' => ['/img/a-800.webp 800w', '/img/a-1600.webp 1600w'],
            'jpeg' => ['/img/a-800.jpg 800w', '/img/a-1600.jpg 1600w'],
        ]]
    );

    $view->assertSee('type="image/avif"', escape: false)
        ->assertSee('type="image/webp"', escape: false);

    expect(strpos($view->__toString(), 'image/avif'))
        ->toBeLessThan(strpos($view->__toString(), 'image/webp'));
});

it('always carries explicit dimensions to prevent layout shift', function () {
    $this->blade(
        '<x-picture :sources="$sources" :width="1600" :height="900" alt="A classroom" />',
        ['sources' => ['jpeg' => ['/img/a.jpg 1600w']]]
    )->assertSee('width="1600"', escape: false)
     ->assertSee('height="900"', escape: false);
});

it('lazy-loads by default', function () {
    $this->blade(
        '<x-picture :sources="$sources" :width="800" :height="600" alt="A classroom" />',
        ['sources' => ['jpeg' => ['/img/a.jpg 800w']]]
    )->assertSee('loading="lazy"', escape: false);
});

it('loads the LCP hero eagerly with high priority', function () {
    $this->blade(
        '<x-picture :sources="$sources" :width="1600" :height="900" alt="A classroom" :eager="true" />',
        ['sources' => ['jpeg' => ['/img/a.jpg 1600w']]]
    )->assertSee('loading="eager"', escape: false)
     ->assertSee('decoding="sync"', escape: false)
     ->assertSee('fetchpriority="high"', escape: false);
});

it('never adds fetchpriority when lazy', function () {
    $this->blade(
        '<x-picture :sources="$sources" :width="800" :height="600" alt="A classroom" />',
        ['sources' => ['jpeg' => ['/img/a.jpg 800w']]]
    )->assertDontSee('fetchpriority', escape: false)
     ->assertSee('decoding="async"', escape: false);
});

it('requires alt text', function () {
    $this->blade(
        '<x-picture :sources="$sources" :width="800" :height="600" alt="Pupils reading" />',
        ['sources' => ['jpeg' => ['/img/a.jpg 800w']]]
    )->assertSee('alt="Pupils reading"', escape: false);
});

it('passes non-ascii and apostrophes through alt verbatim without double-escaping', function () {
    $this->blade(
        '<x-picture :sources="$sources" :width="800" :height="600" :alt="$alt" />',
        [
            'sources' => ['jpeg' => ['/img/a.jpg 800w']],
            'alt' => "Anak-anak Sumba di sekolah desa",
        ]
    )->assertSee('alt="Anak-anak Sumba di sekolah desa"', escape: false);

    $this->blade(
        '<x-picture :sources="$sources" :width="800" :height="600" :alt="$alt" />',
        [
            'sources' => ['jpeg' => ['/img/a.jpg 800w']],
            'alt' => "Ibu's garden",
        ]
    )->assertSee('alt="Ibu&#039;s garden"', escape: false);
});

it('combines caller classes with the component default rather than replacing them', function () {
    $this->blade(
        '<x-picture :sources="$sources" :width="800" :height="600" alt="A classroom" class="h-full w-full object-cover" />',
        ['sources' => ['jpeg' => ['/img/a.jpg 800w']]]
    )->assertSee('block max-w-full h-auto h-full w-full object-cover', escape: false);
});

it('passes extra non-class attributes through to the img element', function () {
    $this->blade(
        '<x-picture :sources="$sources" :width="800" :height="600" alt="A classroom" data-testid="hero-image" />',
        ['sources' => ['jpeg' => ['/img/a.jpg 800w']]]
    )->assertSee('data-testid="hero-image"', escape: false);
});

it('throws when no jpeg fallback is provided', function () {
    try {
        $this->blade(
            '<x-picture :sources="$sources" :width="800" :height="600" alt="A classroom" />',
            ['sources' => ['avif' => ['/img/a.avif 800w']]]
        );
        $this->fail('Expected an exception when no jpeg source is provided.');
    } catch (\Throwable $e) {
        while ($e->getPrevious() !== null) {
            $e = $e->getPrevious();
        }
        expect($e)->toBeInstanceOf(InvalidArgumentException::class);
    }
});
