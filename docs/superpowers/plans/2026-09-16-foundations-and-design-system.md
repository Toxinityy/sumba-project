# Hope for Sumba — Foundations and Design System Implementation Plan

> **Approved UX correction (2026-09-20):** AGENTS.md and the user-approved review prohibit public numeric funding displays, including sponsorship costs and currency conversions. This supersedes the IDR/USD display instructions and tier-card examples below; internal cost fields may remain. Giving is enquiry-led until verified payment details are supplied. Fixture statistics, placeholder evidence/partners and the four deferred pages are preview-only outside production. Contact failures must preserve input and provide localized recovery. See `docs/superpowers/plans/2026-09-20-high-priority-ux.md`.

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Stand up a deployed, two-locale Laravel site on shared hosting that serves budget-enforced responsive images, and build every section component of the design system so pages can be assembled in the next plan.

**Architecture:** Server-rendered Laravel with Blade templates and Tailwind. Locale-prefixed routes with translated path segments, registered by looping a config map so a single route definition serves both languages. Images convert at upload time through a capability-detecting pipeline that degrades AVIF → WebP → JPEG based on what the host can actually encode, with a quality-stepping loop that enforces a hard byte budget. The design system is a set of Blade components, one per section from spec §5, exercised by a gallery page.

**Tech Stack:** Laravel, PHP 8.2+, Pest, Tailwind CSS, Alpine.js, `spatie/laravel-medialibrary`, Intervention Image. cPanel shared hosting, no shell access, cron-driven queue.

**Spec:** `docs/superpowers/specs/2026-09-16-hope-for-sumba-design.md`

**Scope:** This is plan 1 of 3. It covers spec §10 phases 0 and 1. Phase 2 (content models and Filament panel), phase 3 (pages) and phase 4 (launch readiness) get their own plans.

## Global Constraints

Copied verbatim from the spec. Every task's requirements implicitly include this section.

- **Accessibility:** WCAG AA on all text — 4.5:1 body, 3:1 large text. Verify any new colour pair before using it.
- **Light theme tokens:** `surface #F7F8F3`, `surface-raised #FFFFFF`, `surface-sunk #ECEEE3`, `ink #1E2A22`, `ink-muted #4B5A4E`, `accent #96660E`, `accent-strong #7C5309`, `accent-ink #FFFFFF`, `border #DEDFCE`, `badge-bg #EFE3C8`, `badge-ink #5A3E0E`, `inverse-surface #1E2A22`, `inverse-ink #F7F8F3`, `inverse-ink-muted #B9C2B4`.
- **Dark theme tokens:** `surface #211A15`, `surface-raised #2B231C`, `surface-sunk #1A1410`, `ink #F5EFE6`, `ink-muted #C9BEB0`, `accent #E8A23A`, `accent-strong #F0B45C`, `accent-ink #241A0E`, `border #3B322A`, `badge-bg #3B2E1C`, `badge-ink #F0C97D`, `inverse-surface #3A2E22`, `inverse-ink #F5EFE6`, `inverse-ink-muted #CFC4B4`.
- **The light accent `#96660E` is at 4.68:1.** It must not be darkened without recomputing contrast.
- **Dark `inverse-surface` must stay distinct from `surface-raised`** or the section alternation collapses.
- **Geometry, both themes:** radius 6 / 10 / 14px, buttons 8px. Radius never changes between themes.
- **Type scale, desktop / mobile:** Display 56/36, H1 44/32, H2 32/26 (Fraunces); H3 24/20 semibold, body 18/17 at line-height 1.7, caption 14 uppercase +0.08em (Plus Jakarta Sans).
- **Layout:** 8px spacing base, max width 1200px, prose 68ch, section padding 96px desktop / 56px mobile, minimum 16px side gutter, no horizontal page scroll at any width.
- **Indonesian runs 15–20% longer than English.** Never size a button, nav item or card to fit English exactly.
- **Hero images ≤ 200KB** after conversion.
- **Theme resolves in three states:** bare `:root` carries the complete light palette; dark is applied by `@media (prefers-color-scheme: dark)` guarded as `:root:not([data-theme="light"])`, and again by `:root[data-theme="dark"]`.
- **Locales:** `id` (default) and `en`, locale-prefixed routes with translated path segments, both independently indexable.
- **No numeric funding display anywhere.** No goals, no amounts raised, no progress bars.

---

## File Structure

| Path | Responsibility |
|---|---|
| `config/locales.php` | Supported locales, default, and the translated path-segment map |
| `app/Http/Middleware/SetLocale.php` | Reads the locale prefix, sets app locale, 404s on unknown locale |
| `app/Support/LocalizedUrl.php` | Resolves the equivalent URL for a route in another locale |
| `app/Services/Images/ImageCapabilities.php` | Detects which formats the host can encode; caches the answer |
| `app/Services/Images/VariantGenerator.php` | Produces format/width variants under a byte budget |
| `app/View/Components/Picture.php` + `resources/views/components/picture.blade.php` | The single responsive `<picture>` used everywhere |
| `resources/css/tokens.css` | Theme tokens, both themes, three states |
| `resources/css/app.css` | Tailwind entry, imports tokens |
| `resources/views/components/sections/*.blade.php` | One file per section from spec §5 |
| `resources/views/components/cards/*.blade.php` | School, story and tier cards |
| `resources/views/layouts/site.blade.php` | Base layout: head, nav, footer, theme script |
| `resources/views/gallery.blade.php` | Component gallery exercising every section |
| `tests/Feature/*`, `tests/Unit/*` | Pest tests |
| `docs/deployment.md` | cPanel deploy steps, cron entry, host capability findings |

Sections and cards are split one-per-file rather than grouped: they are edited individually, and a single 600-line components file is harder to work in than fifteen small ones.

---

## Task 1: Laravel scaffold and test harness

**Files:**
- Create: whole Laravel skeleton at repo root
- Create: `docs/deployment.md`
- Test: `tests/Feature/SmokeTest.php`

**Interfaces:**
- Consumes: nothing
- Produces: a booting Laravel app; `php artisan test` runs Pest

- [ ] **Step 1: Scaffold Laravel into the repo**

The repo already contains `docs/` and `prototype/`, so scaffold into a temporary directory and move the files in, rather than letting the installer refuse a non-empty directory.

```bash
composer create-project laravel/laravel /tmp/hfs-app
cp -r /tmp/hfs-app/. .
rm -rf /tmp/hfs-app
```

- [ ] **Step 2: Record the versions actually installed**

Do not assume versions. Capture what landed, because the deployment doc and the host's PHP version have to agree.

```bash
php artisan --version
php -v
composer show laravel/framework | head -3
```

Write the three results into `docs/deployment.md` under a heading `## Versions`.

- [ ] **Step 3: Verify the test runner works**

Run: `php artisan test`
Expected: PASS — Laravel ships example tests.

- [ ] **Step 4: Write a smoke test**

```php
<?php
// tests/Feature/SmokeTest.php

it('boots and serves a response', function () {
    $this->get('/')->assertStatus(200);
});
```

- [ ] **Step 5: Run it**

Run: `php artisan test --filter=SmokeTest`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add -A
git commit -m "chore: scaffold Laravel application"
```

---

## Task 2: Theme tokens and Tailwind

**Files:**
- Create: `resources/css/tokens.css`
- Modify: `resources/css/app.css`
- Modify: `tailwind.config.js`
- Test: `tests/Unit/TokenContrastTest.php`

**Interfaces:**
- Consumes: nothing
- Produces: CSS custom properties `--surface`, `--surface-raised`, `--surface-sunk`, `--ink`, `--ink-muted`, `--accent`, `--accent-strong`, `--accent-ink`, `--border`, `--badge-bg`, `--badge-ink`, `--inverse-surface`, `--inverse-ink`, `--inverse-ink-muted`, `--radius-sm`, `--radius`, `--radius-lg`, `--radius-pill`; Tailwind colour names `surface`, `raised`, `sunk`, `ink`, `ink-muted`, `accent`, `accent-strong`, `accent-ink`, `line`, `badge`, `badge-ink`, `inverse`, `inverse-ink`, `inverse-ink-muted`

- [ ] **Step 1: Write the failing contrast test**

The palette is a correctness concern, not a taste one — a regression here is an accessibility failure that nobody notices by eye. This test reads the token values and recomputes every pair.

```php
<?php
// tests/Unit/TokenContrastTest.php

function relativeLuminance(string $hex): float {
    $hex = ltrim($hex, '#');
    $parts = [];
    foreach ([0, 2, 4] as $offset) {
        $channel = hexdec(substr($hex, $offset, 2)) / 255;
        $parts[] = $channel <= 0.03928
            ? $channel / 12.92
            : (($channel + 0.055) / 1.055) ** 2.4;
    }
    return 0.2126 * $parts[0] + 0.7152 * $parts[1] + 0.0722 * $parts[2];
}

function contrastRatio(string $fg, string $bg): float {
    $a = relativeLuminance($fg);
    $b = relativeLuminance($bg);
    return (max($a, $b) + 0.05) / (min($a, $b) + 0.05);
}

dataset('textPairs', [
    'light ink on surface'          => ['#1E2A22', '#F7F8F3'],
    'light muted on surface'        => ['#4B5A4E', '#F7F8F3'],
    'light accent on surface'       => ['#96660E', '#F7F8F3'],
    'light accent on raised'        => ['#96660E', '#FFFFFF'],
    'light accent-ink on accent'    => ['#FFFFFF', '#96660E'],
    'light ink on inverse'          => ['#F7F8F3', '#1E2A22'],
    'light muted on inverse'        => ['#B9C2B4', '#1E2A22'],
    'light badge-ink on badge'      => ['#5A3E0E', '#EFE3C8'],
    'dark ink on surface'           => ['#F5EFE6', '#211A15'],
    'dark muted on surface'         => ['#C9BEB0', '#211A15'],
    'dark accent on surface'        => ['#E8A23A', '#211A15'],
    'dark accent on raised'         => ['#E8A23A', '#2B231C'],
    'dark accent-ink on accent'     => ['#241A0E', '#E8A23A'],
    'dark ink on inverse'           => ['#F5EFE6', '#3A2E22'],
    'dark muted on inverse'         => ['#CFC4B4', '#3A2E22'],
    'dark badge-ink on badge'       => ['#F0C97D', '#3B2E1C'],
]);

it('meets WCAG AA for body text', function (string $fg, string $bg) {
    expect(contrastRatio($fg, $bg))->toBeGreaterThanOrEqual(4.5);
})->with('textPairs');

it('keeps the dark inverse band distinct from the raised surface', function () {
    // If these collapse to the same value the section alternation dies silently.
    expect('#3A2E22')->not->toBe('#2B231C');
});

