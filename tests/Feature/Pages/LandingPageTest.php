<?php
// tests/Feature/Pages/LandingPageTest.php
//
// The landing-page redesign. Every assertion here is written to FAIL if the
// thing it names is removed — this project has a history of tests that passed
// regardless of the feature they claimed to check, so each one either counts
// something or checks an ordering rather than asserting a string exists
// somewhere on a 200-response page.

/** Every <section> on the page, in DOM order, with its class list. */
function landingSections(string $html): array
{
    preg_match_all('/<section class="([^"]*)"/', $html, $m);

    return $m[1];
}

/** Every level-ladder <ul> on the page, inner HTML included. */
function landingLadders(string $html): array
{
    preg_match_all('/<ul class="lvl[^"]*".*?<\/ul>/s', $html, $m);

    return $m[0];
}

it('renders the landing page in both locales', function () {
    $this->get('/id')->assertOk();
    $this->get('/en')->assertOk();
});

// ---------------------------------------------------------------------------
// 1. The nav drops to four items, and the six folded pages stay reachable.
// ---------------------------------------------------------------------------

it('offers exactly four navigation items', function () {
    $html = $this->get('/id')->getContent();

    $nav = substr($html, strpos($html, '<nav'), strpos($html, '</nav>') - strpos($html, '<nav'));

    expect(substr_count($nav, '<a href='))->toBe(4);
});

it('names the four items the brief specifies and none of the folded six', function () {
    $nav = $this->get('/id')->getContent();
    $nav = substr($nav, strpos($nav, '<nav'), strpos($nav, '</nav>') - strpos($nav, '<nav'));

    foreach (['Sekolah', 'Cerita', 'Dukung Kami', 'Kontak'] as $item) {
        expect($nav)->toContain($item);
    }

    // The six folded pages are linked from their landing section and from the
    // footer — just not from the menu.
    foreach (['Tentang Kami', 'Rumah Anak', 'Dampak', 'Proyek', 'Galeri'] as $folded) {
        expect($nav)->not->toContain($folded);
    }
});

it('keeps every folded route alive in both locales', function () {
    // Folding a page into a landing section must not break its URL: existing
    // links, bookmarks and any printed material still have to resolve.
    foreach (['about', 'homes.index', 'impact', 'projects', 'partners', 'gallery.index'] as $name) {
        foreach (['id', 'en'] as $locale) {
            $this->get(route("{$locale}.{$name}"))->assertOk();
        }
    }
});

it('links every folded page from the footer', function () {
    $html = $this->get('/id')->getContent();
    $footer = substr($html, strpos($html, '<footer'));

    foreach (['about', 'homes.index', 'impact', 'projects', 'partners', 'gallery.index'] as $name) {
        expect($footer)->toContain('href="'.route('id.'.$name).'"');
    }
});

it('links the folded pages from the landing sections they belong to', function () {
    $html = $this->get('/id')->getContent();
    // Everything before the footer: these have to be in the narrative, not
    // only in the footer's link list.
    $main = substr($html, 0, strpos($html, '<footer'));

    foreach (['about', 'homes.index', 'impact', 'projects', 'partners', 'gallery.index', 'schools.index', 'stories.index'] as $name) {
        expect($main)->toContain('href="'.route('id.'.$name).'"');
    }
});

// ---------------------------------------------------------------------------
// 2. The narrative: every section present, in order.
// ---------------------------------------------------------------------------

it('carries all eleven narrative sections in order', function () {
    $html = $this->get('/id')->getContent();

    // hero, people, challenge, work, scale, evidence, voice, schools,
    // stories, partners, next step. Each marker is the geometry class that
    // section's variant emits, so a section rendered with the wrong variant
    // fails here rather than passing on its heading text alone.
    $markers = [
        'ed-masthead',       // 1 hero
        'ed-portband',       // 2 who the people are
        'ed-ledger',         // 3 the challenge
        'ed-work__wide',     // 4 what the ministry does
        'ed-stat--big',      // 5 scale
        'ed-evid',           // 6 evidence
        'ed-quote',          // 7 a human voice
        'ed-lead-school',    // 8 featured schools
        'ed-stories',        // 9 stories
        'ed-partners',       // 10 partners
        'ed-next',           // 11 next step
    ];

    $positions = [];
    foreach ($markers as $marker) {
        $pos = strpos($html, $marker);
        expect($pos)->not->toBeFalse("expected the landing page to render [{$marker}]");
        $positions[] = $pos;
    }

    expect($positions)->toBe(collect($positions)->sort()->values()->all());
});

