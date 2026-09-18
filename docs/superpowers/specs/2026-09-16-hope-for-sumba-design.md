# Hope for Sumba — Website Design Spec

**Date:** 2026-09-16
**Status:** Approved, pending expert review of the safeguarding policy
**Launch target:** October–November 2026

---

## 1. Purpose

A bilingual (Indonesian/English) website for an education ministry in Sumba, Indonesia. The ministry runs free schools (TK/SMP/SMA) and children's homes. The site is a storytelling, fundraising and partnership platform.

**Primary audience:** institutional donors doing due diligence — CSR departments, foundations, churches. They arrive skeptical and read carefully. Secondary: individual donors and volunteers. Most traffic is mobile; proposal review happens on desktop.

**Governing principle:**

> Dignity, potential, education, hope, opportunity, the future — **never** helplessness or poverty as identity.

Tone: authentic, warm, human, hopeful, documentary. A well-made annual report crossed with a photo essay. Calm competence, not urgency.

This rules out the standard nonprofit vocabulary — desaturated photography, a sad-eyed portrait above a donate button, countdown urgency, alert banners. Pity-driven design also signals *amateur* to institutional donors. Photography carries the page; the interface recedes.

---

## 2. Decisions

| # | Decision | Rationale |
|---|---|---|
| 1 | **Filament**, not Statamic | Statamic's free tier is single-user with no multi-site and no revisions — it cannot do a bilingual, three-editor site. Pro is $349 + $99/yr. Filament is free and mainstream in Laravel, which is also the better handover story. |
| 2 | Structured entities + fixed narrative templates, **plus one flexible page type** | The §5 narrative spine only holds if it's structural. One escape hatch with a curated block set covers rare one-offs without letting layout drift across the whole site. |
| 3 | Translation lag expected; **fallback, not 404** | Missing locale renders the source language with a quiet inline note. Filament shows per-locale completeness. |
| 4 | **No numeric funding anywhere** | No goals, no amounts raised, no progress bars. A bar frozen at 40% for six months actively damages credibility. Status is a short qualitative sentence an editor writes; `current_need` is one sentence. |
| 5 | Design for small content volume, schema grows | Content inventory not settled. No filtering or pagination at launch; the model supports adding them without a rewrite. |
| 6 | **Visual system: Open Field (light) + Dusk Savanna (dark)** | One system at two luminances, sharing a warm neutral family and a gold accent hue — not two directions. |
| 7 | Safeguarding standard proposed and **enforced in schema** | No policy existed. The site's rules are structural: minors have no surname field, consent gates publishing, EXIF is stripped unconditionally. |
| 8 | Compressed publication masters uploaded; originals on a separate drive | Keeps shared-hosting disk and memory pressure down; bad crops stay recoverable from the archive. |
| 9 | IDR always; **approximate USD on `/en`** from a manual rate | CSR departments think in rupiah; an overseas individual donor reading a bare IDR figure has no sense of scale. A manual rate avoids an API, a scheduled job and a silent staleness bug. |
| 10 | **Deliberate launch scope**, four pages deferred | Six-to-ten week runway with two developers new to Filament. See §10. |

---

## 3. Stack

Laravel + Filament + Blade + Tailwind + Alpine. Server-rendered.

**Hosting:** cPanel shared hosting, typical Indonesian profile — PHP 8.x, GD available, Imagick uncertain, no shell access, no long-running queue workers, cron available.

Key packages: `spatie/laravel-translatable`, `spatie/laravel-medialibrary`, an activity-log package for revisions.

**Add Cloudflare's free tier in front of the domain.** It is the single largest performance win available, costs nothing, and partly compensates for shared hosting having no CDN — which matters a great deal for mobile users on Indonesian networks.

---

## 4. Visual system

One system, two luminances. Light is **Open Field** (warm paper, forest ink, ochre from dry grass). Dark is **Dusk Savanna** (Sumba at last light, ember gold). The accent stays recognisably the same colour in both.

