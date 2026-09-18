# Landing-page redesign — implementation report

**Date:** 2026-09-18
**Branch:** `design/spec-and-prototype`
**Source of truth:** `docs/design/landing-opus.html` (approved design pass)
**Binding documents:** `docs/superpowers/specs/2026-09-16-hope-for-sumba-design.md`
(§4 accent contrast, §4 Fraunces range, §5 section geometry — all amended in
`26d62cd`) and `docs/data-contract.md`.

**Suite:** 268 tests, 839 assertions, green (was 210 / 603).
**Build:** `npm run build` clean.

---

## 1. What changed, in one paragraph

The home page now carries the whole narrative in eleven sections, the nav drops
from ten items to four, and the six pages folded out of the nav keep their
routes and are linked from the landing sections they belong to and from the
footer. The section components did not multiply — the existing fifteen gained
`pad`, `tone`, `container` and `variant` props, and the landing page is
composed entirely from them. The level pill is gone; every school now shows the
whole TK/SMP/SMA ladder with its own rung lit. Fraunces is driven on its `opsz`
and `wght` axes instead of being pinned at 500. Grey placeholder boxes are gone,
replaced by token-built gradient plates that theme correctly and fetch nothing.

---

## 2. Section geometry — how the props map to the design

Spec §5's amendment asks for four padding steps, four container relationships,
load-bearing overlap and one dark chapter. All four are in.

### Padding

`resources/css/landing.css` §2 declares the four steps plus three joins:

| class | value | used by |
|---|---|---|
| `.pad-xl` | `clamp(4.5rem, 11vw, 9.5rem)` | people, challenge, work, stories, next step |
| `.pad-l` | `clamp(3.5rem, 8vw, 7rem)` | declared; unused on the landing page |
| `.pad-m` | `clamp(2.75rem, 5.5vw, 4.5rem)` | partners — the fastest section on the page |
| `.pad-s` | `clamp(2rem, 3.5vw, 2.75rem)` | declared; unused on the landing page |
| `.pad-open` | `xl 0` | scale — opens the dark chapter |
| `.pad-mid` | `0` | evidence — inside the chapter, no boundary of its own |
| `.pad-close` | `l / clamp(7rem,13vw,12rem)` | the voice — the extra floor the overlap below needs |
| `.pad-cont` | `0 / xl` | schools — receives the overlap |

The hero's padding is bespoke (top only, `0` bottom) because the plate has to
hang past the hero's own lower edge.

`.pad-l` and `.pad-s` are declared and tested but not used by the landing page.
They are the two steps the deep pages will take when their geometry is revisited
— the scale exists as a scale, not as two live classes and two dead ones.

### Containers