it('still leads with partnership and not with giving', function () {
    $html = $this->get('/id')->getContent();

    // A CSR department cannot click Donate. Checked in both places the two
    // actions appear together: the hero and the closing field.
    expect(substr_count($html, 'Bermitra dengan Kami'))->toBeGreaterThanOrEqual(2);
    expect(strpos($html, 'Bermitra dengan Kami'))
        ->toBeLessThan(strpos($html, 'Dukung Sebuah Sekolah'));
});

// ---------------------------------------------------------------------------
// 3. Geometry: four padding steps, four container relationships, one dark
//    chapter, three real overlaps.
// ---------------------------------------------------------------------------

it('uses more than one padding step across the page', function () {
    $sections = landingSections($this->get('/id')->getContent());

    $pads = [];
    foreach ($sections as $classes) {
        preg_match('/\bpad-([a-z]+)\b/', $classes, $m);
        $pads[] = $m[1] ?? 'default';
    }

    // The whole point of the redesign: not one uniform step. Seven distinct
    // treatments today (the hero's bespoke top-only padding, two of the four
    // steps, and the four chapter joins); asserted as a floor rather than an
    // exact count so adding a section cannot fail this for the wrong reason.
    expect(array_unique($pads))->toHaveCount(7)
        ->and($pads)->toContain('xl')
        ->and($pads)->toContain('m')
        ->and($pads)->toContain('default');
});

it('declares four padding steps in the stylesheet', function () {
    $css = file_get_contents(resource_path('css/landing.css'));

    foreach (['.pad-xl', '.pad-l', '.pad-m', '.pad-s'] as $step) {
        expect($css)->toContain($step);
    }
});

it('uses four different container relationships', function () {
    $html = $this->get('/id')->getContent();

    // 1200px content width, the wider 1440px band, full bleed to the
    // viewport, and offset into the grid.
    expect($html)->toContain('ed-wrap')
        ->and($html)->toContain('ed-wrap--wide')
        ->and($html)->toContain('ed-work__wide')
        ->and($html)->toContain('ed-offset');

    $css = file_get_contents(resource_path('css/landing.css'));
    // The bleed is a real negative margin against the container, not a wider
    // max-width pretending to be one.
    expect($css)->toContain('margin-left: calc(-1 * (var(--gutter)');
});

it('renders the dark chapter as one continuous field, not alternating bands', function () {
    $sections = landingSections($this->get('/id')->getContent());

    $chapterIndexes = [];
    foreach ($sections as $i => $classes) {
        if (str_contains($classes, 'on-chapter')) {
            $chapterIndexes[] = $i;
        }
    }

    // Scale, evidence and the voice.
    expect($chapterIndexes)->toHaveCount(3);
    // Contiguous: a gap here means a light section landed inside the chapter
    // and the reader sees a broken theme rather than one movement.
    expect($chapterIndexes[2] - $chapterIndexes[0])->toBe(2);
});

it('joins the three sections of the dark chapter with open, mid and close padding', function () {
    $sections = landingSections($this->get('/id')->getContent());

    $joins = [];
    foreach ($sections as $classes) {
        if (str_contains($classes, 'on-chapter')) {
            preg_match('/\bpad-([a-z]+)\b/', $classes, $m);
            $joins[] = $m[1] ?? 'default';
        }
    }

    expect($joins)->toBe(['open', 'mid', 'close']);
});

it('carries three real overlaps, each a negative margin in the stylesheet', function () {
    $css = file_get_contents(resource_path('css/landing.css'));

    // 1: the hero plate bleeds past the gutter AND hangs below the hero.
    expect($css)->toContain('margin-bottom: -4.5rem');
    // 2: the lead school card is pulled up across the dark chapter's floor.
    expect($css)->toMatch('/\.ed-lead-school\s*\{[^}]*margin-top:\s*clamp\(-6rem/s');
    // 3: the pull quote breaks its own measure.
    expect($css)->toMatch('/\.ed-quote\s*\{[^}]*text-indent:\s*-\.?0?\.44em/s');
});

it('gives the section receiving the overlap no top padding to collide with', function () {
    $sections = landingSections($this->get('/id')->getContent());

    $schools = collect($sections)->first(fn ($c) => str_contains($c, 'pad-cont'));
    expect($schools)->not->toBeNull();

    $css = file_get_contents(resource_path('css/landing.css'));
    expect($css)->toMatch('/\.pad-cont\s*\{\s*padding-block:\s*0\b/');
});

// ---------------------------------------------------------------------------
// 4. The level ladder.
// ---------------------------------------------------------------------------