**Geometry and photography are theme-independent.** Corners that change on a theme toggle read as two different sites; a real photograph doesn't change either.

### Tokens — light

| Token | Value | Contrast on surface |
|---|---|---|
| `surface` | `#F7F8F3` | — |
| `surface-raised` | `#FFFFFF` | — |
| `surface-sunk` | `#ECEEE3` | — |
| `ink` | `#1E2A22` | 13.96:1 |
| `ink-muted` | `#4B5A4E` | 6.85:1 |
| `accent` | `#96660E` | 4.68:1 |
| `accent-ink` | `#FFFFFF` | 5.00:1 on accent |
| `border` | `#DEDFCE` | decorative |
| `badge-bg` / `badge-ink` | `#EFE3C8` / `#5A3E0E` | 7.74:1 |
| `inverse-surface` / `inverse-ink` | `#1E2A22` / `#F7F8F3` | 13.96:1 |

### Tokens — dark

| Token | Value | Contrast on surface |
|---|---|---|
| `surface` | `#211A15` | — |
| `surface-raised` | `#2B231C` | — |
| `surface-sunk` | `#1A1410` | — |
| `ink` | `#F5EFE6` | 15.02:1 |
| `ink-muted` | `#C9BEB0` | 9.38:1 |
| `accent` | `#E8A23A` | 7.90:1 |
| `accent-ink` | `#241A0E` | 7.86:1 on accent |
| `border` | `#3B322A` | decorative |
| `badge-bg` / `badge-ink` | `#3B2E1C` / `#F0C97D` | 8.38:1 |
| `inverse-surface` / `inverse-ink` | `#3A2E22` / `#F5EFE6` | 11.53:1 |

The dark `inverse-surface` is deliberately distinct from `surface-raised`. If they resolve to the same value the section alternation in §5 silently dies and the stat band stops separating from the section above it.

**Geometry (both themes):** radius 6 / 10 / 14px, buttons 8px.

**Accessibility:** WCAG AA on all text — 4.5:1 body, 3:1 large. The light-mode gold at 4.68:1 is the tightest and must not be darkened further without re-checking.

**Correction (2026-09-18) — accent is not safe on every ground.** This section previously said "all 17 pairs verified". That verification covered the pairs someone thought to list, not every pairing the palette permits, and two real combinations fail:

| Light `accent #96660E` on | Ratio | |
|---|---|---|
| `surface #F7F8F3` | 4.68:1 | pass |
| `surface-raised #FFFFFF` | 5.00:1 | pass |
| **`surface-sunk #ECEEE3`** | **4.26:1** | **fails AA** |
| **`badge-bg #EFE3C8`** | **3.92:1** | **fails AA** |

Dark-mode accent clears AA on all four grounds.

**The rule: in light mode, accent may be used as TEXT only on `surface` and `surface-raised`.** On `surface-sunk` or `badge-bg`, accent-coloured text uses `badge-ink` (`#5A3E0E` light, `#F0C97D` dark) — same hue family, 8.41:1 and 7.74:1 respectively. Accent remains fine as a *background* with `accent-ink` on it, and as a non-text element such as a rule or a border.

Nothing violates this today — no `bg-sunk` component uses `text-accent`, and the badge uses `badge-ink`. It is written down because it is a trap: the palette invites using accent structurally, and a green token test proves nothing about pairings nobody listed. **The contrast test must assert accent's ratio on every surface token, not on a hand-picked list.**

**Theme resolution — three states, not two.** An explicit choice stamps `data-theme` on the root and wins in both directions. The default "system" setting stamps nothing, so the bare `:root` must carry a complete light palette and the dark media query is guarded as `:root:not([data-theme="light"])`.

### Typography

| Role | Font | Desktop / mobile |
|---|---|---|
| Display | Fraunces | 56 / 36 |
| H1 | Fraunces | 44 / 32 |
| H2 | Fraunces | 32 / 26 |
| H3 | Plus Jakarta Sans semibold | 24 / 20 |
| Body | Plus Jakarta Sans | 18 / 17, line-height 1.7 |
| Caption/label | Plus Jakarta Sans | 14, uppercase, +0.08em |