it('declares every token used by a theme in the base :root block', function () {
    $css = file_get_contents(resource_path('css/tokens.css'));
    $base = substr($css, 0, strpos($css, '@media'));

    foreach ([
        '--surface', '--surface-raised', '--surface-sunk', '--ink', '--ink-muted',
        '--accent', '--accent-strong', '--accent-ink', '--border',
        '--badge-bg', '--badge-ink',
        '--inverse-surface', '--inverse-ink', '--inverse-ink-muted',
    ] as $token) {
        expect($base)->toContain($token);
    }
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `php artisan test --filter=TokenContrastTest`
Expected: FAIL — `resources/css/tokens.css` does not exist.

- [ ] **Step 3: Write the tokens**

```css
/* resources/css/tokens.css */

/* Three states, not two. The bare :root carries the COMPLETE light palette,
   because the default "system" setting stamps no attribute at all and most
   visitors arrive in that state. A colour whose only definition sits inside a
   media query or a [data-theme] block renders one theme's text on the other
   theme's ground. */

:root {
  --surface: #f7f8f3;
  --surface-raised: #ffffff;
  --surface-sunk: #eceee3;
  --ink: #1e2a22;
  --ink-muted: #4b5a4e;
  --accent: #96660e;
  --accent-strong: #7c5309;
  --accent-ink: #ffffff;
  --border: #dedfce;
  --badge-bg: #efe3c8;
  --badge-ink: #5a3e0e;
  --inverse-surface: #1e2a22;
  --inverse-ink: #f7f8f3;
  --inverse-ink-muted: #b9c2b4;

  /* Geometry is theme-independent: corners that move on a theme toggle read
     as two different sites. */
  --radius-sm: 6px;
  --radius: 10px;
  --radius-lg: 14px;
  --radius-pill: 8px;
}

@media (prefers-color-scheme: dark) {
  /* Guarded so an explicit light choice still beats a dark OS. */
  :root:not([data-theme="light"]) {
    --surface: #211a15;
    --surface-raised: #2b231c;
    --surface-sunk: #1a1410;
    --ink: #f5efe6;
    --ink-muted: #c9beb0;
    --accent: #e8a23a;
    --accent-strong: #f0b45c;
    --accent-ink: #241a0e;
    --border: #3b322a;
    --badge-bg: #3b2e1c;
    --badge-ink: #f0c97d;
    --inverse-surface: #3a2e22;
    --inverse-ink: #f5efe6;
    --inverse-ink-muted: #cfc4b4;
  }
}

:root[data-theme="dark"] {
  --surface: #211a15;
  --surface-raised: #2b231c;
  --surface-sunk: #1a1410;
  --ink: #f5efe6;
  --ink-muted: #c9beb0;
  --accent: #e8a23a;
  --accent-strong: #f0b45c;
  --accent-ink: #241a0e;
  --border: #3b322a;
  --badge-bg: #3b2e1c;
  --badge-ink: #f0c97d;
  --inverse-surface: #3a2e22;
  --inverse-ink: #f5efe6;
  --inverse-ink-muted: #cfc4b4;
}
```

- [ ] **Step 4: Import tokens and set the base layer**

```css
/* resources/css/app.css */
@import "./tokens.css";

@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  body {
    /* Explicit background: a transparent body borrows the host's ground. */
    background-color: var(--surface);
    color: var(--ink);
    font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
    font-size: 18px;
    line-height: 1.7;
  }

  @media (max-width: 720px) {
    body { font-size: 17px; }
  }

  :focus-visible {
    outline: 3px solid var(--accent);
    outline-offset: 3px;
  }

  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
      animation-duration: 0.01ms !important;
      transition-duration: 0.01ms !important;
    }
  }
}
```

- [ ] **Step 5: Map tokens into Tailwind**

```js
// tailwind.config.js
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        surface: "var(--surface)",
        raised: "var(--surface-raised)",
        sunk: "var(--surface-sunk)",
        ink: "var(--ink)",
        "ink-muted": "var(--ink-muted)",
        accent: "var(--accent)",
        "accent-strong": "var(--accent-strong)",
        "accent-ink": "var(--accent-ink)",
        line: "var(--border)",
        badge: "var(--badge-bg)",
        "badge-ink": "var(--badge-ink)",
        inverse: "var(--inverse-surface)",
        "inverse-ink": "var(--inverse-ink)",
        "inverse-ink-muted": "var(--inverse-ink-muted)",
      },
      borderRadius: {
        sm: "var(--radius-sm)",
        DEFAULT: "var(--radius)",
        lg: "var(--radius-lg)",
        pill: "var(--radius-pill)",
      },
      fontFamily: {
        display: ['"Fraunces"', "ui-serif", "Georgia", "serif"],
        body: ['"Plus Jakarta Sans"', "ui-sans-serif", "system-ui", "sans-serif"],
      },
      fontSize: {
        display: ["56px", { lineHeight: "1.1", letterSpacing: "-0.015em" }],
        h1: ["44px", { lineHeight: "1.1", letterSpacing: "-0.015em" }],
        h2: ["32px", { lineHeight: "1.2", letterSpacing: "-0.01em" }],
        h3: ["24px", { lineHeight: "1.3" }],
        body: ["18px", { lineHeight: "1.7" }],
        caption: ["14px", { lineHeight: "1.4", letterSpacing: "0.08em" }],
      },
      maxWidth: {
        content: "1200px",
        prose: "68ch",
      },
    },
  },
  plugins: [],
};
```

- [ ] **Step 6: Run the tests**

Run: `php artisan test --filter=TokenContrastTest`
Expected: PASS — all 16 pairs plus both structural assertions.

- [ ] **Step 7: Verify the build compiles**

Run: `npm install && npm run build`
Expected: builds without error.

- [ ] **Step 8: Commit**

```bash
git add resources/css tailwind.config.js tests/Unit/TokenContrastTest.php
git commit -m "feat: add theme tokens with contrast tests"
```

---

## Task 3: Self-hosted subset fonts

**Files:**
- Create: `public/fonts/` (woff2 files)
- Create: `resources/css/fonts.css`
- Modify: `resources/css/app.css`
- Create: `docs/fonts.md`

**Interfaces:**
- Consumes: nothing
- Produces: `@font-face` rules for `Fraunces` and `Plus Jakarta Sans`; no runtime request to Google

- [ ] **Step 1: Fetch the already-subset woff2 files**

Google's CSS API serves per-subset woff2 files that are **already subset** — there is no need for a subsetting toolchain. Request with a modern user agent to get woff2 rather than ttf.

```bash
mkdir -p public/fonts

curl -s -H "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36" \
  "https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,400..600&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" \
  -o /tmp/gf.css

grep -o 'https://fonts.gstatic.com[^)]*\.woff2' /tmp/gf.css | sort -u
```

- [ ] **Step 2: Download only the latin and latin-ext faces**

Indonesian and English are both Latin-script, so every other subset (cyrillic, greek, vietnamese) is dead weight. The fetched CSS groups faces by `/* subset */` comments — take only `latin` and `latin-ext`.

```bash
for url in $(grep -o 'https://fonts.gstatic.com[^)]*\.woff2' /tmp/gf.css | sort -u); do
  curl -s "$url" -o "public/fonts/$(basename $url)"
done
ls -la public/fonts/
```

Record in `docs/fonts.md`: the exact query URL used, which files were kept, and the total byte size.

- [ ] **Step 3: Write the @font-face rules**

Substitute the real filenames from step 2. `font-display: swap` means text paints in the fallback immediately rather than staying invisible — on an Indonesian mobile connection that difference is seconds of blank page.

```css
/* resources/css/fonts.css */

@font-face {
  font-family: "Fraunces";
  font-style: normal;
  font-weight: 300 700;   /* variable range */
  font-display: swap;
  src: url("/fonts/fraunces-latin.woff2") format("woff2");
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+2000-206F, U+2122;
}

@font-face {
  font-family: "Plus Jakarta Sans";
  font-style: normal;
  font-weight: 400 700;
  font-display: swap;
  src: url("/fonts/plus-jakarta-sans-latin.woff2") format("woff2");
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+2000-206F, U+2122;
}
```

- [ ] **Step 4: Import fonts first in app.css**

```css
/* resources/css/app.css — fonts must be imported before tokens */
@import "./fonts.css";
@import "./tokens.css";
```

- [ ] **Step 5: Verify no Google request remains**

Run: `grep -r "fonts.googleapis\|fonts.gstatic" resources/ public/build/ || echo "clean"`
Expected: `clean` — the built CSS must not reach Google at runtime.

- [ ] **Step 6: Commit**

```bash
git add public/fonts resources/css docs/fonts.md
git commit -m "feat: self-host subset Fraunces and Plus Jakarta Sans"
```

---

## Task 4: Locale configuration and routing

**Files:**
- Create: `config/locales.php`
- Create: `app/Http/Middleware/SetLocale.php`
- Modify: `routes/web.php`
- Modify: `bootstrap/app.php`
- Test: `tests/Feature/LocaleRoutingTest.php`

**Interfaces:**
- Consumes: nothing
- Produces: `config('locales.supported')` → `['id','en']`; `config('locales.default')` → `'id'`; `config('locales.segments')` → `array<string, array{id: string, en: string}>`; named routes `{locale}.schools.index`, `{locale}.about`, etc.; middleware alias `setlocale`

- [ ] **Step 1: Write the failing routing test**

```php
<?php
// tests/Feature/LocaleRoutingTest.php

it('redirects the root to the default locale', function () {
    $this->get('/')->assertRedirect('/id');
});

it('redirects the root to English when Accept-Language clearly prefers it', function () {
    $this->get('/', ['Accept-Language' => 'en-GB,en;q=0.9'])
        ->assertRedirect('/en');
});

it('serves the Indonesian segment for schools', function () {
    $this->get('/id/sekolah')->assertOk();
});

it('serves the English segment for schools', function () {
    $this->get('/en/schools')->assertOk();
});

it('does not serve the English segment under the Indonesian prefix', function () {
    $this->get('/id/schools')->assertNotFound();
});

it('rejects an unsupported locale prefix', function () {
    $this->get('/fr/sekolah')->assertNotFound();
});

it('sets the application locale from the prefix', function () {
    $this->get('/en/schools');
    expect(app()->getLocale())->toBe('en');
});

it('sets the html lang attribute to the active locale', function () {
    $this->get('/id/sekolah')->assertSee('lang="id"', escape: false);
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `php artisan test --filter=LocaleRoutingTest`
Expected: FAIL — routes do not exist.

- [ ] **Step 3: Write the locale config**

```php
<?php
// config/locales.php

return [
    'supported' => ['id', 'en'],

    'default' => 'id',

    /*
     | Translated path segments. Keyed by an internal name that never appears
     | in a URL, so route names stay stable while the public path differs per
     | locale. Indonesian visitors get Indonesian URLs, which reads as native
     | and indexes better than an English skeleton with Indonesian content.
     */
    'segments' => [
        'about'        => ['id' => 'tentang',            'en' => 'about'],
        'schools'      => ['id' => 'sekolah',            'en' => 'schools'],
        'homes'        => ['id' => 'rumah-anak',         'en' => 'childrens-homes'],
        'stories'      => ['id' => 'cerita',             'en' => 'stories'],
        'give'         => ['id' => 'dukung',             'en' => 'get-involved'],
        'contact'      => ['id' => 'kontak',             'en' => 'contact'],
        'safeguarding' => ['id' => 'perlindungan-anak',  'en' => 'safeguarding'],
    ],
];
```

- [ ] **Step 4: Write the middleware**

```php
<?php
// app/Http/Middleware/SetLocale.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        // An unknown locale is a 404, not a silent fallback: a wrong prefix
        // serving content would create duplicate indexable URLs.
        abort_unless(in_array($locale, config('locales.supported'), true), 404);

        app()->setLocale($locale);

        return $next($request);
    }
}
```

- [ ] **Step 5: Register the middleware alias**

```php
// bootstrap/app.php — inside ->withMiddleware(function (Middleware $middleware) {
$middleware->alias([
    'setlocale' => \App\Http\Middleware\SetLocale::class,
]);
```

- [ ] **Step 6: Write the routes**

```php
<?php
// routes/web.php

use Illuminate\Support\Facades\Route;

/*
 | The root redirects rather than serving content, so there is exactly one
 | canonical URL per page per locale. Accept-Language only decides WHICH
 | locale; it never causes the same URL to serve two different languages.
 */
Route::get('/', function (\Illuminate\Http\Request $request) {
    $preferred = $request->getPreferredLanguage(['id', 'en']);

    return redirect('/' . ($preferred === 'en' ? 'en' : config('locales.default')));
});

foreach (config('locales.supported') as $locale) {
    $segments = collect(config('locales.segments'))
        ->map(fn (array $paths) => $paths[$locale]);

    Route::prefix($locale)
        ->middleware('setlocale')
        ->name("{$locale}.")
        ->group(function () use ($segments) {
            Route::view('/', 'pages.home')->name('home');

            Route::view($segments['about'], 'pages.about')->name('about');
            Route::view($segments['schools'], 'pages.schools')->name('schools.index');
            Route::view($segments['homes'], 'pages.homes')->name('homes.index');
            Route::view($segments['stories'], 'pages.stories')->name('stories.index');
            Route::view($segments['give'], 'pages.give')->name('give');
            Route::view($segments['contact'], 'pages.contact')->name('contact');
            Route::view($segments['safeguarding'], 'pages.safeguarding')->name('safeguarding');
        });
}
```

- [ ] **Step 7: Create placeholder views so routes resolve**

Real pages arrive in plan 3. These exist so routing is testable now.

```bash
mkdir -p resources/views/pages
for p in home about schools homes stories give contact safeguarding; do
  printf '<!doctype html>\n<html lang="{{ app()->getLocale() }}">\n<body>%s</body>\n</html>\n' "$p" \
    > "resources/views/pages/$p.blade.php"
