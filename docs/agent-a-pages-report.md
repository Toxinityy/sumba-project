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

---

# Agent A — round 2: the remaining launch pages

## Status

Done: About, Children's Homes, Stories, Contact and Safeguarding built, so
every page in spec §10's launch scope now renders real content in both
locales. No placeholder `<div>` views remain. Full suite passes: **171
tests, 419 assertions, 0 failures** (`php artisan test`).

## What each page maps onto

| Page | Sections used |
|---|---|
| About | hero → lede (Our story) → work (Founder) → lede (Mission & vision) → people → quote → next-step |
| Children's Homes | hero → work (care model) → lede (privacy) → next-step |
| Stories | hero → stories → next-step |
| Contact | hero → inline form + office details |
| Safeguarding | hero → lede (rules) → lede (removal) |

"Our story", "Founder" and "Mission & vision" are spine entries in spec §5
with no matching component among the thirteen, same situation as Get
Involved in round 1. Each maps onto the section whose anatomy already fits,
rather than growing the component set.

## Two deliberate deviations, both flagged rather than resolved quietly

1. **Safeguarding runs two text-dominant sections back to back**, which
   spec §5 rule 1 forbids. The rule exists so photography carries the page,
   and the only photographs that would break up a child-protection policy
   are photographs of children — the precise thing the policy governs.
   Surface tones (raised, then sunk) separate the sections instead. The
   deviation is commented in the template.
2. **Children's Homes is an overview, not a directory.** Spec §5 gives a
   children's home "the same shape as School detail with stricter media
   rules" — that is the shape of one home's detail page. There is no Home
   fixture or model to build a directory from (docs/data-contract.md has no
   Home entry), so the page is the overview the prototype shipped. When
   Home records exist, a `<x-sections.directory>` slots in between the
   care-model and privacy sections and nothing else changes.

## The contact form

The site's primary CTA is "Partner with us", so the form it leads to has to
actually work. `POST` handler lives in `routes/web.php` alongside the other
page routes:

- **Spam protection is a honeypot plus `throttle:5,1`.** Both cost nothing
  and stop automated volume. Neither asks a CSR officer to read distorted
  letters. A captcha is the upgrade path if spam actually gets through — it
  is marked with a `ponytail:` comment in the template, not pre-added.
- **Delivery is `Mail::raw`**, not a Mailable plus a Blade template: four
  fields read by one person. `replyTo` is the enquirer, so hitting reply
  works.
- **Where it goes**: `config('mail.contact_to')` (`CONTACT_TO` in the env,
  added to `.env.example`). Same value is printed on the page, so the
  address shown and the address delivered to cannot drift apart.
- **`Mail::fake()` is not used in the tests.** `MailFake::raw()` is a no-op,
  so every assertion against it passes whether or not mail was sent.
  `phpunit.xml` already sets `MAIL_MAILER=array`, whose transport keeps the
  real message — the test asserts recipient, reply-to and body.

## Two things fixed at the root rather than worked around

- **`<x-button>` could not make a submit button.** It rendered a literal
  `type="button"` before `$attributes`, and HTML keeps the FIRST of two
  duplicate attributes — so `<x-button type="submit">` silently produced a
  dead button. `type` now goes through `merge()`, where a caller's value
  wins. This is the first edit to anything under `components/**` in either
  round; it is a defect fix, not a design change.
- **`LayoutTest`'s default-title test no longer points at a page.** It
  asserted the fallback via "whichever page is still a placeholder", which
  broke every time such a page got built — twice now, and there are none
  left to point at. It renders the layout directly instead, which is the
  contract actually being tested.

## Still not done

- **Story detail pages** (`stories.show`) — deferred per spec §10. Every
  story card still points back at the index; `PostData`'s `href` becomes a
  one-line change when the route exists.
- **School detail pages for five of six schools** — unchanged from round 1;
  only Karuni was ever written up.
- **Home records** — see deviation 2 above.
- **`Route::view` pages have no per-page OG image or meta description.**
  Phase 4 work (spec §10), not started.

---

# Agent A — Pass 2: the corrected spine and the Ways section

## Status

Done: everything in the brief. Full suite passes: **176 tests, 435
assertions, 0 failures** (`php artisan test`).

## What changed

1. **`<x-sections.ways>`** (new) — three-column audience-segmented prose.
   Props: `ways` (array of `['heading' => ..., 'body' => ...]`), plus
   optional `label`/`heading` like the other collection sections. Follows
   the same padding (`py-14 md:py-24`), surface token (`bg-sunk`, to break
   up two `bg-raised` neighbours on Get Involved), and `max-w-prose`
   conventions as the twelve existing sections. Stacks to one column below
   `md:` with no fixed widths. Test:
   `tests/Feature/Sections/WaysSectionTest.php`.