Pull quotes: serif, italic, 28–32px, generous leading. Both faces self-hosted and subset; variable axes trimmed to the weights used; `font-display: swap`; two critical faces preloaded.

**Fraunces' range is the editorial lever (added 2026-09-18).** The table above sets sizes; it says nothing about weight, and the first build pinned Fraunces at weight 500 everywhere. It is a five-axis variable face — `opsz 9–144`, `wght 100–900`, `SOFT`, `WONK`, and a true italic — and the unused range is where editorial character comes from without introducing a single new colour.

The governing idea is **distance between extremes in one typeface**: low weight at large size (280–330 at 56–200px) gives the high stroke contrast of a magazine masthead rather than a bold web headline, and it costs nothing. Set against a small mark at `opsz 14 / wght 700`, the same face reads as two voices of one publication, because Fraunces' optical-size axis genuinely redraws the letterforms.

**Landing-page exception to the type table:** on the single landing page that carries the whole narrative, section headings are set at the H1 size (44px desktop) rather than H2's 32px, because there they *are* the top-level moments and no page H1 competes with them. The masthead stays at Display. This exception applies to the landing page only.

### Layout

8px spacing base. Max content width 1200px. Prose column 68ch. Section padding 96px desktop / 56px mobile. Minimum 16px side gutter. Mobile-first, no horizontal page scroll at any width.

**Indonesian runs 15–20% longer than English.** No button, nav item or card is ever sized to fit English exactly.

---

## 5. Section system

Fifteen sections. Every page is a sequence of them; **no page invents new ones, and no spine may name anything absent from this table.**

| Section | Job | Dominant |
|---|---|---|
| Hero | Establish place and tone | Image, art-directed per breakpoint |
| Lede | Mission or profile in one paragraph | Prose, 68ch |
| People | Who this is about | Environmental portraits, 4:5 |
| Context | The challenge | Prose + image |
| Work | What the ministry is doing, or one subject given full-width treatment | Photo/prose alternation |
| Stat band | Scale at a glance | Display numerals |
| Evidence | Before/after pairs, dated | Image pair |
| Quote | A human voice | Serif italic |
| Stories | Teasers into longer reads | 2–3 cards |
| Directory | Card grid — schools, homes, projects, or sponsorship tiers | Card grid |
| Detail panel | Prose beside a scannable facts list — a school's current need, how giving works, a project's status | Prose + facts |
| Ways | Audience-segmented prose in three columns — corporate, church, volunteer | Three-up prose |
| Form | Collect a partnership or volunteer enquiry | Labelled fields + submit |
| Next step | What the reader does now | Prose + actions |
| Partners | Social proof | Logo strip |

*Detail panel* is implemented as `<x-sections.current-need>` — the component kept its original name when the job broadened, to avoid a rename across built and reviewed code.

**Section geometry is per-section, not global (added 2026-09-18).** The first build gave all fifteen sections identical geometry — the same vertical padding, the same max-width, the same centred container, then a symmetric two-column or three-up grid. The result read as assembled rather than designed, and the client's verdict was "too flat and tiresome".

The cause was the uniform geometry, **not the fixed section set.** Those are separable, and the distinction matters: the section set is what produced zero hand-rolled sections across fourteen pages, and retiring it would lose that protection and buy nothing. So the set stays fixed and the geometry varies:

- **Four padding steps**, not one. Coupled sections sit tight; pivots get air.
- **Four container relationships**: inside the 1200px content width, inside a wider 1440px band, full-bleed to the viewport, and offset into the grid.
- **Overlap is permitted and load-bearing** — a plate bleeding past a gutter, a card pulled up across a section boundary, a pull quote breaking its measure.
- **One dark chapter rather than alternating inversion.** A continuous inverse field the reader passes through reads as a designed movement; flipping light and dark section by section reads as a broken theme.

**Three rules produce the rhythm:**