1. **1200px content width** — `.ed-wrap`
2. **1440px band** — `.ed-wrap--wide` (hero and nav; the nav shares it so the
   wordmark sits on the masthead's left edge)
3. **Full bleed to the viewport** — `.ed-work__wide`, a real negative margin
   measured against the container: `calc(-1 * (var(--gutter) + max(0px, (100vw - 1200px)/2)))`
4. **Offset into the grid** — `.ed-offset`, columns 4–12 rather than centred

### The three overlaps

1. **Hero plate** — bleeds past the right gutter (`margin-right: calc(-1 * var(--gutter))`)
   and hangs `-4.5rem` below the hero's boundary.
2. **Lead school card** — `margin-top: clamp(-6rem, -9vw, -3rem)` pulls it up
   across the dark chapter's lower edge. Measured in the browser: the chapter
   ends at 5822px and the schools section starts at 5726px, so they genuinely
   interlock rather than abut.
3. **Pull quote** — breaks its own measure (`max-width: 30ch` against a 68ch
   prose column) with `text-indent: -0.44em` hanging the opening quote mark
   outside it.

### One dark chapter

Scale, evidence and the voice are three `<section>` elements sharing one
`--inverse-surface` ground with the padding of a single section distributed
across them (`open` / `mid` / `close`). The reader sees one continuous field;
the DOM keeps three landmarks. `LandingPageTest` asserts the three are
contiguous and that their joins are exactly `open, mid, close`.

---

## 3. Components that needed a prop I did not expect

Four, all reported rather than quietly added:

1. **`<x-sections.work>` needed a second image and a named `note` slot.** The
   interlock is a wide plate, copy hanging beside it, a *narrow* plate under,
   and a trailing note in the copy column. `imageNarrow` / `plateNarrow` /
   `note` cover the last three. Nothing else on the site uses them.

2. **`<x-sections.next-step>` absorbed the ways.** The design's closing section
   is one tinted field with the head in columns 1–6 and four rule-separated
   ways in 7–13. Two stacked sections could not produce that relationship, so
   `next-step` gained a `ways` array prop. `<x-sections.ways>` is unchanged and
   still used by Get Involved — the landing page simply does not call it. This
   is the one place a landing section duplicates another section's job, and it
   is the trade I would revisit first.

3. **A `more` slot on three sections** (`stat-band`, `stories`, `directory`).
   The folded pages have to be linked from the landing section they belong to,
   and a trailing link is not a heading, a body or a card. One slot name,
   three components, same placement.

4. **A `en` gloss prop on seven sections.** The design sets an English line
   under each Indonesian heading. It is driven from a `*.gloss` lang key
   holding the *other* language, with `lang="{{ __('meta.other_locale') }}"`,
   so on `/en` the gloss is Indonesian and the `lang` attribute is correct in
   both directions. A hardcoded `lang="en"` would have lied on half the site.

Also new, and not section props: `<x-plate>` and `<x-level-ladder>` components,
and an `ageRange` prop on `<x-cards.school>`.

---

## 4. The level ladder

`resources/views/components/level-ladder.blade.php` +
`resources/css/landing.css` §4. It replaces the `bg-badge` pill that used to
float over the card image.

- Lit rung: Fraunces `opsz 14 / wght 700` at **27px** in full `--ink`, with a
  **3px `--accent` rule** in the left gutter.
- Unlit rungs: Plus Jakarta Sans at `--ink-muted` — **6.85:1 on surface,
  7.31:1 on raised, 6.24:1 on sunk**. Never dropped below the threshold to
  look "off". Differentiation is optical size, weight and the rule.
- The lit rung carries the age range beneath it; below 760px the ladder
  collapses to a row with the age range pushed right (`margin-left: auto`).
- Unlit rungs are `aria-hidden="true"`; the lit one carries a visually-hidden
  `, jenjang sekolah ini` / `, this school's level`. A screen reader hears one
  level per school, not nine.

`LandingPageTest` extracts every ladder on the page and asserts, per ladder:
all three rungs present, exactly one `is-on`, exactly two `aria-hidden`, exactly
one visually-hidden qualifier — plus that across the six ladders all three rungs
get lit, so a ladder that lit the same rung every time fails.

### `docs/data-contract.md` needs an `age_range` key

The age ranges are new data. `App\ViewModels\SchoolData` now emits
`age_range` in the **directory** shape, derived from `level` so the two cannot
disagree:

```php
'age_range' => self::ageRange($level),   // TK 4-6, SMP 12-15, SMA 15-18
```

**The School shape in `docs/data-contract.md` needs this key added so the data
layer produces it.** I have not edited the contract: that document says
changing a shape requires agreement from both sides, and this is one side. The
proposed addition, in the directory shape beside `level`:

```php
'age_range' => '12-15 tahun',   // localised; derived from level, never stored apart from it
```

It is a localised string, so it follows the contract's rule 5 (strings arrive
formatted) and rule 4 (Indonesian is the primary case — `ages 12-15` is
shorter than `12-15 tahun`, and nothing is sized to it).

### A second, smaller contract note: `Stat.body`

The challenge ledger reads the Stat shape as a figure ledger: `value` is the
figure, `label` is the bolded opening clause, and an **optional `body`** key
finishes the sentence. `asOf` was already optional, so this is consistent, but
it is a key the contract does not list. `StatData::challenge()` produces it and
only `<x-sections.stat-band variant="ledger">` reads it.

---

## 5. Fraunces

Fourteen distinct axis settings are now in the built stylesheet, against one
pinned weight before:

| role | axes | where |
|---|---|---|
| masthead | `opsz 144, wght 330` | `.ed-masthead` |
| masthead accent (italic) | `opsz 144, wght 360` | `.ed-masthead em` |
| statistics | `opsz 144, wght 280` | `.ed-stat__fig` |
| ledger figures | `opsz 144, wght 290` | `.ed-fig` |
| pull quote (italic) | `opsz 72, wght 320` | `.ed-quote` |
| section head | `opsz 48, wght 420` | `.ed-h2` |
| sub-head | `opsz 32, wght 500` | `.ed-h3` |
| need (lead card) | `opsz 30, wght 460` | `.ed-need` |
| need (rail row) | `opsz 24, wght 450` | `.ed-schoolrow .ed-need` |
| person name | `opsz 24, wght 560` | `.ed-name` |
| wordmark | `opsz 24, wght 600` | `.ed-brand` |
| partner monogram | `opsz 20, wght 640` | `.ed-mono` |
| evidence date | `opsz 18, wght 620` | `.ed-evid figcaption b` |
| **level mark** | **`opsz 14, wght 700`** | `.lvl__i.is-on` |

The distance between the masthead (`opsz 144 / wght 330`) and the level mark
(`opsz 14 / wght 700`) is the whole editorial effect, and it costs no new
colour and no new typeface.

`font-optical-sizing: none` is set wherever `opsz` is set by hand. Without it
the browser derives `opsz` from the font size and the explicit setting does
nothing — the self-hosted file's `opsz` default is 9, so the effect would have
been the opposite of intended.

### Could not port: `SOFT` and `WONK`

The design sets `SOFT` (soft terminals on the pull quote) and `WONK` (the
wonky `g`/`y` on the masthead and section heads). **The self-hosted files do
not carry those axes.** Verified directly:

```
public/fonts/fraunces-latin.woff2        → [('opsz', 9, 9, 144), ('wght', 100, 900, 900)]
public/fonts/fraunces-italic-latin.woff2 → [('opsz', 9, 9, 144), ('wght', 100, 900, 900)]
```

`docs/fonts.md`'s Google Fonts query asked for `ital,opsz,wght` only, so Google
served the font instanced at the `SOFT`/`WONK` defaults. Writing those settings
into the CSS anyway would have been a no-op that read as working. They are
omitted, and `landing.css` §1 says why at the top of the file.

**To get them:** re-fetch with `family=Fraunces:ital,opsz,wght,SOFT,WONK@...`
and re-subset. That is a change to `public/fonts/**` and `docs/fonts.md`, both
of which belong to the fonts pass, not to this one — the two files would grow
and the preload budget would need re-checking.

---

## 6. Placeholder plates

`<x-plate>` + `landing.css` §3. Three tonal variants (`field`, `grass`, `dusk`)
built from `color-mix()` over palette tokens only — no hex literals except the
one `#000` darkening stop in `plate--dusk`. Each is captioned with the
photograph that belongs in it, on a **solid** `--inverse-surface` ground rather
than on the gradient, so the caption's ratio is a fixed **13.96:1 light /
11.53:1 dark** whatever the fill is doing.

The plate is `aria-hidden="true"`: it stands in for a photograph that does not
exist, and announcing "Photograph: children walking to school" would describe an
image that is not there.

Side effect worth naming: the landing page now makes **zero external image
requests**. The fixtures previously pointed `<x-picture>` at `placehold.co`, so
every page view hit a third-party host. `LandingPageTest` asserts
`placehold.co` does not appear in the landing HTML.

**Not built: the `background-image` migration path.** The design describes a
plate taking a `background-image` and dropping its `::before` and caption when
real photography lands. There is no real photography and no `MediaAsset` model
yet, so building the swap now would be speculative. What matters is that it
stays cheap: aspect ratio, bleed, overlap and caption geometry all live in the
frame, not in the fill, so nothing on the page moves when the fill changes.

---

## 7. Contrast — every new pairing, computed

Relative-luminance formula, same as `tests/Unit/TokenContrastTest.php`. All of
these are now in that test's datasets.

### New tokens