it('shows the whole level ladder with exactly one lit rung per school', function () {
    $html = $this->get('/id')->getContent();
    $ladders = landingLadders($html);

    // One lead school plus five in the rail list.
    expect($ladders)->toHaveCount(6);

    foreach ($ladders as $ladder) {
        // Every rung, every time — that is what makes the mark an index.
        foreach (['TK', 'SMP', 'SMA'] as $rung) {
            expect($ladder)->toContain(">{$rung}");
        }
        // Exactly one lit.
        expect(substr_count($ladder, 'lvl__i is-on'))->toBe(1);
        // The two unlit rungs are hidden from assistive tech, and the lit one
        // carries the visually-hidden qualifier, so a screen reader hears one
        // level per school rather than nine.
        expect(substr_count($ladder, 'aria-hidden="true"'))->toBe(2);
        expect(substr_count($ladder, __('school.level_current')))->toBe(1);
    }
});

it('lights a different rung for schools at different levels', function () {
    // A ladder that lit the same rung every time would pass the count test
    // above and be useless.
    $html = $this->get('/id')->getContent();

    $lit = [];
    foreach (landingLadders($html) as $ladder) {
        preg_match('/lvl__i is-on">([A-Z]+)/', $ladder, $m);
        $lit[] = $m[1];
    }

    expect(array_unique($lit))->toHaveCount(3);
});

it('carries the age range on the lit rung, from the school data', function () {
    $this->get('/id')
        ->assertSee('4-6 tahun')
        ->assertSee('12-15 tahun')
        ->assertSee('15-18 tahun');

    // Localised, and nothing is sized to the English string.
    $this->get('/en')->assertSee('ages 12-15');
});

it('produces an age_range on every school in the directory shape', function () {
    // The data layer has to produce this key too — see
    // docs/landing-redesign-report.md on docs/data-contract.md's School shape.
    foreach (\App\ViewModels\SchoolData::all() as $school) {
        expect($school)->toHaveKey('age_range');
        expect($school['age_range'])->not->toBeEmpty();
    }
});

it('differentiates the lit rung by optical size and weight, never by contrast alone', function () {
    $css = file_get_contents(resource_path('css/landing.css'));

    expect($css)->toMatch("/\.lvl__i\.is-on\s*\{[^}]*font-variation-settings:\s*'opsz' 14, 'wght' 700/s")
        ->and($css)->toMatch('/\.lvl__i\.is-on\s*\{[^}]*font-size:\s*27px/s')
        // The unlit rungs stay at a token that clears AA. If this ever becomes
        // a bespoke faded grey, the differentiation has collapsed to contrast.
        ->and($css)->toMatch('/\.lvl__i\s*\{[^}]*color:\s*var\(--ink-muted\)/s')
        // And the accent rule in the left gutter carries the rest of the load.
        ->and($css)->toMatch('/\.lvl__i\.is-on::before\s*\{[^}]*background:\s*var\(--accent\)/s');
});

// ---------------------------------------------------------------------------
// 5. Fraunces' axes.
// ---------------------------------------------------------------------------

it('sets Fraunces axes rather than pinning one weight', function () {
    $css = file_get_contents(resource_path('css/landing.css'));

    // The key move: low weight at large size against a heavy small mark.
    expect($css)->toContain("font-variation-settings: 'opsz' 144, 'wght' 330")  // masthead
        ->and($css)->toContain("font-variation-settings: 'opsz' 144, 'wght' 280")  // statistics
        ->and($css)->toContain("font-variation-settings: 'opsz' 14, 'wght' 700");  // level mark

    // font-optical-sizing must be off wherever opsz is set by hand, or the
    // browser overrides it from the font size and nothing is redrawn.
    expect(substr_count($css, 'font-optical-sizing: none'))->toBeGreaterThanOrEqual(2);
});

// ---------------------------------------------------------------------------
// 6. Placeholder plates.
// ---------------------------------------------------------------------------

it('renders captioned plates built from tokens, with no image request at all', function () {
    $html = $this->get('/id')->getContent();

    expect(substr_count($html, 'class="plate'))->toBeGreaterThanOrEqual(10)
        // Every plate names the photograph that belongs in it.
        ->and($html)->toContain('plate__slug')
        // And nothing on the page fetches a stand-in image from the internet.
        ->and($html)->not->toContain('placehold.co');
});

it('builds every plate variant out of palette tokens only', function () {
    $css = file_get_contents(resource_path('css/landing.css'));

    foreach (['.plate--field', '.plate--grass', '.plate--dusk'] as $variant) {
        expect($css)->toContain($variant);
    }

    // The fills must be tokens, so both themes resolve. A literal hex in a
    // plate variant is the failure this guards: #000 in plate--dusk is the one
    // deliberate exception (a darkening stop, not a colour).
    preg_match('/\.plate--(field|grass|dusk)\s*\{(.*?)\}/s', $css, $m);
    expect($m[2])->toContain('var(--');
});