1. Sections alternate image-dominant and prose-dominant. **Never two prose-dominant sections adjacent** — that is the photo-essay cadence. Lede, Context and Quote are prose-dominant. Detail panel, Ways and Next step are text-based but are not prose walls: one is a facts table, one is three short columns, one is actions. Those three may sit beside a prose-dominant section without breaking the rhythm.
2. Photography goes full-bleed or near it; running text stays in the 68ch column. The contrast makes the images read as the page's substance.
3. Sections alternate across a small set of surface tones rather than uniform white — sectioning without dividers, cards or boxes.

### Page spines

Every name below is a section from the table above. Where a section serves a specific content role on that page, the role is given in parentheses — it describes the content, not a new section type.

| Page | Sequence |
|---|---|
| Home | Hero → Lede → Stat band → Work (featured school) → Stories → Next step |
| School detail | Hero → Lede → People → Context → Work → Evidence → Detail panel (current need) → Next step |
| Children's home | Same shape, stricter media rules |
| Project | Hero → Work → Evidence → Detail panel (status) → Next step |
| Impact | Hero → Stat band → Stories → Evidence → Next step |
| About | Hero → Lede (our story) → Work (founder) → Lede (mission & vision) → People (the team) → Next step |
| Get Involved | Hero → Directory (sponsorship tiers) → Ways (corporate, church, volunteer) → Detail panel (how giving works) → Next step |
| Schools directory | Hero → Directory (schools) → Next step |
| Stories | Hero → Stories → Next step |
| Story detail | Hero → Lede → Quote → Next step |
| Gallery | Hero → Stories (photo essays) → Next step |
| Partners | Hero → Lede → Partners → Next step |
| Contact | Hero → Form (partnership enquiry) → Detail panel (where to find us) → Next step |
| Safeguarding | Hero → Lede → Lede → Lede (policy page — exempt from rule 1, see below) |

The Project and Impact spines are recorded here for completeness; both pages are deferred past launch (§10).

**Policy pages are exempt from rule 1.** Safeguarding — and any later terms or privacy page — is a document, not a photo essay. It is several prose blocks in sequence, which rule 1 would otherwise forbid. The rule exists to prevent walls of prose on narrative pages; applying it here would mean inventing visual rhythm on a child-protection policy, which is worse than the wall it prevents. The exemption is named here so it is a decision rather than an undocumented deviation, and it extends to no other page.

**Why this section was rewritten (2026-09-17).** The original spines named *Featured school*, *Tiers*, *Corporate*, *Church*, *Volunteer*, *How giving works*, *Status*, *Our story*, *Founder* and *Mission & vision* — ten names, none of which existed in the section table. The spec told implementers to build from a fixed set and then handed them a page plan referencing things outside it. This surfaced when the Get Involved page was built: with no section to use, it was assembled from hand-rolled markup, which is precisely the drift the fixed section set exists to prevent. *Ways* is the only genuinely new section; the rest were content roles misnamed as structure.

### Two hard rules

**The Context section is the dignity trap.** Challenges are described as circumstance and system — distance to the nearest school, teacher shortages, no grid electricity — **never as attributes of the children**. Illustrated with people acting, never people suffering. This rule belongs as a comment on the template, not only in a brief.

**"Donate now" is the wrong primary CTA.** A CSR department cannot click Donate; it needs a proposal, a budget line and a named contact. Next step carries two actions: **Partner with us** (→ inquiry form) and **Support a school** (→ giving page). Which leads depends on the page.

Giving is information-plus-redirect (PayPal, Wise, bank transfer, QRIS) with no payment gateway. Bank details on a page read as sketchy unless handled carefully, so the giving page states the legal entity name, address, registration, and what happens after money arrives.

---

## 6. Content model