`--inverse-accent: #e8a23a` and `--inverse-accent-ink: #241a0e`, declared in all
three theme blocks. Needed because light `--accent` (#96660e) is only **1.6:1**
on `--inverse-surface` — the dark chapter could not have used the surface accent
for its labels, dates and links.

| pairing | ratio | |
|---|---|---|
| `inverse-accent` on light `inverse-surface` #1E2A22 | **6.85:1** | AA |
| `inverse-accent` on dark `inverse-surface` #3A2E22 | **6.06:1** | AA |
| `inverse-accent-ink` on `inverse-accent` | **7.86:1** | AA |

### Grounds this redesign newly uses for text

| pairing | ratio | |
|---|---|---|
| light `ink` on `surface-sunk` | **12.70:1** | AA |
| light `ink-muted` on `surface-sunk` | **6.24:1** | AA |
| light `badge-ink` on `surface-sunk` | **8.41:1** | AA |
| light `ink` on `badge-bg` | **11.70:1** | AA |
| light `ink-muted` on `badge-bg` | **5.74:1** | AA |
| light `badge-ink` on `badge-bg` | **7.74:1** | AA |
| light `ink` on `surface-raised` | **14.90:1** | AA |
| light `ink-muted` on `surface-raised` | **7.31:1** | AA |
| light `inverse-ink` on `inverse-surface` (plate caption) | **13.96:1** | AA |
| light `inverse-ink-muted` on `inverse-surface` | **8.12:1** | AA |
| dark `ink` on `surface-sunk` | **15.96:1** | AA |
| dark `ink-muted` on `surface-sunk` | **9.97:1** | AA |
| dark `badge-ink` on `surface-sunk` | **11.60:1** | AA |
| dark `ink` on `badge-bg` | **11.53:1** | AA |
| dark `ink-muted` on `badge-bg` | **7.20:1** | AA |
| dark `badge-ink` on `badge-bg` | **8.38:1** | AA |
| dark `ink` on `surface-raised` | **13.51:1** | AA |
| dark `ink-muted` on `surface-raised` | **8.44:1** | AA |
| dark `inverse-ink` on `inverse-surface` (plate caption) | **11.53:1** | AA |
| dark `inverse-ink-muted` on `inverse-surface` | **7.66:1** | AA |

The unlit ladder rungs are `--ink-muted` and therefore appear above at 6.24 /
7.31 / 6.85:1 depending on the ground. **They are never below the threshold.**

### The accent rule (spec §4)

`--accent` was **not** darkened. Instead, the three classes that could have
reached for it switch by ground:

```css
.ed-label { color: var(--accent); }
.on-sunk .ed-label, .on-tint .ed-label { color: var(--badge-ink); }
.on-chapter .ed-label, .on-inverse .ed-label { color: var(--inverse-accent); }
```

Same pattern for `.ed-txtlink` and `.ed-fig`. `TokenContrastTest` now does what
the spec correction asked for: it asserts the **accent-family** text colour
against **every** surface token rather than a hand-picked list, and a second
test pins the two failing ratios (4.26:1 on sunk, 3.92:1 on badge-bg) so
"just use `text-accent`, it passed on surface" is contradicted by a number in
the suite rather than by a paragraph in a document. `LandingPageTest` also
extracts every `on-sunk` / `on-tint` section from the rendered page and asserts
none of them contains a bare `text-accent` utility (`text-accent-ink` on an
accent *background* is fine at 5.00:1 and is what the primary button uses).

---

## 8. Maria Bulu — TK Harapan Karuni

`lang/{id,en}.json` placed her at **SMP Harapan Anakalang**; `PostData` tells
her story as the head teacher who returned to **TK Harapan Karuni**. Both
shipped.

**TK Harapan Karuni wins.** Three reasons, in order of weight:

1. `PostData` does not merely name the school, it tells a story that depends on
   it — eleven years in Kupang, a letter from her own village, enrolment
   growing from twenty to sixty, the third room under construction being her
   idea. That paragraph cannot be moved to Anakalang without rewriting it.
2. Karuni's own `people` list in `SchoolData` already includes her, and
   `resources/views/gallery.blade.php` already quoted her as "Kepala Sekolah,
   TK Harapan Karuni".
3. The conflicting version existed in exactly one place — two lines of the two
   lang files — and said nothing that would be lost.

Fixed in `lang/id.json` and `lang/en.json` (`about.people.maria`). The landing
page's pull quote uses her existing reviewed quote from `PostData`, role
included, rather than a fourth copy of the same words in a lang key.

A side effect worth noting: SMP Harapan Anakalang's head teacher is now
**unnamed** in the challenge ledger ("the head teacher covers the lessons
between her own"). That is correct — inventing a name for her would be worse —
but if the client wants her named, `SchoolData`'s Anakalang `people` list has
Ana Kolimon and Daniel Awang and one of them needs a real role attached.

`LandingPageTest` asserts the consistency in both locales, in the fixture, and
on both rendered pages.

---

## 9. Could not port / deliberately did not port

1. **`SOFT` and `WONK`** — the font files do not carry the axes. See §5.

2. **The hero plate's bleed to the *viewport* edge above 1441px.** The design
   has `margin-right: calc((1440px - 100vw)/2 - var(--gutter))`. `100vw`
   includes the classic scrollbar, so on a scrollbar-reserving platform it
   overshoots by half the scrollbar width and the page gains ~8px of horizontal
   scroll — which the spec forbids at any width. Clipping it at the root
   element removed the scroll but made Chrome mispaint scrolled content, so
   that was backed out too. **The bleed past the gutter, which is the move the
   brief names, is intact.** Above the 1440px band the plate now stops at the
   band's edge rather than the window's. Visible only on screens wider than
   ~1550px, and only as slightly more margin.

3. **The design's mobile disclosure menu (`<details>`).** With four items
   instead of ten the bar wraps cleanly and needs no disclosure. Dropping ten
   items to four was the fix; a menu behind a button would have been a second
   fix for a problem that no longer exists.

4. **`<x-sections.ways>` on the landing page.** See §3, point 2.

5. **The `.ed-rise` scroll animation** uses `animation-timeline: view()`, which
   is behind `@supports` and simply does not run in Safari/Firefox today.
   Content is fully visible without it; it is progressive enhancement, and it
   collapses entirely under `prefers-reduced-motion`.

6. **`docs/design/landing-opus.html` is still untracked** (along with
   `landing-editorial.html` and `landing-opus.artifact.html`). This report and
   `landing.css`'s header both cite it as the source of truth, so somebody
   should commit it — it was untracked before this pass and is not this
   workstream's file to add.

---

## 10. Two Blade traps hit while building this, recorded so nobody re-finds them

1. **Never write `<x-something>` inside a `//` comment in an `@props([...])`
   block.** `BladeCompiler::compileString` strips `{{-- --}}` comments *before*
   it compiles component tags, but a PHP `//` comment is still plain text at
   that point — so `<x-picture>` in a props comment compiles into a real,
   unclosed component and the page 500s with `Undefined variable $component`.
   `{{-- --}}` comments are safe; `//` comments are not.

2. **A bare text node beside a `<span>` inside a flex container loses the
   whitespace between them.** The wordmark rendered as "Hope forSumba" until
   the whole label became one flex item.

---

## 11. Files touched

**New**
- `resources/css/landing.css`
- `resources/views/components/plate.blade.php`
- `resources/views/components/level-ladder.blade.php`
- `app/ViewModels/PartnerData.php`
- `tests/Feature/Pages/LandingPageTest.php`
- `docs/landing-redesign-report.md`

**Changed**
- `resources/css/app.css`, `resources/css/tokens.css`
- `resources/views/pages/home.blade.php`, `resources/views/pages/partners.blade.php`
- `resources/views/components/site-nav.blade.php`, `site-footer.blade.php`, `cards/school.blade.php`
- `resources/views/components/sections/{hero,people,stat-band,work,evidence,quote,directory,stories,partners,next-step}.blade.php`
- `app/ViewModels/SchoolData.php`, `app/ViewModels/StatData.php`
- `routes/web.php`, `lang/id.json`, `lang/en.json`
- `tests/Unit/TokenContrastTest.php`, `tests/Feature/Pages/HomePageTest.php`

**Untouched, as instructed:** `app/Models/**`, `database/**`, `composer.json`,
`composer.lock`. No composer package was installed.