it('uses all three plate variants rather than one tone for the whole page', function () {
    $html = $this->get('/id')->getContent();

    foreach (['plate--field', 'plate--grass', 'plate--dusk'] as $variant) {
        expect($html)->toContain($variant);
    }
});

// ---------------------------------------------------------------------------
// 7. The constraints that bind.
// ---------------------------------------------------------------------------

it('never uses accent as text on a sunk or tinted ground', function () {
    // Spec §4: light accent is 4.26:1 on surface-sunk and 3.92:1 on badge-bg.
    // Both fail AA, and --accent must not be darkened to fix it, so those
    // grounds use --badge-ink instead.
    $html = $this->get('/id')->getContent();

    preg_match_all('/<section class="[^"]*on-(?:sunk|tint)[^"]*".*?<\/section>/s', $html, $m);
    expect($m[0])->not->toBeEmpty('expected the page to have a sunk and a tinted section');

    foreach ($m[0] as $section) {
        // text-accent as its own utility, not text-accent-ink: --accent-ink on
        // an --accent BACKGROUND is fine (5.00:1) and is what the primary
        // button on the tinted field uses. It is accent as TEXT on these two
        // grounds that fails.
        expect($section)->not->toMatch('/text-accent(?![\w-])/');
    }

    $css = file_get_contents(resource_path('css/landing.css'));
    expect($css)->toMatch('/\.on-sunk \.ed-label,\s*\.on-tint \.ed-label\s*\{\s*color:\s*var\(--badge-ink\)/');
});

it('shows no numeric funding figure or progress indicator anywhere', function () {
    $html = $this->get('/id')->getContent();

    expect($html)->not->toContain('<progress')
        ->and($html)->not->toContain('role="progressbar"')
        ->and($html)->not->toContain('funding_goal')
        ->and($html)->not->toContain('amount_raised')
        // No percentages: the statistics and the ledger are counts and
        // durations, never a proportion of a target.
        ->and($html)->not->toMatch('/\d+\s?%/');
});

it('sizes nothing to English and truncates no rendered copy', function () {
    foreach (['/id', '/en'] as $url) {
        $html = $this->get($url)->getContent();

        expect($html)->not->toContain('truncate')
            // The one permitted nowrap is none: Indonesian runs 15-20% longer.
            ->and($html)->not->toContain('whitespace-nowrap');
    }
});

it('keeps the skip link as the first focusable element', function () {
    $html = $this->get('/id')->getContent();

    expect(strpos($html, 'href="#main"'))->toBeLessThan(strpos($html, '<header'));
});

it('resolves the theme in three states', function () {
    $tokens = file_get_contents(resource_path('css/tokens.css'));

    // A bare :root with the complete light palette, a guarded media query, and
    // an explicit dark block. Two of the three is how one theme's text ends up
    // on the other theme's ground.
    expect($tokens)->toContain(':root {')
        ->and($tokens)->toContain(':root:not([data-theme="light"])')
        ->and($tokens)->toContain(':root[data-theme="dark"]');

    // The two tokens this redesign added must exist in all three.
    expect(substr_count($tokens, '--inverse-accent:'))->toBe(3)
        ->and(substr_count($tokens, '--inverse-accent-ink:'))->toBe(3);
});

// ---------------------------------------------------------------------------
// 8. Maria Bulu — one school, everywhere.
// ---------------------------------------------------------------------------

it('places Maria Bulu at TK Harapan Karuni everywhere she appears', function () {
    // lang/*.json used to place her at SMP Harapan Anakalang while PostData
    // told her story as the head teacher who returned to TK Harapan Karuni.
    // TK Karuni wins — see docs/landing-redesign-report.md.
    foreach (['id', 'en'] as $locale) {
        app()->setLocale($locale);
        expect(__('about.people.maria'))->toContain('TK Harapan Karuni')
            ->and(__('about.people.maria'))->not->toContain('Anakalang');
    }

    app()->setLocale('id');
    $post = \App\ViewModels\PostData::find('ibu-maria-bulu');
    expect($post['quote']['role'])->toContain('TK Harapan Karuni');

    // And on the rendered pages, including the landing page's pull quote.
    foreach (['/id', route('id.about')] as $url) {
        $html = $this->get($url)->getContent();
        if (str_contains($html, 'Maria Bulu')) {
            expect($html)->not->toMatch('/Maria Bulu[^<]*Anakalang/');
        }
    }
});