| Model | Core fields | Notes |
|---|---|---|
| **School** | level (TK/SMP/SMA), location, hero, gallery, pupil/teacher counts, `current_need`, `status` | Status is a translated qualitative sentence, never a number — see the correction below |
| **Home** | Same shape plus care model | Stricter media rules |
| **Project** | status (planned/underway/complete), before + after image with dated captions, optional school/home relation | Powers Projects and Impact |
| **Post** | `kind` (profile/update/news), title, slug, hook, body, featured image, published_at, optional subject fields, optional relations | The blog and the story feed |
| **SponsorshipTier** | title, cost (IDR), what-it-unlocks, photo, category | Six tiers |
| **Partner** | logo, type (corporate/church/foundation), testimonial | |
| **Stat** | label, value, `as_of` | `as_of` makes staleness visible to the team |
| **MediaAsset** | file, caption, consent record, `depicts_minor`, named crops, focal point | Wraps every upload |
| **Consent** | subject, guardian, date, scope, review date, signed form scan | See §9 |

**Why `status` was corrected (2026-09-17).** This table called School and Home `status` "a short enum". `docs/data-contract.md` called it a qualitative string, and the school card — built, tested and committed — renders sentences no enum can produce ("Butuh 4 mitra lagi", "Didanai penuh tahun ini"). Both documents agreed on the load-bearing rule, that it is never a number, so the table's wording was the error and the component is the fixed point. `status` is a translated JSON string.

Consequence: `status` being taken means publication state is not an enum either. A null `published_at` is the draft state on School, Home, Project and Post, which Post needed regardless.

**Post kinds:** `profile` (student/teacher/community/founder stories, portrait 4:5 card) and `update` / `news` (3:2, date-led). Relations to School, Home or Project let a post surface on that entity's page automatically.

**Editor:** Filament's rich editor with a curated block set — paragraph, H2/H3, pull quote, image, YouTube embed, before/after pair.

Blocks *inside an article body* are editorial. Blocks that arrange *page sections* are layout. Only the first is permitted; the second erodes the spine.

**Gallery needs no model.** Photo essays are posts whose body is mostly image blocks; the page pulls from those plus the media library.

### Editor experience to build

Filament is an admin panel builder, not a CMS, so these are build items rather than features. Vera publishes without a reviewer, which makes them necessary rather than nice:

- **Draft → preview:** a null `published_at` is the draft state (see the correction above), plus a signed preview route rendering the real Blade template with unpublished content. ~1 day.
- **Revision history:** activity-log package or a versions table storing the translatable JSON payload, with a rollback action. ~1–2 days.
- **Media library:** `spatie/laravel-medialibrary` with the Filament plugin — browsable, reusable uploads. Built regardless, since the conversion pipeline hangs off it.

---

## 7. Bilingual implementation

**Routing:** locale-prefixed with translated path segments — `/id/sekolah/karuni`, `/en/schools/karuni`. Both independently indexable. `/` 302s to `/id` unless `Accept-Language` clearly prefers English. Reciprocal `hreflang` alternates plus `x-default` on every page; both locales in the sitemap.

**Slugs are per-locale** wherever the title is translated.

**The language switcher must link to the equivalent page** in the other locale, never to the homepage. Where a translation doesn't exist the link still goes to the equivalent URL and the fallback renders there with its note. No dead ends, no 404s.

**Fallback:** missing locale renders the source language with a quiet inline note — and that note is itself translated.

**Currency:** costs stored in IDR. `/id` renders IDR. `/en` renders IDR plus an approximate USD equivalent from a single manually-set rate in settings, explicitly labelled approximate.

**In Filament:** locale switcher in the panel, per-locale completeness badges on list views, translation status visible before publish.

---

## 8. Image pipeline and performance

Performance is a design constraint: shared hosting, Indonesian mobile connections.

**Capability detection first.** The pipeline checks at boot what the server can encode and caches the answer, then degrades in a defined order: AVIF + WebP + JPEG → WebP + JPEG → JPEG. It never silently ships nothing. **Build this in week one** — if AVIF is unavailable we need to know then, not in October.

**Conversion at upload, never at request,** via `spatie/laravel-medialibrary`, queued.

**The queue runs on cron:** `queue:work --stop-when-empty` on a one-minute cPanel cron with the database driver. No shell access means no daemon; this is adequate for image conversion.

