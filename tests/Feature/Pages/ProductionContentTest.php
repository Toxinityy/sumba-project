<?php

it('keeps deferred destinations and prototype evidence out of production', function () {
    app()['env'] = 'production';

    foreach (['id', 'en'] as $locale) {
        $html = $this->get("/{$locale}")->assertOk()->getContent();

        foreach (['gallery.index', 'partners', 'impact', 'projects'] as $name) {
            $url = route("{$locale}.{$name}");
            $this->get($url)->assertNotFound();
            expect($html)->not->toContain('href="'.$url.'"');
        }

        foreach (['ed-ledger', 'ed-stat--big', 'ed-evid', 'ed-partners', '612'] as $previewContent) {
            expect($html)->not->toContain($previewContent);
        }
        foreach (['fourteen', 'empat belas'] as $unverifiedTotal) {
            expect(strtolower($html))->not->toContain($unverifiedTotal);
            expect(strtolower($this->get(route("{$locale}.about"))->assertOk()->getContent()))
                ->not->toContain($unverifiedTotal);
        }
        expect($html)->toContain('href="'.route("{$locale}.schools.index").'"')
            ->toContain('href="'.route("{$locale}.stories.index").'"')
            ->toContain('action="'.route("{$locale}.contact.send").'"');

        $directory = $this->get(route("{$locale}.schools.index"))->assertOk()->getContent();
        expect($directory)->not->toMatch('/(?:Butuh|Perlu)\s+\d+\s+mitra|Needs\s+\d+\s+(?:corporate\s+)?partners/i');
    }

    $this->get('/gallery')->assertNotFound();
});

it('keeps prototype pages available for non-production review', function () {
    $this->get('/en/impact')->assertOk();
    $this->get('/en')->assertOk()->assertSee('ed-stat--big', false);
});