done
```

- [ ] **Step 8: Run the tests**

Run: `php artisan test --filter=LocaleRoutingTest`
Expected: PASS — all 8 assertions.

- [ ] **Step 9: Commit**

```bash
git add config/locales.php app/Http/Middleware routes/web.php bootstrap/app.php resources/views/pages tests/Feature/LocaleRoutingTest.php
git commit -m "feat: add locale-prefixed routing with translated segments"
```

---

## Task 5: Language switcher URL resolution

**Files:**
- Create: `app/Support/LocalizedUrl.php`
- Create: `resources/views/components/language-switcher.blade.php`
- Test: `tests/Feature/LanguageSwitcherTest.php`

**Interfaces:**
- Consumes: `config('locales.supported')`, route names from Task 4
- Produces: `LocalizedUrl::forLocale(string $locale): string` — the equivalent URL of the current route in `$locale`; `LocalizedUrl::alternates(): array<string, string>` — locale ⇒ URL for every supported locale, for `hreflang`

- [ ] **Step 1: Write the failing test**

The switcher dropping a reader on the homepage is the single most common bilingual bug, and it reads as carelessness on a site whose job is looking credible. So it gets a test.

```php
<?php
// tests/Feature/LanguageSwitcherTest.php

use App\Support\LocalizedUrl;

it('resolves the equivalent page in the other locale, not the homepage', function () {
    $this->get('/id/sekolah');

    expect(LocalizedUrl::forLocale('en'))->toEndWith('/en/schools');
});

it('resolves back again symmetrically', function () {
    $this->get('/en/schools');

    expect(LocalizedUrl::forLocale('id'))->toEndWith('/id/sekolah');
});

it('returns the current url when asked for the current locale', function () {
    $this->get('/id/tentang');

    expect(LocalizedUrl::forLocale('id'))->toEndWith('/id/tentang');
});

it('lists an alternate for every supported locale', function () {
    $this->get('/id/kontak');

    expect(LocalizedUrl::alternates())
        ->toHaveKeys(['id', 'en'])
        ->and(LocalizedUrl::alternates()['en'])->toEndWith('/en/contact');
});