**Ingest spec — what Dhani hands over.** Originals are archived on a separate drive; the site receives compressed publication masters:

- 3000px on the long edge — large enough to crop and downscale from
- Quality 85–90, sRGB, roughly 1.5–3MB
- **Both the wide and tall versions of key scenes.** A 4:5 portrait cannot be cropped into a 21:9 hero. This is load-bearing, not optional.

Originals are downscaled to a 3000px working maximum before variants are generated, so PHP memory limits on shared hosting aren't hit.

**Art direction, not just resizing.** `MediaAsset` carries named crops — wide 21:9, tall 4:5, card 4:5, story 3:2 — plus a focal point set in the panel so automatic cropping never takes someone's head off.

**One `<picture>` component used everywhere,** including the rich-text rewrite pass. Emits AVIF/WebP/JPEG with `srcset` and `sizes`, explicit width and height, `loading="lazy"` except the LCP hero which is eager with `fetchpriority="high"`.

**Two places image discipline usually breaks, both closed:**

- Images pasted into rich text bypass the pipeline. Body HTML gets a render-time pass rewriting every `<img>` into the responsive component. Authors cannot opt out by accident.
- YouTube embeds ship ~1MB of player before anyone presses play. Embeds render as a lightweight thumbnail facade that swaps in the iframe on click.

**The 200KB hero budget is enforced, not hoped for.** Encode, measure, step quality down until under budget or at a quality floor — and log when the floor is reached so someone can pick a less detailed crop rather than ship a soft hero.

**Verification runs against staging, not localhost.** Lighthouse on the three heaviest page types as part of each phase's definition of done. A local machine on fibre says nothing about a phone in Waingapu.

---

## 9. Safeguarding

> **This is a technical and editorial standard, not a legal policy.** An organisation working with children must have its actual safeguarding policy reviewed by someone with child protection expertise, reflecting Indonesian law and partner requirements. This spec covers how the website enforces whatever policy is adopted, and a sensible default meanwhile.

### Editorial rules

- **First names only for minors.** No surnames — not in stories, captions, or image alt text.
- **No identifying bundles.** Name, village, school and routine are each harmless alone and a tracing kit together. A named child is never tied to a specific location plus a routine.
- **Children are shown clothed, active and dignified.** No distress, no medical situations, nothing that reads as victimhood.
- **Residential care is stricter than school.** The site never states why a child is in care, and never attaches that fact to a name.
- **Consent is per-use and revocable.** Guardian consent plus the child's assent, scoped to web use specifically. Consent for a printed newsletter is not consent for a public website.

### Enforcement

- `Consent` holds subject, guardian, date, scope, review date, and a scan of the signed form.
- `MediaAsset.depicts_minor` **gates publishing.** An asset flagged as depicting a minor without valid web-scoped consent cannot go live — Filament blocks it rather than warning.
- **Minor subject records have no surname field at all.** The schema makes the rule unbreakable rather than merely documented. If it can't be entered, it can't leak.
- **EXIF is stripped on upload, unconditionally, GPS included.** A geotagged photograph of a child outside their home is a precise published location. Highest-severity failure mode here.
- **Withdrawal actually works.** A "withdraw consent" action unpublishes every record using that asset immediately — not a task in someone's inbox.
- Consent records carry review dates; the dashboard lists what's expiring. A story about a nine-year-old is still indexed when they're nineteen.

### Publicly

A **Safeguarding page** in both locales: the policy in plain language, how consent is obtained, how to request removal, with a named contact. Worth doing for its own sake — and it is exactly what a CSR reviewer looks for and usually doesn't find on a small ministry site.

---

## 10. Build phases and scope

**The runway is six to ten weeks** (16 September 2026 → October–November launch), with two developers new to Filament, content still being gathered and photography still being shot. The eleven-page sitemap is not a six-week build at a quality level that survives due-diligence reading.