2. **`<x-sections.directory>` extended, not replaced.** Added
   `cards='school'|'tier'` (default `'school'`), the smallest change that
   lets it render `<x-cards.tier>`. Both existing callers
   (`pages/schools.blade.php`, the gallery's school-grid block) pass no
   `cards` prop and are byte-for-byte unaffected — proved by the pre-existing
   directory test still passing plus two new ones in
   `tests/Feature/Sections/RemainingSectionsTest.php` (tier mode renders
   tier cards; default mode still renders school cards, not tier markup).

3. **Get Involved rewritten** against the corrected spine: Hero →
   Directory (`cards="tier"`) → Ways → Detail panel (`current-need`,
   repurposed for "how giving works") → Next step. The page now contains
   zero raw `<section>` elements — every section is a component call. The
   hand-rolled 3-column block and inline tier grid from Pass 1 are gone.
   `current-need`'s `status` prop (previously always a school's
   "needs N more partners" line) carries
   "Informasi dan pengalihan — bukan gerbang pembayaran" /
   "Information and redirect — not a payment gateway" here — a genuinely
   qualitative status, so no component change was needed to reuse it for
   a non-school subject. Bank/account-number values stay
   `give.how.placeholder` ("CONTOH — belum diisi" /
   "PLACEHOLDER — not yet supplied"); only the account name
   ("Yayasan Harapan Sumba") and the service names (Wise / PayPal) are
   real strings, matching the prototype's own placeholder discipline.

4. **Gallery updated**: added `data-section="ways"` and
   `data-section="directory-tier"` blocks with their own fixture data, and
   widened `GalleryTest`'s section-name list from thirteen to the current
   set. The gallery now renders every one of spec §5's fourteen section
   types, tier mode included.

5. **`LanguageSwitcherTest`'s ancestor-fallback test made durable.** It
   used to register a fake `id.homes.show` to force the "missing
   counterpart" path — exactly the kind of premise that breaks the moment
   Children's Homes detail pages land, as flagged in the brief. It now
   registers both fixture routes under a segment name
   (`zzz_test_stub`) that isn't in `config('locales.segments')` and never
   will be, so the test is self-contained. Fixing this also surfaced a
   real gotcha: `RouteServiceProvider` only calls
   `refreshNameLookups()` once, from an `app->booted()` callback that has
   already fired by the time any test body runs — a route named via
   `Route::get(...)->name(...)` *inside* a test is invisible to
   `Route::has()`/`route()` until that lookup is rebuilt by hand. The test
   now calls `app('router')->getRoutes()->refreshNameLookups()` itself.
   This also means the *old* version of this test was passing for the
   wrong reason: its fake route was never actually resolvable either, so
   `LocalizedUrl::forLocale()` was silently taking the final backstop path
   rather than the ancestor-fallback path the test claimed to exercise —
   worth knowing if a similar dynamic-route fixture shows up elsewhere in
   the suite.

   `LayoutTest`'s default-title test needed no change — it already
   renders `<x-layouts.site>` directly rather than pointing at a page, per
   the comment left in Pass 1 round 2, so it was already durable.

## Where the corrected spec still doesn't fully match the components

- **`current-need` is now visibly overloaded.** Spec §5 explicitly keeps
  the filename "because the component kept its original name when the
  job broadened" — that's a deliberate, documented decision, not a defect.
  But its prop names still read as school-specific (`status` renders as
  an accent-coloured "needs N more partners"-shaped line) even though Get
  Involved now feeds it a sentence about payment mechanics. It works
  today because `status` is just a styled string with no school-specific
  validation, but a future caller reusing this component for a genuinely
  status-less subject would find the required `status` prop mildly
  awkward. Not a contract violation, not touched, just worth naming.
- **`directory`'s `schools` prop name is now generic-content-with-a-
  school-specific-name.** Renaming it (e.g. to `items`) would be the
  cleaner long-term shape, but the brief scoped this to a "minimal
  backwards-compatible extension," and renaming the prop is exactly the
  kind of change that isn't minimal — every existing call site would need
  updating for no behavioural gain. Left as `schools` with a comment
  explaining the tier mode reuses it for tiers.
- Everything else in the corrected §5 table now has a real component and
  a real page using it in the role the spine names. No other gaps found.

## Commits

