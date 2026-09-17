# Outstanding findings — fix wave (re-run, 2026-09-17)

All eight findings from the interrupted whole-branch review are closed.
Full suite: **195 tests, 524 assertions, green** (up from 190/511 — five
new tests added, none removed).

## 1. README told agents to install Laravel Boost

Replaced with a short project README (stack, setup, `php artisan test`,
pointers to the design spec, implementation plan, and `docs/data-contract.md`
as authoritative). Security contact now points at "the project maintainers"
instead of `taylor@laravel.com`. 37 lines, under the 40-line target.

## 2. Build shipped ~100KB of unused Instrument Sans

Removed the `bunny('Instrument Sans', …)` plugin usage from `vite.config.js`
and the `'Instrument Sans'` token from `--font-sans` in `resources/css/app.css`
(now `'Plus Jakarta Sans'`, matching `--font-body`). Rebuilt: `public/build/`
now contains only `app-*.css`/`app-*.js` — no `instrument-sans-*` files, no
generated `fonts-*.css`. `grep -rli "bunny\|instrument" public/build/` and
`resources/` both return nothing. Widened `docs/fonts.md`'s verification
grep to also check for `bunny`/`instrument` by name, not just Google's
hosts, since a host-only grep is exactly what let this go unnoticed.

## 3. No skip link (WCAG 2.4.1)

Added a skip link as the first focusable element in `<body>` in
`resources/views/components/layouts/site.blade.php`, `sr-only` until
`:focus` (Tailwind's built-in utilities), then fixed-position with a
visible accent background and a 2px outline. Added `id="main"` to `<main>`.
New test in `tests/Feature/LayoutTest.php` asserts the link exists before
the nav, targets `#main`, and that `<main id="main">` is present. Verified
against both themes via the existing `--color-accent`/`--color-accent-ink`
tokens (no new colors introduced, so both light and dark already work).
Added `nav.skip_to_content` to `lang/en.json` and `lang/id.json`.

## 4. Pull quotes rendered as synthetic oblique

Fetched the italic Fraunces latin and latin-ext woff2 files from the same
Google Fonts CSS API query already on file in `docs/fonts.md` (network was
available — not blocked). Added them at `public/fonts/fraunces-italic-latin.woff2`
(81,520 B) and `public/fonts/fraunces-italic-latin-ext.woff2` (71,460 B),
added matching `@font-face` rules with `font-style: italic` in
`resources/css/fonts.css`, and updated `docs/fonts.md`'s file table and
total (now 328,748 bytes / ~321 KiB). `resources/views/components/sections/quote.blade.php`
needed no change — it already applies `italic`; the browser now has a real
italic face to select instead of slanting the roman.

## 5. ImageCapabilities / VariantGenerator disagreed on imaging library

`ImageCapabilities` now exposes `driver(): DriverInterface` — Imagick when
`extension_loaded('imagick')`, GD otherwise — and `detectAvif()`/`detectWebp()`
check formats against **that same selected library only**, not the union of
GD and Imagick. `VariantGenerator` now builds its `ImageManager` from
`$this->capabilities->driver()` instead of hardcoding `new GdDriver()`.

Added tests in `tests/Feature/ImageCapabilitiesTest.php`: driver selection
(Imagick vs GD) and that the reported avif/webp values match detection
against the selected driver specifically. Added webp and avif generation
tests in `tests/Feature/VariantGeneratorTest.php` (previously only `'jpeg'`
was ever exercised), skipped cleanly via `->skip()` when the runtime can't
encode that format — on this machine (GD with `imageavif`/`imagewebp`) both
ran for real and passed.

Widened `docs/deployment.md`'s host note to cover the commoner mixed case
(GD without AVIF + Imagick with AVIF, or vice versa) rather than only "a
host with Imagick and no GD."

## 6. VariantGenerator re-decoded the source every quality step

Hoisted `$manager->decode($sourcePath)->scaleDown(width: $width)` out of the
`foreach (self::QUALITY_STEPS as $quality)` loop in
`app/Services/Images/VariantGenerator.php` — decode/scale run once, `encode()`
runs per quality step (it doesn't mutate the image, so this is safe).
Existing tests (`width`, `height`, quality-stepping, budget, floor-flag)
all still pass unchanged, confirming behavior is identical.

## 7. Two tests pinned this machine into the suite

`tests/Feature/ImageCapabilitiesTest.php` had two assertions hardcoding
`['avif' => true, 'webp' => true, 'jpeg' => true]`. Both now assert against
live `function_exists()`/`extension_loaded()` detection (matching the logic
in `ImageCapabilities` itself, since that's exactly what's under test), with
the same skip guard used elsewhere in the file for hosts with neither
extension. The "stale cache" test was changed from a positive pin to a
negative assertion (not equal to the deliberately-wrong fake value seeded
by the previous test) — this is what that test actually needed to prove.

## 8. Lighthouse baseline documented already-fixed bugs

Ran Lighthouse for real: `php artisan serve --port=8123`, then
`npx lighthouse http://127.0.0.1:8123/id/sekolah --form-factor=mobile --throttling-method=simulate --chrome-flags="--headless"`.
Replaced the placeholder-era entry in `docs/deployment.md` with a dated
re-run. Accessibility is now 1.0/100 (was 0.86) — `document-title` and
`landmark-one-main` are both closed and covered by `LayoutTest`. Kept the
existing discipline: LCP, TTFB, and the overall Performance score are still
omitted, with the reason (localhost has no real latency/contention)
restated, and marked pending staging. Noted the real remaining flags
(`is-on-https` — expected on plain-HTTP localhost; `errors-in-console` — a
local artisan-serve-without-vite-dev-server artifact; `meta-description` —
real and host-independent, not something this task was asked to fix).

## Not completed / deferred

Nothing from the eight findings was left undone. Two things adjacent to
this work were noticed but intentionally left alone as out of scope:

- `resources/views/welcome.blade.php` (Laravel's default scaffold page,
  unrouted) still contains an inline `'Instrument Sans'` reference in its
  embedded styles. It isn't reachable by any route and isn't part of the
  build pipeline findings called out in #2, so it was left as-is rather
  than editing a file the findings didn't name.
- The `errors-in-console` Best Practices failure from the Lighthouse re-run
  (404s for `[::1]:5173` font requests) is an artifact of running
  `php artisan serve` without a Vite dev server, not a production defect —
  noted in `docs/deployment.md` rather than "fixed," since there's nothing
  to fix in the built asset pipeline.
