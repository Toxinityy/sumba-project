# Agent A — pages workstream report

## Status

Done: locale defect fixed, fixture layer built, all four launch pages
(School detail, Schools directory, Home, Get Involved) built and wired
into `routes/web.php`. Full suite passes: **154 tests, 360 assertions,
0 failures** (`php artisan test`).

## Commits

1. `4adb791` — fix: single-source the default locale from config/locales.php
2. `bbd5334` — feat: build fixtures and four launch pages

## The fixture API (for Agent B to match)

`app/ViewModels/`:

- `SchoolData::all(): array` — directory shape for every school (see
  data-contract.md § School).
- `SchoolData::find(string $slug): ?array` — directory shape merged with
  the detail-page fields (`lede`, `people`, `context`, `work`, `evidence`,
  `facts`) when a full profile exists, `null` for an unknown slug, or a
  **directory-only array with no `lede` key** for a school that's listed
  but has no detail profile yet. Pages treat "no `lede` key" as "no detail
  page" and 404 — see routes/web.php's `schools.show` closure.
- `StatData::all(): array` — the three home-page stats.
- `PostData::recent(int $n): array` — the `$n` most recent posts, newest
  first.
- `TierData::all(): array` — the six sponsorship tiers, `costApprox`
  populated only when `app()->getLocale() === 'en'`.
- `PlaceholderImage::make(int $width, int $height, string $alt): array` —
  builds one image-shape entry (a placehold.co URL + a `jpeg` source, per
  the contract's mandatory-fallback rule). Shared by every fixture above.
- `Concerns/ResolvesLocale` — a one-method trait (`pick(string $id, string
  $en): string`) every fixture class uses instead of hand-rolling the same
  ternary. This is the only "beyond data in, array out" logic in any of
  them, and it's exactly the locale resolution the contract asks for.

All five fixture classes carry a header comment naming
`docs/data-contract.md` and stating they're temporary. At integration,
each `::all()`/`::find()`/`::recent()` call in `routes/web.php` swaps for
the Eloquent equivalent — no Blade template needs to change if the shapes
still match.

## Contract gaps found while building real pages

None of these are contract *violations* — the shapes SchoolData, StatData,
PostData and TierData produce match docs/data-contract.md exactly. These
are gaps in the **spine tables** (spec §5) that the component set doesn't
cover, discovered because a real page needed to render them:

1. **Home's spine names a "Featured school" section that doesn't exist**
   among the thirteen. I reused `<x-sections.work>` (image+text+slot,
   its own surface tone) rather than add or edit a component — see the
   comment in `resources/views/pages/home.blade.php`.
2. **Get Involved's spine names four sections that don't exist**: "Tiers",
   "Corporate", "Church", "Volunteer", "How giving works". There's no
   component for a plain 3-column text block or a tier grid. I used inline
   markup on the same surface tokens and padding rhythm the section
   components use (reusing `<x-cards.tier>` for the grid itself), rather
   than adding a `resources/views/components/**` file — see the comment in
   `resources/views/pages/give.blade.php`. This is the one place I came
   closest to wanting a component change; I did not make it.
3. **`PostData`'s `href` points at the stories index, not a per-post URL.**
   Post/story detail pages are deferred past launch (spec §10), so no
   `stories.show` route exists yet to build a real href from. When that
   route exists, `PostData`'s (or the model's) `href` should point at it
   instead — a one-line change, not a shape change.
4. **Five of six schools in `SchoolData` have no detail profile**, matching
   `prototype/build.js` (which itself only built one school detail page —
   `sekolah-karuni.html`). Visiting `/id/sekolah/anakalang` (etc.) 404s.
   Worth knowing before seeding real data: only Karuni was ever fully
   written up.

## Components I wanted to change but did not

Only the Get Involved gap above came close. I did not edit or add anything
under `resources/views/components/**`.

## Other things fixed along the way

- `tests/Feature/LanguageSwitcherTest.php`'s ancestor-fallback test used
  to register a fake `id.schools.show` to exercise "a route that exists
  only in one locale." That's no longer fake — `schools.show` is now real
  in both locales — so I swapped the fixture to `homes.show` (which
  genuinely doesn't exist yet) rather than leave a test asserting a
  now-false premise.
- `tests/Feature/LayoutTest.php`'s "always emits a non-empty title" test
  checked `/id/sekolah` for the literal default title. That page now has
  its own real title, so the check moved to `/id/tentang` (still an
  unbuilt placeholder view).
- `phpunit.xml` needed `memory_limit=512M`: with the added page/fixture
  coverage, the full suite ran two dozen more feature tests before
  `VariantGeneratorTest`'s image work, and 128M wasn't enough headroom by
  then. This isn't page code, but the suite doesn't pass green without it.
- `.env` (gitignored, local only) had `APP_LOCALE=en` hardcoded, silently
  overriding the new config/app.php default and defeating the locale-fix
  test. Set to `id` to match `.env.example`.

## Not done / deferred (out of scope per the brief)

- About, Children's Homes, Stories, Contact, Safeguarding pages remain
  the placeholder `<div>` views — not among the four pages assigned.
- Per-post ("story detail") and per-school-without-a-profile routes don't
  exist — matches spec §10's deferred scope and the prototype's own
  coverage.