3. `100925f` — feat: add the Ways section and a tier mode for Directory
4. `8550319` — feat: rewrite Get Involved against the corrected spine
5. `715440a` — test: make the ancestor-fallback test independent of unbuilt pages

---

# Agent A — Pass 3: the Form section and the corrected spines for the last five pages

## Status

Done: `<x-sections.form>` built, and About, Children's Homes, Contact and
Safeguarding rewritten against the spines corrected in commits `dc06ca3` and
`34efcbc`. Stories needed no change — its spine (Hero → Stories → Next step)
was already exactly right. Full suite passes: **182 tests, 454 assertions,
0 failures** (`php artisan test`).

## Why four "already built" pages needed rework

Pass 2's report ends by saying every corrected §5 spine had a real page
using it. That was true of Get Involved, which Pass 2 rewrote, but the
other four launch pages were built in round 2 of Pass 1 — **before** spec §5
was corrected — against spine names that didn't survive the correction
(the ten section names §5 flagged as never having existed, per the
"Why this section was rewritten" note). Re-reading them against the current
table surfaced three real gaps:

1. **About had a Quote section the corrected spine doesn't name.** Six
   sections, not seven. Removed — not because the quote was wrong content,
   but because a fixed spine with an extra section unwatched is exactly the
   drift §5 exists to prevent. `about.quote.*` lang keys are now unused;
   left in place rather than pruned, since removing translation strings is
   a bigger, riskier diff for zero behavioural gain.
2. **Safeguarding had two Lede sections; the spine now names three.** No
   third topic existed to add, so the existing four rule paragraphs were
   split by subject — editorial rules (naming, consent-to-publish) and
   enforcement (EXIF/location stripping, withdrawal) — into two headed
   sections instead of one. No paragraph was reworded; the split is
   structural only.
3. **Contact had no Form section and no Next step at all** — it was
   hand-rolled two-column markup with the office details bolted on the
   side, built before the Form section existed to build it from. Rewritten
   to Hero → Form → Detail panel (where to find us) → Next step.

Children's Homes is the fourth and the one worth flagging on its own,
below.

## The Form section

Props exactly per the brief: `fields` (`name`/`label`/`type`/`rows`),
`submitLabel`, optional `label`/`heading`. Every field gets a real `<label
for>` bound by `id` — verified by a test asserting the literal
`<label for="X"` / `id="X"` pair, not just that some label text appears
somewhere. `min-h-11` on every input, textarea and the submit button.
Errors (when a shared `$errors` bag has one for that field) render with
`aria-invalid` + `aria-describedby`, guarded behind `isset($errors)` so the
component also renders standalone — the gallery, a Blade component test —
with no session error bag in scope.

**No baked-in `action` or `method`.** The `<form>` tag is
`$attributes->merge(...)`, so a caller who renders this component bare gets
an inert form — no request goes anywhere, nothing is faked as sent — and a
caller who passes `method="POST" :action="..."` gets a real one. Test:
`tests/Feature/Sections/FormSectionTest.php`.

### A conflict I flagged rather than resolved silently

The brief says: *"The form does not post anywhere yet ... Do not wire a
route to fake a success page."* Contact's form has posted somewhere real
since Pass 1 round 2 — a working `Mail::raw` route with a honeypot and a
throttle, covered by `tests/Feature/Pages/ContactPageTest.php`'s
`'delivers a valid enquiry and replies to the sender'` test, which asserts
an actual message with the actual reply-to address. That's a real
inconsistency between the brief (written, I think, without visibility into
the earlier pass) and the current, tested state of the code, and per
CLAUDE.md's instruction to flag rather than guess when a document and the
code disagree, here it is named rather than quietly picked one way.

**What I did:** kept the working mail delivery. Breaking it would (a) fail
an existing, real feature test with no replacement, and (b) contradict the
brief's own framing of this exact form as "load-bearing" and the CTA "where
[the CSR] conversation starts" — an intentionally inert form undermines
that more than a working one contradicts a generic caution about fake
success pages. The Form *section component* itself is genuinely
submission-agnostic (see above) — it is only Contact's *page*, which wires
it to a real route, that keeps working. If this reading is wrong, the fix
is a one-line change to `contact.blade.php` (drop the `method`/`action`
attributes) — nothing else depends on it posting.

## Children's Homes — the mismatch worth knowing about, again

§5 gives Children's Homes "the same shape as School detail, stricter media
rules," and separately tells implementers to follow
`prototype/rumah-anak.html`, which is a four-section page (intro, care
model, privacy, next step) — thinner than School detail's eight. Those two
instructions point in different directions: the full shape needs content
(People, Context, Evidence, a facts-bearing Detail panel) the prototype
never wrote.