| Phase | Weeks | Contents | Definition of done |
|---|---|---|---|
| **0 — Foundations** | 1 | Laravel + Filament, deploy path, staging URL, locale routing, self-hosted subset fonts, Tailwind tokens, image pipeline with capability detection, Cloudflare | A two-locale page on staging serving a responsive AVIF/WebP hero under 200KB, Lighthouse run against staging |
| **1 — Design system** | 2 | Fifteen sections as Blade components, card and quote components, both themes | Component gallery renders every section in both locales at 400px and 1440px |
| **2 — Models and panel** | 3–4 | Nine models, translatable fields, media library, consent gate, EXIF stripping, draft preview, revisions | **Vera can create a school and a post end to end and preview them** — and she gets access that day |
| **3 — Pages** | 4–6 | Launch pages, both locales, forms with spam protection | All launch pages live on staging in both locales |
| **4 — Launch readiness** | 6–8 | Content and photography in, hreflang, sitemap, OG images, accessibility audit, throttled-mobile performance, safeguarding page live, analytics, backup **with a tested restore** | Launch checklist signed off |

### Launch scope

**Ship:** Home, About, Schools (directory + detail), Children's Homes, Stories, Get Involved, Contact, Safeguarding. That is the complete donor journey — arrive, understand, believe, act.

**Defer:** Gallery, Partners, Impact, Projects. These four share a property: each depends on content that does not exist yet. Gallery needs finished photo essays; Partners needs logos and written permission; Impact needs verified statistics; Projects needs documented before/after pairs. **Shipping any of them thin is worse than not shipping them** — a half-empty Partners page reads as *nobody partners with them*, and a vague Impact page is what a reviewer uses to decide you're not ready.

### The critical path is content, not code

The site can be structurally finished and still not launchable. Two dates matter more than any engineering milestone:

1. **Vera has the panel by week 4.**
2. **Dhani's shoot, including the tall crops, lands by week 5.**

---

## 11. Testing

Not chasing coverage on Blade templates. Feature tests on what fails silently and expensively:

- Locale routing, translated segments, and fallback rendering
- **The consent publish gate** — a protection boundary, so it gets tests, and they run in CI. "We'll be careful" is not a control.
- **EXIF stripping**, GPS in particular
- Hero size budget enforcement
- The language switcher resolving to equivalent pages, including where a translation is missing

---

## 12. Open items

| Item | Owner | Notes |
|---|---|---|
| Safeguarding policy reviewed by a child-protection specialist | Ministry | Spec is provisional until this happens |
| Real legal entity name, registration number, bank details | Reynold | Prototype uses explicit placeholders |
| Confirm Imagick/AVIF availability on the host | Dev | Week 1, gates the image strategy |
| Dhani briefed on the export spec and tall crops | Dev/Reynold | Before the shoot finishes |
| Deferred pages scheduled post-launch | Team | Gallery, Partners, Impact, Projects |
| `spatie/laravel-medialibrary` adopted, or not | Dev | `MediaAsset` is currently a plain table whose columns map onto it; the decision belongs with whoever builds the upload pipeline |
| EXIF stripping on upload, GPS included (§9) | Dev | No owner. Highest-severity safeguarding gap still open — the schema cannot enforce it, only the upload path can |
| Revision history and the signed preview route | Dev | No owner. Listed under "Editor experience to build" and not yet built |
| Consent withdrawal reaching rich-text body images | Dev | `Consent::withdraw()` unpublishes attached assets now; images embedded in body HTML are only reachable once the §8 rewrite pass exists |

---

## Appendix — prototype

A ten-page clickable prototype exists at `prototype/`, generated by `prototype/build.js`. It demonstrates the section system, both themes, both languages, the school card, a before/after pair, the dual CTA and the safeguarding rules applied to real content.

All of its content is invented placeholder material and every page says so. Bank details and registration numbers read `CONTOH — belum diisi` rather than plausible-looking fakes, deliberately.

Its CSS is hand-written rather than Tailwind: for a throwaway prototype a build step buys nothing, and every value maps onto a Tailwind token when it moves across.