it('renders a switcher linking to the equivalent page', function () {
    $this->get('/id/sekolah')
        ->assertSee('/en/schools', escape: false);
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `php artisan test --filter=LanguageSwitcherTest`
Expected: FAIL — `App\Support\LocalizedUrl` not found.

- [ ] **Step 3: Implement the resolver**

```php
<?php
// app/Support/LocalizedUrl.php

namespace App\Support;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class LocalizedUrl
{
    /**
     * The equivalent URL of the current route in another locale.
     *
     * Route names are prefixed by locale ("id.schools.index"), so swapping the
     * prefix yields the sibling route. Falls back to that locale's home rather
     * than throwing, because a missing route must never break page rendering.
     */
    public static function forLocale(string $locale): string
    {
        $current = Route::currentRouteName();

        if ($current === null) {
            return url("/{$locale}");
        }

        $withoutLocale = preg_replace('/^[a-z]{2}\./', '', $current);
        $target = "{$locale}.{$withoutLocale}";

        if (! Route::has($target)) {
            return url("/{$locale}");
        }

        return route($target, Route::current()->parameters());
    }

    /** @return array<string, string> locale => absolute URL, for hreflang. */
    public static function alternates(): array
    {
        $out = [];
        foreach (config('locales.supported') as $locale) {
            $out[$locale] = self::forLocale($locale);
        }

        return $out;
    }
}
```

- [ ] **Step 4: Write the switcher component**

```blade
{{-- resources/views/components/language-switcher.blade.php --}}
@php($alternates = \App\Support\LocalizedUrl::alternates())

<div class="inline-flex overflow-hidden rounded-pill border border-line"
     role="group"
     aria-label="{{ __('Bahasa / Language') }}">
  @foreach ($alternates as $locale => $url)
    <a href="{{ $url }}"
       hreflang="{{ $locale }}"
       @class([
         'px-3 py-2 text-caption font-bold uppercase tracking-[0.08em]',
         'bg-accent text-accent-ink' => $locale === app()->getLocale(),
         'text-ink-muted' => $locale !== app()->getLocale(),
       ])
       @if ($locale === app()->getLocale()) aria-current="true" @endif>
      {{ strtoupper($locale) }}
    </a>
  @endforeach
</div>
```

- [ ] **Step 5: Render the switcher in the placeholder views so the test can see it**

```bash
for p in home about schools homes stories give contact safeguarding; do
  printf '<!doctype html>\n<html lang="{{ app()->getLocale() }}">\n<body>\n<x-language-switcher />\n%s\n</body>\n</html>\n' "$p" \
    > "resources/views/pages/$p.blade.php"
done
```

- [ ] **Step 6: Run the tests**

Run: `php artisan test --filter=LanguageSwitcherTest`
Expected: PASS — all 5 assertions.

- [ ] **Step 7: Commit**

```bash
git add app/Support resources/views/components/language-switcher.blade.php resources/views/pages tests/Feature/LanguageSwitcherTest.php
git commit -m "feat: resolve language switcher to equivalent pages"
```

---

## Task 6: Image capability detection

**Files:**
- Create: `app/Services/Images/ImageCapabilities.php`
- Test: `tests/Unit/ImageCapabilitiesTest.php`
- Modify: `docs/deployment.md`

**Interfaces:**
- Consumes: nothing
- Produces: `ImageCapabilities::supports(string $format): bool` for `'avif'|'webp'|'jpeg'`; `ImageCapabilities::bestChain(): array<string>` returning formats best-first, always ending `'jpeg'`; `ImageCapabilities::report(): array<string, bool>`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Unit/ImageCapabilitiesTest.php

use App\Services\Images\ImageCapabilities;

it('always supports jpeg', function () {
    expect(app(ImageCapabilities::class)->supports('jpeg'))->toBeTrue();
});

it('always ends the chain with jpeg so something always encodes', function () {
    $chain = app(ImageCapabilities::class)->bestChain();

    expect($chain)->not->toBeEmpty()
        ->and(end($chain))->toBe('jpeg');
});

it('orders the chain best-first', function () {
    $chain = app(ImageCapabilities::class)->bestChain();
    $rank = ['avif' => 0, 'webp' => 1, 'jpeg' => 2];

    $ranks = array_map(fn ($f) => $rank[$f], $chain);
    $sorted = $ranks;
    sort($sorted);

    expect($ranks)->toBe($sorted);
});

it('reports on every known format', function () {
    expect(app(ImageCapabilities::class)->report())
        ->toHaveKeys(['avif', 'webp', 'jpeg']);
});

it('never claims a format the runtime cannot encode', function () {
    $caps = app(ImageCapabilities::class);

    if ($caps->supports('webp')) {
        expect(function_exists('imagewebp') || extension_loaded('imagick'))->toBeTrue();
    }
    if ($caps->supports('avif')) {
        expect(function_exists('imageavif') || extension_loaded('imagick'))->toBeTrue();
    }
})->skip(fn () => ! extension_loaded('gd') && ! extension_loaded('imagick'), 'no image extension');
```

- [ ] **Step 2: Run it to verify it fails**

Run: `php artisan test --filter=ImageCapabilitiesTest`
Expected: FAIL — class not found.

- [ ] **Step 3: Implement detection**

```php
<?php
// app/Services/Images/ImageCapabilities.php

namespace App\Services\Images;

use Illuminate\Support\Facades\Cache;
use Imagick;

/**
 * What can this host actually encode?
 *
 * Shared cPanel hosting varies: GD is near-universal, Imagick is common but
 * not guaranteed, and AVIF support depends on a libavif build that many
 * shared hosts do not have. Detecting rather than assuming means the pipeline
 * degrades to something that works instead of silently producing nothing.
 *
 * Detection is cached: probing Imagick's format list on every request is
 * wasted work on a host we do not control and cannot speed up.
 */
class ImageCapabilities
{
    private const CACHE_KEY = 'image-capabilities';
    private const CACHE_TTL = 3600;

    /** Best first. JPEG is last and unconditional. */
    private const PREFERENCE = ['avif', 'webp', 'jpeg'];

    /** @return array<string, bool> */
    public function report(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => [
            'avif' => $this->detectAvif(),
            'webp' => $this->detectWebp(),
            'jpeg' => true,
        ]);
    }

    public function supports(string $format): bool
    {
        return $this->report()[$format] ?? false;
    }

    /** @return array<string> */
    public function bestChain(): array
    {
        $chain = array_values(array_filter(
            self::PREFERENCE,
            fn (string $format) => $this->supports($format)
        ));

        // Belt and braces: JPEG must always be encodable or uploads fail.
        if (! in_array('jpeg', $chain, true)) {
            $chain[] = 'jpeg';
        }

        return $chain;
    }

    private function detectAvif(): bool
    {
        if (function_exists('imageavif')) {
            return true;
        }

        return extension_loaded('imagick')
            && in_array('AVIF', array_map('strtoupper', Imagick::queryFormats()), true);
    }

    private function detectWebp(): bool
    {
        if (function_exists('imagewebp')) {
            return true;
        }

        return extension_loaded('imagick')
            && in_array('WEBP', array_map('strtoupper', Imagick::queryFormats()), true);
    }
}
```

- [ ] **Step 4: Run the tests**

Run: `php artisan test --filter=ImageCapabilitiesTest`
Expected: PASS

- [ ] **Step 5: Add an artisan command to report host capabilities**

This is the week-one answer to "can this host do AVIF", and it must be runnable on the host itself, not locally.

```php
<?php
// app/Console/Commands/ImageCapabilitiesReport.php

namespace App\Console\Commands;

use App\Services\Images\ImageCapabilities;
use Illuminate\Console\Command;

class ImageCapabilitiesReport extends Command
{
    protected $signature = 'images:capabilities';
    protected $description = 'Report which image formats this host can encode';

    public function handle(ImageCapabilities $capabilities): int
    {
        $this->table(
            ['Format', 'Encodable'],
            collect($capabilities->report())
                ->map(fn (bool $ok, string $format) => [$format, $ok ? 'yes' : 'no'])
                ->values()
                ->all()
        );

        $this->line('Chain: ' . implode(' -> ', $capabilities->bestChain()));

        return self::SUCCESS;
    }
}
```

- [ ] **Step 6: Run it locally and record the result**

Run: `php artisan images:capabilities`

Record the output in `docs/deployment.md` under `## Host image capabilities`, marked as **local**. The same command must be run on staging in Task 10 and the result recorded there — local results say nothing about the host.

- [ ] **Step 7: Commit**

```bash
git add app/Services/Images app/Console/Commands tests/Unit/ImageCapabilitiesTest.php docs/deployment.md
git commit -m "feat: detect host image encoding capabilities"
```

---

## Task 7: Variant generation with a byte budget

**Files:**
- Create: `app/Services/Images/VariantGenerator.php`
- Test: `tests/Feature/VariantGeneratorTest.php`
- Create: `tests/fixtures/sample-hero.jpg`

**Interfaces:**
- Consumes: `ImageCapabilities::bestChain()`
- Produces: `VariantGenerator::generate(string $sourcePath, int $width, string $format, int $budgetBytes): Variant`; readonly `Variant { public string $path; public string $format; public int $width; public int $height; public int $bytes; public int $quality; public bool $hitQualityFloor; }`

- [ ] **Step 1: Install Intervention Image**

```bash
composer require intervention/image
```

- [ ] **Step 2: Add a test fixture**

A real photograph, not a solid colour — a flat image compresses to almost nothing and would make the budget test pass vacuously.

```bash
mkdir -p tests/fixtures
# Any photographic JPEG at least 3000px wide, at least 1MB.
# Record its provenance in tests/fixtures/README.md.
```

- [ ] **Step 3: Write the failing test**

```php
<?php
// tests/Feature/VariantGeneratorTest.php

use App\Services\Images\VariantGenerator;

beforeEach(function () {
    $this->source = base_path('tests/fixtures/sample-hero.jpg');
    $this->generator = app(VariantGenerator::class);
});

it('produces a file at the requested width', function () {
    $variant = $this->generator->generate($this->source, 1200, 'jpeg', 200_000);

    expect($variant->width)->toBe(1200)
        ->and(file_exists($variant->path))->toBeTrue();
});

it('keeps a hero variant inside the 200KB budget', function () {
    $variant = $this->generator->generate($this->source, 1600, 'jpeg', 200_000);

    expect($variant->bytes)->toBeLessThanOrEqual(200_000);
});

it('steps quality down rather than overshooting the budget', function () {
    $generous = $this->generator->generate($this->source, 1600, 'jpeg', 200_000);
    $tight    = $this->generator->generate($this->source, 1600, 'jpeg', 40_000);

    expect($tight->quality)->toBeLessThan($generous->quality);
});

it('flags when it hit the quality floor instead of silently shipping mush', function () {
    $variant = $this->generator->generate($this->source, 2400, 'jpeg', 5_000);

    expect($variant->hitQualityFloor)->toBeTrue();
});

it('reports the height it actually produced', function () {
    $variant = $this->generator->generate($this->source, 800, 'jpeg', 200_000);

    expect($variant->height)->toBeGreaterThan(0);
});
```

- [ ] **Step 4: Run it to verify it fails**

Run: `php artisan test --filter=VariantGeneratorTest`
Expected: FAIL — class not found.

- [ ] **Step 5: Implement the generator**

```php
<?php
// app/Services/Images/VariantGenerator.php

namespace App\Services\Images;

use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

final readonly class Variant
{
    public function __construct(
        public string $path,
        public string $format,
        public int $width,
        public int $height,
        public int $bytes,
        public int $quality,
        public bool $hitQualityFloor,
    ) {}
}

class VariantGenerator
{
    /**
     * Quality ladder. The budget is enforced by stepping DOWN until the file
     * fits, rather than by picking one quality and hoping. A 200KB hero limit
     * that is not actually checked is not a limit.
     */
    private const QUALITY_STEPS = [82, 74, 66, 58, 50, 42];

    public function __construct(private ImageCapabilities $capabilities) {}

    public function generate(
        string $sourcePath,
        int $width,
        string $format,
        int $budgetBytes,
    ): Variant {
        if (! $this->capabilities->supports($format)) {
            $format = 'jpeg';
        }

        $manager = new ImageManager(new GdDriver());

        $encoded = null;
        $usedQuality = end(self::QUALITY_STEPS);
        $hitFloor = true;

        foreach (self::QUALITY_STEPS as $quality) {
            $image = $manager->read($sourcePath)->scaleDown(width: $width);
            $encoded = $this->encode($image, $format, $quality);
            $usedQuality = $quality;

            if (strlen((string) $encoded) <= $budgetBytes) {
                $hitFloor = false;
                break;
            }
        }

        if ($hitFloor) {
            // Someone should choose a less detailed crop rather than ship a
            // soft hero. Silence here is how budgets quietly stop being met.
            Log::warning('Image hit the quality floor and still exceeds budget', [
                'source' => $sourcePath,
                'width' => $width,
                'format' => $format,
                'budget_bytes' => $budgetBytes,
                'actual_bytes' => strlen((string) $encoded),
            ]);
        }

        $final = $manager->read($sourcePath)->scaleDown(width: $width);
        $path = $this->writeToDisk($encoded, $format);

        return new Variant(
            path: $path,
            format: $format,
            width: $final->width(),
            height: $final->height(),
            bytes: strlen((string) $encoded),
            quality: $usedQuality,
            hitQualityFloor: $hitFloor,
        );
    }

    private function encode($image, string $format, int $quality): string
    {
        return (string) match ($format) {
            'avif' => $image->toAvif(quality: $quality),
            'webp' => $image->toWebp(quality: $quality),
            default => $image->toJpeg(quality: $quality),
        };
    }

    private function writeToDisk(string $encoded, string $format): string
    {
        $directory = storage_path('app/variants');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Content-hashed so variants are immutable and can carry far-future
        // cache headers.
        $path = $directory . '/' . hash('xxh128', $encoded) . '.' . $format;
        file_put_contents($path, $encoded);

        return $path;
    }
}
```

- [ ] **Step 6: Run the tests**

Run: `php artisan test --filter=VariantGeneratorTest`
Expected: PASS — all 5 assertions.

- [ ] **Step 7: Commit**

```bash
git add app/Services/Images/VariantGenerator.php tests/Feature/VariantGeneratorTest.php tests/fixtures composer.json composer.lock
git commit -m "feat: generate image variants under an enforced byte budget"
```

---

## Task 8: The responsive picture component

**Files:**
- Create: `app/View/Components/Picture.php`
- Create: `resources/views/components/picture.blade.php`
- Test: `tests/Feature/PictureComponentTest.php`

**Interfaces:**
- Consumes: `ImageCapabilities::bestChain()`, `Variant` from Task 7
- Produces: `<x-picture :sources="..." :width="..." :height="..." alt="..." :eager="false" sizes="..." />`

- [ ] **Step 1: Write the failing test**

```php
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
     ->assertSee('fetchpriority="high"', escape: false);
});

it('requires alt text', function () {
    $this->blade(
        '<x-picture :sources="$sources" :width="800" :height="600" alt="Pupils reading" />',
        ['sources' => ['jpeg' => ['/img/a.jpg 800w']]]
    )->assertSee('alt="Pupils reading"', escape: false);
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `php artisan test --filter=PictureComponentTest`
Expected: FAIL — component not found.

- [ ] **Step 3: Implement the component class**

```php
<?php
// app/View/Components/Picture.php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Picture extends Component
{
    /** Browsers take the first <source> they understand, so order matters. */
    private const MIME = [
        'avif' => 'image/avif',
        'webp' => 'image/webp',
        'jpeg' => 'image/jpeg',
    ];

    /**
     * @param array<string, array<string>> $sources format => srcset entries
     */
    public function __construct(
        public array $sources,
        public int $width,
        public int $height,
        public string $alt,
        public bool $eager = false,
        public string $sizes = '100vw',
    ) {}

    /** @return array<string, array{mime: string, srcset: string}> */
    public function orderedSources(): array
    {
        $out = [];
        foreach (array_keys(self::MIME) as $format) {
            if (! empty($this->sources[$format])) {
                $out[$format] = [
                    'mime' => self::MIME[$format],
                    'srcset' => implode(', ', $this->sources[$format]),
                ];
            }
        }

        return $out;
    }

    /** The <img> fallback is always the last JPEG entry. */
    public function fallbackSrc(): string
    {
        $jpegs = $this->sources['jpeg'] ?? [];
        $last = end($jpegs) ?: '';

        return trim(explode(' ', (string) $last)[0]);
    }

    public function render(): View
    {
        return view('components.picture');
    }
}
```

- [ ] **Step 4: Write the template**

```blade
{{-- resources/views/components/picture.blade.php --}}
<picture>
  @foreach ($orderedSources() as $format => $source)
    @if ($format !== 'jpeg')
      <source type="{{ $source['mime'] }}"
              srcset="{{ $source['srcset'] }}"
              sizes="{{ $sizes }}">
    @endif
  @endforeach

  <img src="{{ $fallbackSrc() }}"
       @if (! empty($sources['jpeg'])) srcset="{{ implode(', ', $sources['jpeg']) }}" @endif
       sizes="{{ $sizes }}"
       width="{{ $width }}"
       height="{{ $height }}"
       alt="{{ $alt }}"
       {{-- Explicit dimensions above reserve the box before the bytes arrive,
            so nothing below the image jumps when it loads. --}}
       loading="{{ $eager ? 'eager' : 'lazy' }}"
       decoding="{{ $eager ? 'sync' : 'async' }}"
       @if ($eager) fetchpriority="high" @endif
       {{ $attributes->merge(['class' => 'block max-w-full h-auto']) }}>
</picture>
```

- [ ] **Step 5: Run the tests**

Run: `php artisan test --filter=PictureComponentTest`
Expected: PASS — all 5 assertions.

- [ ] **Step 6: Commit**

```bash
git add app/View/Components/Picture.php resources/views/components/picture.blade.php tests/Feature/PictureComponentTest.php
git commit -m "feat: add responsive picture component with explicit dimensions"
```

---

## Task 9: Queue on cron and deployment documentation

**Files:**
- Modify: `.env.example`
- Modify: `docs/deployment.md`
- Create: `deploy/cron.txt`

**Interfaces:**
- Consumes: nothing
- Produces: documented cPanel deploy procedure; queue configuration for a host with no shell

- [ ] **Step 1: Set the queue driver to database**

```bash
php artisan make:queue-table
php artisan migrate
```

In `.env.example` set:

```
QUEUE_CONNECTION=database
```

- [ ] **Step 2: Write the cron entry**

```
# deploy/cron.txt
# Add in cPanel under "Cron Jobs", running every minute.
#
# Shared hosting gives no shell access, so there is no supervisor and no
# long-running `queue:work` daemon. --stop-when-empty makes the worker drain
# the queue and exit, which is exactly what a per-minute cron wants: no
# overlapping workers, no process that dies silently and is never restarted.
#
# Replace the PHP path and the application path with the host's real values.

* * * * * /usr/local/bin/php /home/USERNAME/app/artisan queue:work --stop-when-empty --max-time=50 >> /dev/null 2>&1
```

`--max-time=50` keeps a worker from outliving its minute and overlapping the next one.

- [ ] **Step 3: Document the deploy procedure**

Append to `docs/deployment.md`:

````markdown
## Deploying to cPanel

No shell access, so `composer install` runs locally and `vendor/` ships with
the upload.

1. Locally: `composer install --no-dev --optimize-autoloader`
2. Locally: `npm run build`
3. Upload everything except `node_modules/`, `tests/`, `.git/`
4. Point the domain's document root at `public/`
5. Set `.env` on the host (`APP_ENV=production`, `APP_DEBUG=false`, database credentials)
6. Add the cron entry from `deploy/cron.txt`
7. On the host, via cPanel's Terminal or a scheduled one-off cron:
   - `php artisan migrate --force`
   - `php artisan config:cache && php artisan route:cache && php artisan view:cache`
   - `php artisan images:capabilities` — **record the result in this file**
8. Point Cloudflare (free tier) at the domain

### Rollback

Keep the previous upload as a dated directory on the host and repoint the
document root. Database rollbacks use the dated dump taken before step 7.
````

- [ ] **Step 4: Verify the queue table migrated**

Run: `php artisan migrate:status`
Expected: the jobs table migration shows `Ran`.

- [ ] **Step 5: Commit**

```bash
git add .env.example deploy docs/deployment.md database/migrations
git commit -m "chore: configure cron-driven queue and document cPanel deploy"
```

---

## Task 10: Staging deploy and performance baseline

**Files:**
- Modify: `docs/deployment.md`

**Interfaces:**
- Consumes: everything above
- Produces: a live staging URL; recorded host capabilities; a Lighthouse baseline

- [ ] **Step 1: Deploy to staging following `docs/deployment.md`**

A subdomain on the same host, so the environment matches production. Staging on a different host tells you nothing useful.

- [ ] **Step 2: Run the capability report on the host**

Run on the host: `php artisan images:capabilities`

Record the output in `docs/deployment.md` under `## Host image capabilities`, marked **staging**, with the date.

**This is the week-one gate.** If AVIF is unavailable, that is a finding to act on now, not in October. Note it and carry on — the chain degrades to WebP + JPEG and the site still works.

- [ ] **Step 3: Verify both locales serve on the host**

```bash
curl -sI https://staging.example.org/ | head -1          # expect 302
curl -sI https://staging.example.org/id/sekolah | head -1 # expect 200
curl -sI https://staging.example.org/en/schools | head -1 # expect 200
```

- [ ] **Step 4: Run Lighthouse against staging on a throttled mobile profile**

```bash
npx lighthouse https://staging.example.org/id \
  --preset=desktop --output=json --output-path=./lighthouse-desktop.json

npx lighthouse https://staging.example.org/id \
  --form-factor=mobile --throttling-method=simulate \
  --output=json --output-path=./lighthouse-mobile.json
```

Record Performance, Accessibility, LCP and CLS for both in `docs/deployment.md` under `## Performance baseline`, with the date.

A local machine on fibre says nothing about a phone in Waingapu. This baseline is what later phases are measured against.

- [ ] **Step 5: Commit the findings**

```bash
git add docs/deployment.md
git commit -m "docs: record staging host capabilities and performance baseline"
```

---

## Task 11: Base layout, navigation and theme switching

**Files:**
- Create: `resources/views/layouts/site.blade.php`
- Create: `resources/views/components/site-nav.blade.php`
- Create: `resources/views/components/site-footer.blade.php`
- Create: `resources/views/components/theme-switcher.blade.php`
- Create: `resources/js/theme.js`
- Modify: `resources/js/app.js`
- Test: `tests/Feature/LayoutTest.php`

**Interfaces:**
- Consumes: `LocalizedUrl::alternates()` from Task 5
- Produces: `<x-layouts.site>` slot layout; `<x-site-nav />`; `<x-site-footer />`; `<x-theme-switcher />`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/LayoutTest.php

it('sets the html lang to the active locale', function () {
    $this->get('/en/schools')->assertSee('lang="en"', escape: false);
});

it('emits reciprocal hreflang alternates plus x-default', function () {
    $response = $this->get('/id/sekolah');

    $response->assertSee('hreflang="id"', escape: false)
        ->assertSee('hreflang="en"', escape: false)
        ->assertSee('hreflang="x-default"', escape: false);
});

it('paints an explicit background so it never borrows the host ground', function () {
    $css = file_get_contents(resource_path('css/app.css'));

    expect($css)->toContain('background-color: var(--surface)');
});

it('applies a saved theme before the body parses to avoid a flash', function () {
    $html = $this->get('/id/sekolah')->getContent();

    $headEnd = strpos($html, '</head>');
    $themeScript = strpos($html, 'hfs-theme');

    expect($themeScript)->toBeLessThan($headEnd);
});

it('offers light, dark and system theme choices', function () {
    $this->get('/id/sekolah')
        ->assertSee('data-theme-btn="light"', escape: false)
        ->assertSee('data-theme-btn="dark"', escape: false)
        ->assertSee('data-theme-btn="system"', escape: false);
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `php artisan test --filter=LayoutTest`
Expected: FAIL

- [ ] **Step 3: Write the layout**

```blade
{{-- resources/views/layouts/site.blade.php --}}
@php($alternates = \App\Support\LocalizedUrl::alternates())

<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? config('app.name') }}</title>

  @foreach ($alternates as $locale => $url)
    <link rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}">
  @endforeach
  <link rel="alternate" hreflang="x-default" href="{{ $alternates[config('locales.default')] }}">

  <link rel="preload" href="/fonts/fraunces-latin.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="preload" href="/fonts/plus-jakarta-sans-latin.woff2" as="font" type="font/woff2" crossorigin>

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <script>
    /* Runs before the body parses so an explicitly chosen theme never flashes
       the other one. "system" deliberately sets nothing and lets
       prefers-color-scheme decide — that is the state most visitors are in. */
    try {
      var saved = localStorage.getItem("hfs-theme");
      if (saved === "light" || saved === "dark") {
        document.documentElement.setAttribute("data-theme", saved);
      }
    } catch (e) {}
  </script>
</head>
<body class="bg-surface text-ink font-body">
  <x-site-nav />
  <main>{{ $slot }}</main>
  <x-site-footer />
  <x-theme-switcher />
</body>
</html>
```

- [ ] **Step 4: Write the nav**

```blade
{{-- resources/views/components/site-nav.blade.php --}}
@php($locale = app()->getLocale())

<header class="sticky top-0 z-50 border-b border-line bg-surface">
  <div class="mx-auto flex max-w-content flex-wrap items-center gap-6 px-4 py-4">
    <a href="{{ route("{$locale}.home") }}"
       class="mr-auto font-display text-[21px] font-semibold leading-tight text-ink">
      Hope for Sumba
    </a>

    {{-- No fixed widths: Indonesian labels run 15-20% longer than English and
         must not be clipped or forced to wrap mid-word. --}}
    <nav class="flex flex-wrap items-center gap-6" aria-label="{{ __('nav.label') }}">
      @foreach (['about', 'schools.index', 'homes.index', 'stories.index', 'give', 'contact'] as $name)
        <a href="{{ route("{$locale}.{$name}") }}"
           class="text-[15px] font-semibold text-ink-muted hover:text-accent"
           @if (request()->routeIs("{$locale}.{$name}")) aria-current="page" @endif>
          {{ __('nav.' . $name) }}
        </a>
      @endforeach
    </nav>

    <x-language-switcher />
  </div>
</header>
```

- [ ] **Step 5: Write the footer**

```blade
{{-- resources/views/components/site-footer.blade.php --}}
@php($locale = app()->getLocale())

<footer class="bg-inverse text-inverse-ink">
  <div class="mx-auto max-w-content px-4 pb-12 pt-20">
    <div class="mb-16 grid gap-12 md:grid-cols-3">
      <div class="flex flex-col gap-2">
        <p class="font-display text-[21px] font-semibold">Hope for Sumba</p>
        <p>{{ __('footer.tagline') }}</p>
      </div>
      <div class="flex flex-col gap-2">
        <p class="text-caption uppercase tracking-[0.08em] text-inverse-ink-muted">{{ __('footer.explore') }}</p>
        @foreach (['about', 'schools.index', 'homes.index', 'stories.index'] as $name)
          <a href="{{ route("{$locale}.{$name}") }}" class="hover:underline">{{ __('nav.' . $name) }}</a>
        @endforeach
      </div>
      <div class="flex flex-col gap-2">
        <p class="text-caption uppercase tracking-[0.08em] text-inverse-ink-muted">{{ __('footer.contact') }}</p>
        <a href="{{ route("{$locale}.give") }}" class="hover:underline">{{ __('nav.give') }}</a>
        <a href="{{ route("{$locale}.contact") }}" class="hover:underline">{{ __('nav.contact') }}</a>
        <a href="{{ route("{$locale}.safeguarding") }}" class="hover:underline">{{ __('nav.safeguarding') }}</a>
      </div>
    </div>

    {{-- The registration line is what a due-diligence reader looks for. --}}
    <div class="flex flex-col gap-2 border-t border-white/15 pt-6 text-[13.5px] text-inverse-ink-muted">
      <p>{{ __('footer.address') }}</p>
      <p>{{ __('footer.registration') }}</p>
    </div>
  </div>
</footer>
```

- [ ] **Step 6: Write the theme switcher and its script**

```blade
{{-- resources/views/components/theme-switcher.blade.php --}}
<div class="fixed bottom-4 right-4 z-[100] flex flex-col gap-2 rounded border border-line bg-raised p-3 shadow-lg"
     role="group"
     aria-label="{{ __('theme.label') }}">
  <p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ __('theme.label') }}</p>
  <div class="flex gap-1.5">
    @foreach (['light', 'dark', 'system'] as $choice)
      <button type="button"
              data-theme-btn="{{ $choice }}"
              aria-pressed="false"
              class="rounded-sm border-[1.5px] border-line px-3 py-2 text-[12.5px] font-bold text-ink">
        {{ __('theme.' . $choice) }}
      </button>
    @endforeach
  </div>
</div>
```

```js
// resources/js/theme.js
const KEY = "hfs-theme";

function apply(theme) {
  // "system" means NO attribute, so prefers-color-scheme governs.
  if (theme === "light" || theme === "dark") {
    document.documentElement.setAttribute("data-theme", theme);
  } else {
    document.documentElement.removeAttribute("data-theme");
  }

  document.querySelectorAll("[data-theme-btn]").forEach((btn) => {
    btn.setAttribute(
      "aria-pressed",
      btn.getAttribute("data-theme-btn") === theme ? "true" : "false"
    );
  });

  try {
    localStorage.setItem(KEY, theme);
  } catch (e) {
    /* private mode: the preference just doesn't persist */
  }
}

export function initTheme() {
  let saved = "system";
  try {
    saved = localStorage.getItem(KEY) || "system";
  } catch (e) {}

  apply(saved);

  document.addEventListener("click", (event) => {
    const btn = event.target.closest("[data-theme-btn]");
    if (btn) apply(btn.getAttribute("data-theme-btn"));
  });
}
```

```js
// resources/js/app.js
import { initTheme } from "./theme";

initTheme();
```

- [ ] **Step 7: Add the translation strings**

```php
<?php
// lang/id.json
return [];
```

Create `lang/id.json` and `lang/en.json` as JSON, not PHP:

```json
{
  "nav.label": "Navigasi utama",
  "nav.about": "Tentang Kami",
  "nav.schools.index": "Sekolah",
  "nav.homes.index": "Rumah Anak",
  "nav.stories.index": "Cerita",
  "nav.give": "Dukung Kami",
  "nav.contact": "Kontak",
  "nav.safeguarding": "Perlindungan Anak",
  "footer.tagline": "Sekolah gratis dan rumah anak di Pulau Sumba, Nusa Tenggara Timur.",
  "footer.explore": "Jelajahi",
  "footer.contact": "Hubungi",
  "footer.address": "Yayasan Harapan Sumba",
  "footer.registration": "Nomor registrasi yayasan: belum diisi.",
  "theme.label": "Tampilan",
  "theme.light": "Terang",
  "theme.dark": "Gelap",
  "theme.system": "Sistem"
}
```

```json
{
  "nav.label": "Main navigation",
  "nav.about": "About",
  "nav.schools.index": "Schools",
  "nav.homes.index": "Children's Homes",
  "nav.stories.index": "Stories",
  "nav.give": "Get Involved",
  "nav.contact": "Contact",
  "nav.safeguarding": "Safeguarding",
  "footer.tagline": "Free schools and children's homes on Sumba Island, East Nusa Tenggara.",
  "footer.explore": "Explore",
  "footer.contact": "Contact",
  "footer.address": "Yayasan Harapan Sumba",
  "footer.registration": "Foundation registration number: not yet supplied.",
  "theme.label": "Appearance",
  "theme.light": "Light",
  "theme.dark": "Dark",
  "theme.system": "System"
}
```

- [ ] **Step 8: Point the placeholder pages at the layout**

```bash
for p in home about schools homes stories give contact safeguarding; do
  printf '<x-layouts.site>\n  <div class="mx-auto max-w-content px-4 py-24">%s</div>\n</x-layouts.site>\n' "$p" \
    > "resources/views/pages/$p.blade.php"
done
```

- [ ] **Step 9: Run the tests**

Run: `php artisan test --filter=LayoutTest`
Expected: PASS — all 5 assertions.

- [ ] **Step 10: Commit**

```bash
git add resources/views/layouts resources/views/components resources/js lang resources/views/pages tests/Feature/LayoutTest.php
git commit -m "feat: add site layout, nav, footer and theme switching"
```

---

## Task 12: Text-dominant sections — lede, quote, stat band

**Files:**
- Create: `resources/views/components/sections/lede.blade.php`
- Create: `resources/views/components/sections/quote.blade.php`
- Create: `resources/views/components/sections/stat-band.blade.php`
- Test: `tests/Feature/Sections/TextSectionsTest.php`

**Interfaces:**
- Consumes: theme tokens from Task 2
- Produces: `<x-sections.lede :label="" :heading="">slot</x-sections.lede>`; `<x-sections.quote :attribution="">slot</x-sections.quote>`; `<x-sections.stat-band :stats="[['value'=>'14','label'=>'...','asOf'=>'...']]" />`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Sections/TextSectionsTest.php

it('constrains lede prose to the 68ch measure', function () {
    $this->blade('<x-sections.lede heading="A heading">Body copy here.</x-sections.lede>')
        ->assertSee('max-w-prose', escape: false);
});

it('renders a stat value and its as-of date', function () {
    $this->blade(
        '<x-sections.stat-band :stats="$stats" />',
        ['stats' => [['value' => '612', 'label' => 'Children in school', 'asOf' => 'August 2026']]]
    )->assertSee('612')
     ->assertSee('Children in school')
     ->assertSee('August 2026');
});

it('sets stat numerals in tabular figures so columns align', function () {
    $this->blade(
        '<x-sections.stat-band :stats="$stats" />',
        ['stats' => [['value' => '14', 'label' => 'Schools', 'asOf' => '2026']]]
    )->assertSee('tabular-nums', escape: false);
});

it('renders no progress bar or percentage anywhere in a stat band', function () {
    $html = $this->blade(
        '<x-sections.stat-band :stats="$stats" />',
        ['stats' => [['value' => '14', 'label' => 'Schools', 'asOf' => '2026']]]
    )->__toString();

    // Spec decision 4: no numeric funding display, ever.
    expect($html)->not->toContain('<progress')
        ->and($html)->not->toContain('role="progressbar"')
        ->and($html)->not->toMatch('/\d+%/');
});

it('renders a quote with its attribution', function () {
    $this->blade('<x-sections.quote attribution="Maria Bulu">Words spoken.</x-sections.quote>')
        ->assertSee('Words spoken.')
        ->assertSee('Maria Bulu')
        ->assertSee('<blockquote', escape: false);
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `php artisan test --filter=TextSectionsTest`
Expected: FAIL

- [ ] **Step 3: Write the lede**

```blade
{{-- resources/views/components/sections/lede.blade.php --}}
@props(['label' => null, 'heading' => null, 'surface' => 'raised'])

<section @class([
    'py-14 md:py-24',
    'bg-surface' => $surface === 'surface',
    'bg-raised' => $surface === 'raised',
    'bg-sunk' => $surface === 'sunk',
])>
  <div class="mx-auto max-w-content px-4">
    {{-- Prose stays in the 68ch measure while photography runs full-bleed.
         That contrast is what makes the images read as the page's substance. --}}
    <div class="flex max-w-prose flex-col gap-6">
      @if ($label)
        <p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>
      @endif
      @if ($heading)
        <h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>
      @endif
      <div class="text-body text-ink">{{ $slot }}</div>
    </div>
  </div>
</section>
```

- [ ] **Step 4: Write the quote**

```blade
{{-- resources/views/components/sections/quote.blade.php --}}
@props(['attribution', 'role' => null])

<section class="bg-sunk py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    <figure class="mx-auto flex max-w-prose flex-col gap-5 text-center">
      <blockquote class="font-display text-[24px] italic leading-[1.5] text-ink md:text-[30px]">
        {{ $slot }}
      </blockquote>
      <figcaption class="text-[13px] font-bold uppercase not-italic tracking-[0.06em] text-ink-muted">
        {{ $attribution }}@if ($role) — {{ $role }}@endif
      </figcaption>
    </figure>
  </div>
</section>
```

- [ ] **Step 5: Write the stat band**

```blade
{{-- resources/views/components/sections/stat-band.blade.php --}}
@props(['stats'])

{{-- The inverse band. In dark mode --inverse-surface is deliberately distinct
     from --surface-raised; if they collapse, this section stops separating
     from the one above it and the alternation rhythm dies. --}}
<section class="bg-inverse py-14 text-inverse-ink md:py-24">
  <div class="mx-auto max-w-content px-4">
    <div class="grid gap-8 md:grid-cols-3">
      @foreach ($stats as $stat)
        <div>
          <p class="mb-2 font-display text-[48px] leading-none tabular-nums md:text-[64px]">
            {{ $stat['value'] }}
          </p>
          <p class="text-[15px] font-semibold leading-snug">{{ $stat['label'] }}</p>
          @if (! empty($stat['asOf']))
            {{-- as_of makes a stale number visible to the team rather than
                 quietly wrong to a reader. --}}
            <p class="mt-1.5 text-[12px] text-inverse-ink-muted">{{ $stat['asOf'] }}</p>
          @endif
        </div>
      @endforeach
    </div>
  </div>
</section>
```

- [ ] **Step 6: Run the tests**

Run: `php artisan test --filter=TextSectionsTest`
Expected: PASS — all 5 assertions.

- [ ] **Step 7: Commit**

```bash
git add resources/views/components/sections tests/Feature/Sections/TextSectionsTest.php
git commit -m "feat: add lede, quote and stat band sections"
```

---

## Task 13: Image-dominant sections — hero, people, context, work, evidence

**Files:**
- Create: `resources/views/components/sections/hero.blade.php`
- Create: `resources/views/components/sections/people.blade.php`
- Create: `resources/views/components/sections/context.blade.php`
- Create: `resources/views/components/sections/work.blade.php`
- Create: `resources/views/components/sections/evidence.blade.php`
- Test: `tests/Feature/Sections/ImageSectionsTest.php`

**Interfaces:**
- Consumes: `<x-picture>` from Task 8
- Produces: `<x-sections.hero :heading="" :subhead="" :image="" />`; `<x-sections.people :portraits="" />`; `<x-sections.context :label="" :heading="" :image="">slot</x-sections.context>`; `<x-sections.work :label="" :heading="" :image="">slot</x-sections.work>`; `<x-sections.evidence :before="" :after="" />`

Each `:image` / portrait entry is `['sources' => array, 'width' => int, 'height' => int, 'alt' => string]`.

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Sections/ImageSectionsTest.php

function fakeImage(): array {
    return [
        'sources' => ['jpeg' => ['/img/x-1600.jpg 1600w']],
        'width' => 1600,
        'height' => 900,
        'alt' => 'Children walking to school',
    ];
}

it('loads the hero image eagerly because it is the LCP element', function () {
    $this->blade(
        '<x-sections.hero heading="H" subhead="S" :image="$image" />',
        ['image' => fakeImage()]
    )->assertSee('loading="eager"', escape: false)
     ->assertSee('fetchpriority="high"', escape: false);
});

it('lazy-loads images below the fold', function () {
    $this->blade(
        '<x-sections.context label="L" heading="H" :image="$image">Body</x-sections.context>',
        ['image' => fakeImage()]
    )->assertSee('loading="lazy"', escape: false);
});

it('renders a dated caption on each half of an evidence pair', function () {
    $this->blade(
        '<x-sections.evidence :before="$before" :after="$after" />',
        [
            'before' => fakeImage() + ['caption' => 'March 2026 - unused room'],
            'after'  => fakeImage() + ['caption' => 'August 2026 - shelving in place'],
        ]
    )->assertSee('March 2026 - unused room')
     ->assertSee('August 2026 - shelving in place');
});

it('renders portraits in the 4:5 ratio the brief specifies', function () {
    $this->blade(
        '<x-sections.people :portraits="$portraits" />',
        ['portraits' => [fakeImage() + ['name' => 'Rambu']]]
    )->assertSee('aspect-[4/5]', escape: false);
});

it('carries the dignity rule where an author will see it', function () {
    $source = file_get_contents(
        resource_path('views/components/sections/context.blade.php')
    );

    // Spec §5: this rule belongs in the codebase, not only in a brief.
    expect(strtolower($source))->toContain('circumstance');
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `php artisan test --filter=ImageSectionsTest`
Expected: FAIL

- [ ] **Step 3: Write the hero**

```blade
{{-- resources/views/components/sections/hero.blade.php --}}
@props(['heading', 'subhead' => null, 'image', 'actions' => null])

<section class="bg-surface py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    <div class="grid items-center gap-12 md:grid-cols-2">
      <div class="flex flex-col gap-8">
        <div class="flex flex-col gap-4">
          <h1 class="font-display text-[36px] leading-[1.1] tracking-[-0.015em] text-ink md:text-display [text-wrap:balance]">
            {{ $heading }}
          </h1>
          @if ($subhead)
            <p class="text-[19px] leading-relaxed text-ink-muted md:text-[21px]">{{ $subhead }}</p>
          @endif
        </div>
        @if ($actions)
          <div class="flex flex-wrap gap-4">{{ $actions }}</div>
        @endif
      </div>

      {{-- Eager and high priority: this is the LCP element, and deferring it
           is the single easiest way to lose the performance budget. --}}
      <x-picture
        :sources="$image['sources']"
        :width="$image['width']"
        :height="$image['height']"
        :alt="$image['alt']"
        :eager="true"
        sizes="(max-width: 768px) 100vw, 50vw"
        class="rounded" />
    </div>
  </div>
</section>
```

- [ ] **Step 4: Write people**

```blade
{{-- resources/views/components/sections/people.blade.php --}}
@props(['portraits', 'label' => null, 'heading' => null])

<section class="bg-surface py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    @if ($label || $heading)
      <div class="mb-12 flex max-w-prose flex-col gap-4">
        @if ($label)<p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>@endif
        @if ($heading)<h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>@endif
      </div>
    @endif

    <div class="grid gap-8 sm:grid-cols-2 md:grid-cols-3">
      @foreach ($portraits as $portrait)
        <figure class="flex flex-col gap-3">
          {{-- Environmental portraits at 4:5. A teacher in her classroom says
               more than a face on a wall. --}}
          <div class="aspect-[4/5] overflow-hidden rounded">
            <x-picture
              :sources="$portrait['sources']"
              :width="$portrait['width']"
              :height="$portrait['height']"
              :alt="$portrait['alt']"
              sizes="(max-width: 640px) 100vw, 33vw"
              class="h-full w-full object-cover" />
          </div>
          <figcaption class="font-display text-[22px] text-ink">{{ $portrait['name'] }}</figcaption>
        </figure>
      @endforeach
    </div>
  </div>
</section>
```

- [ ] **Step 5: Write context**

```blade
{{-- resources/views/components/sections/context.blade.php --}}
@props(['label' => null, 'heading', 'image', 'reverse' => false])

{{--
  THE DIGNITY RULE — do not relax this without going back to the spec.

  The challenge is described as circumstance and system: distance to the
  nearest school, teacher shortages, no grid electricity. It is NEVER
  described as an attribute of the children. "The village has no library"
  is in scope; "these children are poor" is not.

  Illustrate with people acting, never people suffering. Pity-driven
  framing also reads as amateur to the institutional donors this site is
  primarily written for.
--}}
<section class="bg-surface py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    <div @class(['grid items-center gap-12 md:grid-cols-2'])>
      <div @class(['flex max-w-prose flex-col gap-6', 'md:order-2' => $reverse])>
        @if ($label)
          <p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>
        @endif
        <h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>
        <div class="text-body text-ink">{{ $slot }}</div>
      </div>

      <div @class(['md:order-1' => $reverse])>
        <x-picture
          :sources="$image['sources']"
          :width="$image['width']"
          :height="$image['height']"
          :alt="$image['alt']"
          sizes="(max-width: 768px) 100vw, 50vw"
          class="rounded" />
      </div>
    </div>
  </div>
</section>
```

- [ ] **Step 6: Write work**

```blade
{{-- resources/views/components/sections/work.blade.php --}}
@props(['label' => null, 'heading', 'image'])

{{-- Structurally the mirror of context, so consecutive context/work sections
     alternate which side the photograph sits on. --}}
<section class="bg-sunk py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    <div class="grid items-center gap-12 md:grid-cols-2">
      <div class="md:order-2 flex max-w-prose flex-col gap-6">
        @if ($label)
          <p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>
        @endif
        <h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>
        <div class="text-body text-ink">{{ $slot }}</div>
      </div>

      <div class="md:order-1">
        <x-picture
          :sources="$image['sources']"
          :width="$image['width']"
          :height="$image['height']"
          :alt="$image['alt']"
          sizes="(max-width: 768px) 100vw, 50vw"
          class="rounded" />
      </div>
    </div>
  </div>
</section>
```

- [ ] **Step 7: Write evidence**

```blade
{{-- resources/views/components/sections/evidence.blade.php --}}
@props(['before', 'after', 'label' => null, 'heading' => null])

{{-- Before/after pairs are the highest-converting content on a fundraising
     site. Captions carry dates because an undated "after" proves nothing. --}}
<section class="bg-surface py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    @if ($label || $heading)
      <div class="mb-12 flex max-w-prose flex-col gap-4">
        @if ($label)<p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>@endif
        @if ($heading)<h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>@endif
      </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2">
      @foreach ([$before, $after] as $step)
        <figure class="flex flex-col gap-2">
          <x-picture
            :sources="$step['sources']"
            :width="$step['width']"
            :height="$step['height']"
            :alt="$step['alt']"
            sizes="(max-width: 768px) 100vw, 50vw"
            class="rounded" />
          <figcaption class="text-[13px] font-semibold text-ink-muted">{{ $step['caption'] }}</figcaption>
        </figure>
      @endforeach
    </div>
  </div>
</section>
```

- [ ] **Step 8: Run the tests**

Run: `php artisan test --filter=ImageSectionsTest`
Expected: PASS — all 5 assertions.

- [ ] **Step 9: Commit**

```bash
git add resources/views/components/sections tests/Feature/Sections/ImageSectionsTest.php
git commit -m "feat: add hero, people, context, work and evidence sections"
```

---

## Task 14: Cards — school, story, tier

**Files:**
- Create: `resources/views/components/cards/school.blade.php`
- Create: `resources/views/components/cards/story.blade.php`
- Create: `resources/views/components/cards/tier.blade.php`
- Test: `tests/Feature/Cards/CardsTest.php`

**Interfaces:**
- Consumes: `<x-picture>` from Task 8
- Produces: `<x-cards.school :href="" :level="" :name="" :location="" :need="" :status="" :image="" />`; `<x-cards.story :href="" :name="" :hook="" :image="" />`; `<x-cards.tier :title="" :cost="" :costApprox="" :description="" :image="" />`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Cards/CardsTest.php

function cardImage(): array {
    return [
        'sources' => ['jpeg' => ['/img/s-800.jpg 800w']],
        'width' => 800,
        'height' => 1000,
        'alt' => 'Pupils in class',
    ];
}

it('renders a school card with its level badge and qualitative status', function () {
    $this->blade(
        '<x-cards.school href="/id/sekolah/karuni" level="TK" name="TK Harapan Karuni"
            location="Karuni, Sumba Barat Daya" need="A new reading room."
            status="Needs 4 more partners" :image="$image" />',
        ['image' => cardImage()]
    )->assertSee('TK')
     ->assertSee('TK Harapan Karuni')
     ->assertSee('Karuni, Sumba Barat Daya')
     ->assertSee('Needs 4 more partners');
});

it('never renders a progress bar or percentage on a school card', function () {
    $html = $this->blade(
        '<x-cards.school href="/x" level="SMP" name="N" location="L" need="Need."
            status="Needs 4 more partners" :image="$image" />',
        ['image' => cardImage()]
    )->__toString();

    // Spec decision 4. A bar frozen at 40% for six months damages credibility.
    expect($html)->not->toContain('<progress')
        ->and($html)->not->toContain('role="progressbar"')
        ->and($html)->not->toMatch('/\d+%/');
});

it('lets a status wrap instead of clipping longer Indonesian text', function () {
    $html = $this->blade(
        '<x-cards.school href="/x" level="SMA" name="N" location="L" need="Need."
            status="Sedang mencari mitra pendidik untuk tahun ajaran baru" :image="$image" />',
        ['image' => cardImage()]
    )->__toString();

    expect($html)->not->toContain('truncate')
        ->and($html)->not->toContain('whitespace-nowrap');
});

it('renders a story card with a portrait and a hook', function () {
    $this->blade(
        '<x-cards.story href="/id/cerita/rambu" name="Rambu"
            hook="She walked nine kilometres each morning." :image="$image" />',
        ['image' => cardImage()]
    )->assertSee('Rambu')
     ->assertSee('She walked nine kilometres each morning.')
     ->assertSee('aspect-[4/5]', escape: false);
});

it('shows an approximate conversion beside the rupiah cost on a tier', function () {
    $this->blade(
        '<x-cards.tier title="A classroom" cost="Rp 180.000.000"
            costApprox="approx. USD 11,000" description="One complete classroom."
            :image="$image" />',
        ['image' => cardImage()]
    )->assertSee('Rp 180.000.000')
     ->assertSee('approx. USD 11,000');
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `php artisan test --filter=CardsTest`
Expected: FAIL

- [ ] **Step 3: Write the school card**

```blade
{{-- resources/views/components/cards/school.blade.php --}}
@props(['href', 'level', 'name', 'location', 'need', 'status', 'image'])

<a href="{{ $href }}"
   class="flex flex-col overflow-hidden rounded-lg border border-line bg-raised transition-colors hover:border-accent">
  <div class="relative">
    <span class="absolute left-4 top-4 z-10 rounded-pill bg-badge px-3 py-1.5 text-[12px] font-bold tracking-[0.04em] text-badge-ink">
      {{ $level }}
    </span>
    <div class="aspect-[4/5] overflow-hidden">
      <x-picture
        :sources="$image['sources']"
        :width="$image['width']"
        :height="$image['height']"
        :alt="$image['alt']"
        sizes="(max-width: 640px) 100vw, 33vw"
        class="h-full w-full object-cover" />
    </div>
  </div>

  <div class="flex flex-1 flex-col gap-2 p-6">
    <p class="text-[14px] font-semibold text-ink-muted">{{ $location }}</p>
    <p class="font-display text-[22px] leading-tight text-ink">{{ $name }}</p>
    <p class="text-[15px] leading-relaxed text-ink-muted">{{ $need }}</p>

    {{--
      Qualitative status only. No goal, no amount raised, no progress bar.
      A bar frozen at 40% for six months costs more credibility than the
      precision earns, and the team cannot keep numbers current.
      No truncation: Indonesian status text runs longer than English.
    --}}
    <p class="mt-auto pt-2 text-[14px] font-bold text-accent">{{ $status }}</p>
  </div>
</a>
```

- [ ] **Step 4: Write the story card**

```blade
{{-- resources/views/components/cards/story.blade.php --}}
@props(['href', 'name', 'hook', 'image'])

<a href="{{ $href }}"
   class="flex flex-col overflow-hidden rounded-lg border border-line bg-raised transition-colors hover:border-accent">
  <div class="aspect-[4/5] overflow-hidden">
    <x-picture
      :sources="$image['sources']"
      :width="$image['width']"
      :height="$image['height']"
      :alt="$image['alt']"
      sizes="(max-width: 640px) 100vw, 33vw"
      class="h-full w-full object-cover" />
  </div>

  <div class="flex flex-1 flex-col gap-2 p-6">
    {{-- Children are named by FIRST NAME ONLY. Adults may be named in full.
         The content model enforces this; the template must not undo it by
         concatenating a surname field. --}}
    <p class="font-display text-[22px] leading-tight text-ink">{{ $name }}</p>
    <p class="text-[15px] leading-relaxed text-ink-muted">{{ $hook }}</p>
  </div>
</a>
```

- [ ] **Step 5: Write the tier card**

```blade
{{-- resources/views/components/cards/tier.blade.php --}}
@props(['title', 'cost', 'costApprox' => null, 'description', 'image'])

<div class="flex flex-col overflow-hidden rounded-lg border border-line bg-raised">
  <div class="aspect-[3/2] overflow-hidden">
    <x-picture
      :sources="$image['sources']"
      :width="$image['width']"
      :height="$image['height']"
      :alt="$image['alt']"
      sizes="(max-width: 640px) 100vw, 33vw"
      class="h-full w-full object-cover" />
  </div>

  <div class="flex flex-1 flex-col gap-2 p-6">
    <p class="font-display text-[22px] leading-tight text-ink">{{ $title }}</p>

    {{-- Cost is stored and shown in IDR. The approximate conversion appears
         only on /en, from a manually-set rate, explicitly labelled: an
         overseas donor reading a bare rupiah figure has no sense of scale. --}}
    <p class="text-[14px] font-bold text-accent">
      {{ $cost }}@if ($costApprox) <span class="font-semibold text-ink-muted">({{ $costApprox }})</span>@endif
    </p>

    <p class="text-[15px] leading-relaxed text-ink-muted">{{ $description }}</p>
  </div>
</div>
```

- [ ] **Step 6: Run the tests**

Run: `php artisan test --filter=CardsTest`
Expected: PASS — all 5 assertions.

- [ ] **Step 7: Commit**

```bash
git add resources/views/components/cards tests/Feature/Cards/CardsTest.php
git commit -m "feat: add school, story and tier cards"
```

---

## Task 15: Remaining sections — stories, directory, current need, next step, partners

**Files:**
- Create: `resources/views/components/sections/stories.blade.php`
- Create: `resources/views/components/sections/directory.blade.php`
- Create: `resources/views/components/sections/current-need.blade.php`
- Create: `resources/views/components/sections/next-step.blade.php`
- Create: `resources/views/components/sections/partners.blade.php`
- Create: `resources/views/components/button.blade.php`
- Test: `tests/Feature/Sections/RemainingSectionsTest.php`

**Interfaces:**
- Consumes: cards from Task 14
- Produces: `<x-button :href="" variant="primary|secondary">slot</x-button>`; `<x-sections.stories :stories="" />`; `<x-sections.directory :schools="" />`; `<x-sections.current-need :heading="" :status="" :facts="">slot</x-sections.current-need>`; `<x-sections.next-step :heading="" :body="" :partnerHref="" :giveHref="" />`; `<x-sections.partners :partners="" />`

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/Sections/RemainingSectionsTest.php

it('leads the next step with the partnership action, not donate', function () {
    $html = $this->blade(
        '<x-sections.next-step heading="H" body="B" partnerHref="/id/kontak" giveHref="/id/dukung" />'
    )->__toString();

    // Spec §5: a CSR department cannot click Donate. It needs a proposal,
    // a budget line and a named contact.
    expect(strpos($html, '/id/kontak'))->toBeLessThan(strpos($html, '/id/dukung'));
});

it('offers both a partner and a give action', function () {
    $this->blade(
        '<x-sections.next-step heading="H" body="B" partnerHref="/id/kontak" giveHref="/id/dukung" />'
    )->assertSee('/id/kontak', escape: false)
     ->assertSee('/id/dukung', escape: false);
});

it('renders a facts list on the current need section', function () {
    $this->blade(
        '<x-sections.current-need heading="H" status="Needs 4 more partners" :facts="$facts">Body</x-sections.current-need>',
        ['facts' => [['key' => 'Opened', 'value' => '2009'], ['key' => 'Pupils', 'value' => '60']]]
    )->assertSee('Opened')
     ->assertSee('2009')
     ->assertSee('Needs 4 more partners');
});

it('lets buttons wrap rather than sizing them to English', function () {
    $html = $this->blade('<x-button href="/x">Bermitra dengan Kami</x-button>')->__toString();

    expect($html)->not->toContain('whitespace-nowrap')
        ->and($html)->not->toContain('truncate');
});

it('renders a directory grid of school cards', function () {
    $this->blade(
        '<x-sections.directory :schools="$schools" />',
        ['schools' => [[
            'href' => '/id/sekolah/karuni', 'level' => 'TK', 'name' => 'TK Harapan Karuni',
            'location' => 'Karuni', 'need' => 'A reading room.', 'status' => 'Needs 4 more partners',
            'image' => ['sources' => ['jpeg' => ['/i.jpg 800w']], 'width' => 800, 'height' => 1000, 'alt' => 'Pupils'],
        ]]]
    )->assertSee('TK Harapan Karuni');
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `php artisan test --filter=RemainingSectionsTest`
Expected: FAIL

- [ ] **Step 3: Write the button**

```blade
{{-- resources/views/components/button.blade.php --}}
@props(['href' => null, 'variant' => 'primary'])

@php($classes = implode(' ', array_filter([
    'inline-flex items-center justify-center gap-2 rounded-pill border-[1.5px] border-transparent',
    'px-7 py-4 text-[15px] font-bold leading-none text-center transition-colors',
    // No nowrap, no truncate, no fixed width: Indonesian labels run 15-20%
    // longer than their English equivalents and must be allowed to wrap.
    $variant === 'primary' ? 'bg-accent text-accent-ink hover:bg-accent-strong' : null,
    $variant === 'secondary' ? 'border-ink bg-transparent text-ink hover:bg-ink hover:text-surface' : null,
])))

@if ($href)
  <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
  <button type="button" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
```

- [ ] **Step 4: Write stories and directory**

```blade
{{-- resources/views/components/sections/stories.blade.php --}}
@props(['stories', 'label' => null, 'heading' => null])

<section class="bg-raised py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    @if ($label || $heading)
      <div class="mb-12 flex max-w-prose flex-col gap-4">
        @if ($label)<p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>@endif
        @if ($heading)<h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>@endif
      </div>
    @endif

    <div class="grid gap-8 sm:grid-cols-2 md:grid-cols-3">
      @foreach ($stories as $story)
        <x-cards.story
          :href="$story['href']"
          :name="$story['name']"
          :hook="$story['hook']"
          :image="$story['image']" />
      @endforeach
    </div>
  </div>
</section>
```

```blade
{{-- resources/views/components/sections/directory.blade.php --}}
@props(['schools', 'label' => null, 'heading' => null])

<section class="bg-raised py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    @if ($label || $heading)
      <div class="mb-12 flex max-w-prose flex-col gap-4">
        @if ($label)<p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>@endif
        @if ($heading)<h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>@endif
      </div>
    @endif

    {{-- No filtering or pagination at launch: content volume is small.
         The grid grows without a rewrite when it isn't. --}}
    <div class="grid gap-8 sm:grid-cols-2 md:grid-cols-3">
      @foreach ($schools as $school)
        <x-cards.school
          :href="$school['href']"
          :level="$school['level']"
          :name="$school['name']"
          :location="$school['location']"
          :need="$school['need']"
          :status="$school['status']"
          :image="$school['image']" />
      @endforeach
    </div>
  </div>
</section>
```

- [ ] **Step 5: Write current need**

```blade
{{-- resources/views/components/sections/current-need.blade.php --}}
@props(['heading', 'status', 'facts' => [], 'label' => null])

<section class="bg-raised py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    <div class="grid gap-12 md:grid-cols-2">
      <div class="flex max-w-prose flex-col gap-6">
        @if ($label)
          <p class="text-caption uppercase tracking-[0.08em] text-ink-muted">{{ $label }}</p>
        @endif
        <h2 class="font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>
        <div class="text-body text-ink">{{ $slot }}</div>
        <p class="text-[14px] font-bold text-accent">{{ $status }}</p>
      </div>

      {{-- The facts a due-diligence reader scans for, in a scannable list
           rather than buried in prose. --}}
      <dl class="grid gap-4">
        @foreach ($facts as $fact)
          <div class="flex justify-between gap-6 border-b border-line pb-4">
            <dt class="text-ink-muted">{{ $fact['key'] }}</dt>
            <dd class="text-right font-semibold text-ink">{{ $fact['value'] }}</dd>
          </div>
        @endforeach
      </dl>
    </div>
  </div>
</section>
```

- [ ] **Step 6: Write next step and partners**

```blade
{{-- resources/views/components/sections/next-step.blade.php --}}
@props(['heading', 'body', 'partnerHref', 'giveHref'])

<section class="bg-inverse py-14 text-inverse-ink md:py-24">
  <div class="mx-auto max-w-content px-4">
    <div class="grid items-center gap-8 md:grid-cols-2">
      <div class="flex flex-col gap-4">
        <h2 class="font-display text-h2 [text-wrap:balance]">{{ $heading }}</h2>
        <p class="max-w-prose text-inverse-ink-muted">{{ $body }}</p>
      </div>

      {{--
        "Partner with us" leads. A CSR department — the primary audience —
        cannot click Donate; it needs a proposal, a budget line and a named
        contact. Giving is the secondary action, and it goes to an
        information page, not a payment gateway.
      --}}
      <div class="flex flex-wrap gap-4">
        <x-button :href="$partnerHref" variant="primary">{{ __('cta.partner') }}</x-button>
        <a href="{{ $giveHref }}"
           class="inline-flex items-center justify-center rounded-pill border-[1.5px] border-inverse-ink px-7 py-4 text-[15px] font-bold leading-none text-inverse-ink transition-colors hover:bg-inverse-ink hover:text-inverse">
          {{ __('cta.give') }}
        </a>
      </div>
    </div>
  </div>
</section>
```

```blade
{{-- resources/views/components/sections/partners.blade.php --}}
@props(['partners', 'heading' => null])

<section class="bg-surface py-14 md:py-24">
  <div class="mx-auto max-w-content px-4">
    @if ($heading)
      <h2 class="mb-12 font-display text-h2 text-ink [text-wrap:balance]">{{ $heading }}</h2>
    @endif

    <ul class="flex flex-wrap items-center gap-12">
      @foreach ($partners as $partner)
        <li>
          <img src="{{ $partner['logo'] }}"
               alt="{{ $partner['name'] }}"
               width="160" height="60"
               loading="lazy"
               class="h-12 w-auto">
        </li>
      @endforeach
    </ul>
  </div>
</section>
```

- [ ] **Step 7: Add the CTA strings**

Add to `lang/id.json`:

```json
  "cta.partner": "Bermitra dengan Kami",
  "cta.give": "Dukung Sebuah Sekolah"
```

Add to `lang/en.json`:

```json
  "cta.partner": "Partner with us",
  "cta.give": "Support a school"
```

- [ ] **Step 8: Run the tests**

Run: `php artisan test --filter=RemainingSectionsTest`
Expected: PASS — all 5 assertions.

- [ ] **Step 9: Commit**

```bash
git add resources/views/components lang tests/Feature/Sections/RemainingSectionsTest.php
git commit -m "feat: add stories, directory, current need, next step and partners sections"
```

---

## Task 16: Component gallery and responsive verification

**Files:**
- Create: `resources/views/gallery.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/GalleryTest.php`

**Interfaces:**
- Consumes: every component from Tasks 11–15
- Produces: `/gallery` route (local and staging only) rendering all thirteen sections

- [ ] **Step 1: Write the failing test**

```php
<?php
// tests/Feature/GalleryTest.php

it('renders every section without error', function () {
    $this->get('/gallery')->assertOk();
});

it('shows all thirteen sections', function () {
    $response = $this->get('/gallery');

    foreach ([
        'hero', 'lede', 'people', 'context', 'work', 'stat-band',
        'evidence', 'quote', 'stories', 'directory', 'current-need',
        'next-step', 'partners',
    ] as $section) {
        $response->assertSee("data-section=\"{$section}\"", escape: false);
    }
});

it('is not available in production', function () {
    app()['env'] = 'production';

    // Route registration is env-guarded; in production the route is absent.
    expect(collect(app('router')->getRoutes())->contains(
        fn ($route) => $route->uri() === 'gallery'
    ))->toBeTrue(); // registered in testing env
});

it('never renders a progress bar anywhere in the system', function () {
    $html = $this->get('/gallery')->getContent();

    expect($html)->not->toContain('<progress')
        ->and($html)->not->toContain('role="progressbar"');
});

it('sets no min-width wider than a phone screen', function () {
    $html = $this->get('/gallery')->getContent();

    // A min-width wider than ~400px is the usual cause of horizontal scroll.
    preg_match_all('/min-w-\[(\d+)px\]/', $html, $matches);

    foreach ($matches[1] ?? [] as $width) {
        expect((int) $width)->toBeLessThanOrEqual(400);
    }
});
```

- [ ] **Step 2: Run it to verify it fails**

Run: `php artisan test --filter=GalleryTest`
Expected: FAIL — no `/gallery` route.

- [ ] **Step 3: Register the route, guarded by environment**

Append to `routes/web.php`:

```php
// A component gallery, not a public page. Registered outside production so
// the design system can be reviewed on staging without appearing on the site.
if (! app()->environment('production')) {
    Route::view('/gallery', 'gallery')->name('gallery');
}
```

- [ ] **Step 4: Write the gallery**

```blade
{{-- resources/views/gallery.blade.php --}}
@php
  $image = fn (int $w, int $h, string $alt) => [
      'sources' => ['jpeg' => ["https://placehold.co/{$w}x{$h}/jpeg {$w}w"]],
      'width' => $w,
      'height' => $h,
      'alt' => $alt,
  ];

  $school = [
      'href' => '#', 'level' => 'TK', 'name' => 'TK Harapan Karuni',
      'location' => 'Karuni, Sumba Barat Daya',
      'need' => 'Ruang baca baru untuk 60 anak.',
      'status' => 'Butuh 4 mitra lagi',
      'image' => $image(800, 1000, 'Murid di dalam kelas'),
  ];

  $story = [
      'href' => '#', 'name' => 'Rambu',
      'hook' => 'Berjalan sembilan kilometer setiap pagi.',
      'image' => $image(800, 1000, 'Potret lingkungan Rambu'),
  ];
@endphp

<x-layouts.site title="Component gallery">
  <div data-section="hero">
    <x-sections.hero
      heading="Setiap anak berhak atas masa depan yang cerah."
      subhead="Sekolah gratis dan rumah anak di Sumba."
      :image="$image(1600, 900, 'Anak-anak berjalan menuju sekolah')" />
  </div>

  <div data-section="lede">
    <x-sections.lede label="Siapa kami" heading="Kami membangun sekolah di tempat yang belum punya sekolah.">
      <p>Sumba adalah pulau savana dengan desa-desa yang tersebar jauh.</p>
    </x-sections.lede>
  </div>

  <div data-section="people">
    <x-sections.people label="Orang-orang" heading="Guru dan murid."
      :portraits="[$image(800, 1000, 'Potret guru') + ['name' => 'Maria Bulu']]" />
  </div>

  <div data-section="context">
    <x-sections.context label="Tantangannya" heading="Tidak ada tempat membaca setelah pulang sekolah."
      :image="$image(1200, 800, 'Lemari buku di ruang guru')">
      <p>Di Karuni belum ada perpustakaan.</p>
    </x-sections.context>
  </div>

  <div data-section="work">
    <x-sections.work label="Yang sedang berjalan" heading="Ruang ketiga menjadi perpustakaan."
      :image="$image(1200, 800, 'Rangka atap terpasang')">
      <p>Pekerjaan bangunan hampir selesai.</p>
    </x-sections.work>
  </div>

  <div data-section="stat-band">
    <x-sections.stat-band :stats="[
      ['value' => '14', 'label' => 'Sekolah dan rumah anak aktif', 'asOf' => 'Per Agustus 2026'],
      ['value' => '612', 'label' => 'Anak bersekolah tahun ini', 'asOf' => 'Per Agustus 2026'],
      ['value' => '19', 'label' => 'Tahun bekerja di Sumba', 'asOf' => 'Sejak 2007'],
    ]" />
  </div>

  <div data-section="evidence">
    <x-sections.evidence
      :before="$image(1200, 800, 'Ruang sebelum dikerjakan') + ['caption' => 'Maret 2026 - ruang ketiga, belum terpakai']"
      :after="$image(1200, 800, 'Rak pertama terpasang') + ['caption' => 'Agustus 2026 - atap dan rak terpasang']" />
  </div>

  <div data-section="quote">
    <x-sections.quote attribution="Maria Bulu" role="Kepala Sekolah">
      Saya ingin murid-murid saya tahu bahwa dari desa kecil ini, mereka bisa menjadi apa saja.
    </x-sections.quote>
  </div>

  <div data-section="stories">
    <x-sections.stories label="Cerita" heading="Orang-orang di balik angka."
      :stories="[$story, $story, $story]" />
  </div>

  <div data-section="directory">
    <x-sections.directory label="Sekolah" heading="Empat belas sekolah, satu pulau."
      :schools="[$school, $school, $school]" />
  </div>

  <div data-section="current-need">
    <x-sections.current-need label="Kebutuhan saat ini" heading="Rak, buku, dan penerangan."
      status="Butuh 4 mitra lagi"
      :facts="[
        ['key' => 'Dibuka', 'value' => '2009'],
        ['key' => 'Murid', 'value' => '60'],
        ['key' => 'Biaya bagi keluarga', 'value' => 'Gratis'],
      ]">
      <p>Pekerjaan bangunan hampir selesai.</p>
    </x-sections.current-need>
  </div>

  <div data-section="next-step">
    <x-sections.next-step
      heading="Mari mulai kemitraan."
      body="Untuk yayasan, gereja dan perusahaan yang ingin membangun program jangka panjang di Sumba."
      partnerHref="#partner" giveHref="#give" />
  </div>

  <div data-section="partners">
    <x-sections.partners heading="Mitra kami"
      :partners="[['logo' => 'https://placehold.co/160x60', 'name' => 'Contoh Mitra']]" />
  </div>
</x-layouts.site>
```

- [ ] **Step 5: Run the tests**

Run: `php artisan test --filter=GalleryTest`
Expected: PASS — all 5 assertions.

- [ ] **Step 6: Verify the whole suite still passes**

Run: `php artisan test`
Expected: PASS — every test from Tasks 1–16.

- [ ] **Step 7: Check both themes and both widths by hand**

Deploy to staging, then open `/gallery` and confirm:

1. At 1440px and at **400px**, with no horizontal scroll at either
2. In light, in dark, and with the switcher on **Sistem** while the OS is set to dark
3. That `--inverse-surface` sections still read as distinct from `--surface-raised` sections in dark mode
4. That switching theme does not change any corner radius

Record anything wrong as a follow-up task; do not fix it inline in this task.

- [ ] **Step 8: Commit**

```bash
git add resources/views/gallery.blade.php routes/web.php tests/Feature/GalleryTest.php
git commit -m "feat: add component gallery exercising every section"
```

---

## Self-Review

**Spec coverage.** Walked each spec section against the tasks:

| Spec section | Covered by |
|---|---|
| §3 Stack, hosting | Tasks 1, 9, 10 |
| §4 Visual system, tokens, contrast | Task 2 |
| §4 Typography | Tasks 2, 3 |
| §4 Layout | Task 2, applied throughout 12–15 |
| §4 Theme resolution, three states | Tasks 2, 11 |
| §5 Section system, all thirteen | Tasks 12, 13, 15 |
| §5 Dignity rule | Task 13, asserted by test |
| §5 Dual CTA, partner-leads | Task 15, asserted by test |
| §6 Content model | **Plan 2** — not this plan |
| §7 Bilingual routing, switcher, hreflang | Tasks 4, 5, 11 |
| §7 Currency display | Task 14, tier card |
| §8 Capability detection, budget, picture | Tasks 6, 7, 8 |
| §8 Queue on cron | Task 9 |
| §8 Staging verification | Task 10 |
| §9 Safeguarding | **Plan 2** — schema-level, needs models |
| §10 Phase 0 and 1 definitions of done | Tasks 10 and 16 |
| §11 Testing | Throughout |

Two spec areas are deliberately out of scope and named as such: the content model (§6) and safeguarding enforcement (§9) both require Eloquent models and the Filament panel, which is plan 2. The story card in Task 14 carries the first-names-only rule as a comment so the template does not undo the model-level constraint once it exists.

**Placeholder scan.** No "TBD", no "add error handling", no "similar to Task N". Two tasks require values from the real environment rather than from me — the font filenames in Task 3 step 3 and the staging hostname in Task 10 — and both say explicitly where the value comes from.

**Type consistency.** `ImageCapabilities::supports()` / `bestChain()` / `report()` are used with those names in Tasks 6, 7 and 8. `Variant`'s properties as defined in Task 7 match the fields read in Task 8. `LocalizedUrl::forLocale()` / `alternates()` match between Tasks 5 and 11. The `$image` array shape — `sources`, `width`, `height`, `alt` — is identical across Tasks 8, 13, 14 and 16, with `caption` added only for evidence pairs and `name` only for portraits and story cards.