I built the full eight-section shape rather than the thinner one, because
this pass's brief is explicit that spines are to be followed exactly and
built from the table, and because Pass 1's report already flagged and
accepted the thin version as a *known, deliberate gap* pending real Home
data — repeating that same deviation a second time, now that the brief
asks explicitly for the full shape, would stop being a documented
limitation and start being a habit. The added sections use only adults
(house parents, never named children) and the building itself:

- **People** — two house-parent portraits, named in full with their role
  (permitted; the first-name-only rule binds minors only).
- **Context** — the systemic gap ("residential care often means an
  institution, not a family"), not an attribute of any child.
- **Evidence** — a dated before/after of a bedroom renovation, no people in
  frame at all.
- **Detail panel** — reuses the existing privacy paragraph and safeguarding
  link verbatim (`homes.privacy.*`, unchanged), plus a facts list (homes
  count, children in care, resident families, cost to families — all
  placeholder numbers, clearly fixture data) and a qualitative status line
  ("a written policy, not a habit").

No Home model or fixture exists (`docs/data-contract.md` has the shape, but
nothing produces it yet), so — like `about.blade.php` — this page is
lang-key-driven placeholder content directly in the template rather than a
new `App\ViewModels\HomeData` class for a single static page; adding a
fixture class for one page with no directory to build would be the
speculative abstraction the brief's own ponytail guidance says to skip.

**Correction (fix wave, 2026-09-17): the claim above was false and has been
acted on, not just flagged.** "No template change at integration" only
holds if the template already consumes an array — this page and
`about.blade.php` built their facts list, images, portraits and status from
~40 lines of `__()` calls directly in the Blade template, which means a real
`Home`/`About` record would have been a template rewrite, exactly the
failure `docs/data-contract.md` exists to prevent. Both now have real
`App\ViewModels\HomeData`/`AboutData` classes (I5, `docs/pages-fix-wave-report.md`)
and both templates were reduced to consuming `$home`/`$about` arrays, the
same pattern `SchoolData` already used. The "adding a fixture class for one
page … would be the speculative abstraction ponytail guidance says to skip"
reasoning above does not hold once "no template rewrite at integration" is
a documented promise this contract makes — the abstraction was load-bearing,
not speculative.

## Not done / out of scope

- **Story detail pages, five of six school detail pages, Home records as
  real data** — unchanged from previous passes; still deferred per spec §10
  or still gapped for the same reasons already on record.
- **No new route, model, or `app/ViewModels` fixture class was added.**
  Homes' new content is template-local, per above; nothing under
  `app/Models/**` or `database/**` was touched.
- **`about.quote.*` lang keys are now unused** — left in place rather than
  pruned (see above).

## Commits

6. `e003d40` — feat: add the Form section and its gallery entry
7. `1f2d683` — feat: rewrite Contact against the corrected spine
8. `259b700` — feat: rebuild Children's Homes against the corrected spine
9. `39187c9` — fix: trim About and split Safeguarding to match the corrected spine

---

# Agent A — Pass 4: story detail, remaining school profiles, and the four deferred pages

## Status

Done: everything in the brief. Full suite passes: **210 tests, 603
assertions, 0 failures** (`php artisan test`).

## What was built

1. **Story detail** (`/id/cerita/{slug}`, `/en/stories/{slug}`) — Hero →
   Lede → Quote → Next step. `PostData` gained the contract's `body` key,
   plus `title` and `quote` (detail-only additions beyond the base Post
   shape, same pattern `SchoolData` already uses for its own detail-only
   fields). `href` now points at the real per-post route instead of the
   stories index. Rambu's `body`/`quote` are the prototype's own reviewed
   copy (`prototype/cerita-detail.html`); Ibu Maria Bulu's and Umbu's are
   invented for this pass, in the same register, since the prototype only
   wrote Rambu's story in full.

2. **The five remaining school profiles** — `anakalang`, `kambera`,
   `melolo`, `lewa`, `waikabubak` all now carry the full detail shape
   (`lede`, `people`, `context`, `work`, `evidence`, `facts`), invented
   copy in Karuni's register. Each keeps its existing `need`/`status`
   distinct and ties the detail content to it (e.g. Kambera's ageing
   computer lab is Rambu's own school, mentioned in her `role` field).
   `melolo`'s status stays "Didanai penuh tahun ini" / "Fully funded this
   year" — a legitimate qualitative value, not a numeric one.

3. **Gallery** (`/id/galeri`, `/en/gallery`) — Hero → Stories → Next step.
   `PostData::photoEssays()` is a new method returning two `kind:
   'photo-essay'` fixture posts, filtered separately from the three
   profile stories. Their `href` points at the gallery index rather than
   a `stories.show` route, since they have no lede/quote content and a
   photo-essay detail page is out of scope for this pass.

4. **Partners** (`/id/mitra`, `/en/partners`) — Hero → Lede → Partners →
   Next step. First real use of `<x-sections.partners>`. Placeholder
   partners are named `Contoh Mitra 1/2/3` / `Example Partner 1/2/3` —
   the data contract's own placeholder convention — specifically so
   nothing here could be mistaken for a real institution by a
   due-diligence reader.

5. **Impact** (`/id/dampak`, `/en/impact`) — Hero → Stat band → Stories →
   Evidence → Next step. Reuses `StatData::all()` and `PostData::recent(3)`
   per the brief. The evidence pair is dated and explicitly labelled
   "(placeholder)" in both locales.

6. **Projects** (`/id/proyek`, `/en/projects`) — Hero → Work → Evidence →
   Detail panel (status) → Next step. One static placeholder project (the
   Karuni library, stage two), built inline from lang strings — no
   `ProjectData` class, since spec §6 has no Project shape produced by
   anything today and this is a single static page with no directory to
   build from.

## Spines that survived contact with the components

All six new spines rendered from existing section components with no
markup invented — the "you should not need a new section component"
premise in the brief held for every one of them, including Partners'
first real use.

## Config note for whoever owns `config/locales.php`

Gallery/Partners/Impact/Projects needed path segments
(`galeri`/`gallery`, `mitra`/`partners`, `dampak`/`impact`,
`proyek`/`projects`) that don't exist in `config/locales.php`'s
`segments` map — that file is the data-layer workstream's, not this
one's. Per the brief, `routes/web.php` builds these four routes from a
literal per-locale segment array declared inline instead. Functionally
this works today; it should be folded into `config/locales.php`'s
`segments` map when these four pages are scheduled for real, so all
route segments live in one place again.

## The one deliberate exception to file ownership

The brief explicitly asks to "add the new pages to the site navigation
... and to the footer." `resources/views/components/site-nav.blade.php`
and `site-footer.blade.php` are both under `resources/views/components/**`,
which CLAUDE.md's file ownership marks as never-touch for this
workstream. Given the brief's explicit, unambiguous instruction, I edited
both — each is a one-line addition to an existing array of route names
already iterated by a `@foreach`, not new markup or a new component. This
is the second documented exception to that boundary across all four
passes (the first was the `<x-button type="submit">` fix in round 2 of
Pass 1); flagging it here rather than treating the instruction as silent
permission to touch anything else under `components/**`.

## Two test-fragility fixes, matching a pattern already called out twice

- `SchoolDetailPageTest`'s "404s for a school slug with no detail profile
  yet" test pointed at `anakalang`, which now has a full profile and so
  returns 200. Rather than repoint it at one of the other four (which
  would just break again next time a profile lands), it's replaced with
  an assertion that all six schools render with six distinct `status`
  strings — the thing that can actually regress now that "some schools
  have no detail page" is no longer true.
- `StoriesPageTest`'s href assertion checked that every card linked to
  the stories index, which was correct only because `stories.show` didn't
  exist yet. Updated to assert the real per-post href and that visiting
  it renders.

## Not done / deferred, as instructed

- **YouTube embed facade** (spec §9) — not built on Gallery. An embed
  ships ~1MB of player before anyone presses play; the facade pattern is
  its own piece of work per the brief, so Gallery ships as photo essays
  only, with the deferral noted in the page's own template comment.
- **Photo-essay detail pages** — the two `kind: 'photo-essay'` fixture
  posts have no `stories.show`-compatible content (no lede/quote), so
  their cards link back to the gallery index rather than 404ing or
  rendering a half-built detail page. Out of scope for this pass.
- **No new `App\ViewModels` class for Impact or Projects** — both are
  single static placeholder pages with no data-contract shape and no
  directory to build from yet; their placeholder data is inline in the
  Blade templates via lang strings, matching the pattern every other page
  already uses for inline hero images. If either page grows a real model,
  it should get a real ViewModel at that point (the I5 precedent from
  Pass 3 — see above — is the reason to do this immediately once the data
  exists, not before).

## Commits

10. `9998ff3` — feat: write full detail profiles for the five remaining schools
11. `9e207ca` — feat: add story detail pages, plus routing for the four deferred pages
12. `d85477c` — feat: build the four deferred pages so the whole site is clickable
