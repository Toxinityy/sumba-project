# Daily plan — 2026-09-19 — Agent A (pages) and Agent B (data layer)

**Where things stand:** Agent A has finished the static site and the editorial
landing redesign (270 tests green, tree clean at `e7efcb8`). Agent B has nine
models, three migrations and nine factories, but no model yet produces the
contract arrays, there are no seeders, no Filament panel and no EXIF
stripping. Every page still reads fixtures. **B's lane is the critical path.**

**Goal for the day:** School becomes the first real entity end to end — the
database produces exactly what the pages already consume, and the school pages
render from it with zero Blade changes. That is the data contract's first real
test.

---

## How to work today (no subagent-driven execution)

Each agent works through its own list **in order, directly, one task at a
time**:

1. Write the test first and watch it fail.
2. Implement until it passes.
3. **Break the feature on purpose and confirm the test goes red, then restore.**
   This replaces the review loop — it is what caught the counterfeit tests on
   this project, several of which had passed review.
4. Run the full suite (`php artisan test`).
5. One commit per task, staging **explicit paths only** — never
   `git add -A` / `git add .` / `git commit -a`.

Two human checkpoints replace per-task review: **midday** and **end of day**.

**Separate branches.** Last time both agents shared one working directory and
nearly swept each other's work into commits. Agent A stays on
`design/spec-and-prototype`; Agent B works on `feature/data-layer` in its own
git worktree. B merges into A's branch at the end-of-day checkpoint.

---

## File ownership (unchanged — do not cross)

| Agent A owns | Agent B owns |
|---|---|
| `resources/**` (pages, components, CSS, JS) | `app/Models/**` |
| `app/ViewModels/**` | `database/**` (migrations, factories, seeders) |
| `routes/web.php`, `config/locales.php` | `composer.json`, `composer.lock` |
| `lang/**` | `tests/Feature/Models/**` |
| `tests/Feature/**` except Models | |

`docs/data-contract.md` changes only with **both** agents agreeing — that is
task B1, done first.

---

## Agent B — data layer

### B1. Agree and write the three contract amendments *(first — unblocks both)*
- **School gains `age_range`** (localised; derived from `level` so the two can
  never disagree: TK 4–6, SMP 12–15, SMA 15–18). The level ladder already
  renders it from the fixture.
- **Stat gains an optional `body`** — the challenge ledger uses `label` as the
  bold opening clause and `body` as the sentence that finishes it.
- **Spec §3** still names `spatie/laravel-translatable`; the models use the
  hand-rolled `HasTranslations` trait instead. Update §3 so the spec matches
  the code.
- *Done when:* `docs/data-contract.md` and the spec reflect all three, and
  Agent A has seen them.

### B2. School shape methods
- `School::toDirectoryArray()` and `School::toDetailArray()` returning **exactly**
  the contract's keys, strings resolved for `app()->getLocale()`.
- Images stay `PlaceholderImage` for now — agreed earlier; real images land
  with the upload path.
- `people` from `posts()` filtered to `kind = profile`; `evidence` from
  `projects()`; `href` is **not** the model's job (the page builds it).
- *Test:* the returned keys equal the contract's keys, in both locales.

### B3. `SchoolSeeder` — the six schools
- Content copied from `app/ViewModels/SchoolData.php` so the rendered pages are
  reviewable and identical to today's. Both locales.
- *Done when:* `php artisan migrate:fresh --seed` succeeds on SQLite and yields
  six published schools.

### B4. Route-model binding by translated slug
- `slug` is translatable, so `/en/schools/{slug}` must resolve against the
  **English** slug and `/id/sekolah/{slug}` against the Indonesian one —
  otherwise one locale 404s or silently resolves by the other's slug.
- Override `resolveRouteBinding` on `School`.
- *Test:* both locales resolve; the wrong locale's slug 404s.

### B5. EXIF and GPS stripping on upload *(stretch — else first thing tomorrow)*
- The highest-severity open safeguarding gap in the project, and still
  unowned: a geotagged photo of a child outside their home is a published
  location. Strip EXIF unconditionally, GPS included, when a `MediaAsset` is
  stored.
- *Test:* a fixture JPEG carrying GPS tags comes out with none.

---

## Agent A — pages

### A1. Correct `CLAUDE.md` and `AGENTS.md` *(tiny, do first)*
- Both claim the stack includes **Filament and Alpine.js — neither is
  installed.** A fresh session reading them assumes they exist. Replace the
  stack list with the non-derivable parts only: cPanel shared hosting with no
  Node in production, and "Tailwind 4 is CSS-first — don't create a
  `tailwind.config.js`". Drop the `Tests` section (standard `php artisan test`).
- Keep the two files identical.

### A2. One source of truth for route segments
- The four folded pages (`galeri`, `mitra`, `dampak`, `proyek`) define their
  segments inline in `routes/web.php`; every other page uses
  `config/locales.php`. Move them into the config map.
- *Test:* the existing folded-route tests still pass in both locales.

### A3. Enforce the accent contrast rule in the test suite
- Spec §4 (amended 2026-09-17) now requires the contrast test to check accent
  against **every** surface token, not a hand-picked list. Light accent fails
  AA on `surface-sunk` (4.26:1) and `badge-bg` (3.92:1).
- Add: accent measured against each surface parsed from `tokens.css`, and an
  assertion that no section rendering on `bg-sunk` or `bg-badge` uses
  `text-accent`.
- *Mutation check:* put `text-accent` inside a sunk section — the test must go
  red.

### A4. Phone pass at 400px *(with you)*
- The landing page was only verified programmatically at 400px — Chrome would
  not resize below its minimum. Mobile is most of the traffic.
- Open the landing page and the four deep pages on a real phone; A fixes
  whatever is found.

### A5. Flip `SchoolData` to Eloquent *(end of day — only if B2–B4 are green)*
- After B merges: `SchoolData::all()` / `find()` call the model's shape methods
  instead of returning fixtures.
- **The rule that makes this the day's real test: no Blade template may
  change.** If one has to, the contract was wrong — stop and fix
  `docs/data-contract.md` first.
- *Done when:* every school page test passes against seeded data.
- Integration was set aside earlier so the static site could be finished
  first; it is now finished, so this is the first integration step. If you'd
  rather keep integration parked, skip A5 and stop B after B5.

### Stretch (A)
- Re-fetch Fraunces with the `SOFT` and `WONK` axes, so the pull quote gets the
  softer terminals the design specified.

---

## Checkpoints

**Midday:** B1 agreed and written; B2 in progress; A1–A3 done.

**End of day:** B merges `feature/data-layer`; A does A5; full suite green;
school pages render from the database. If A5 needed a Blade change, that is a
contract bug to fix first thing tomorrow.

## Not today

The Filament panel for Vera, the rest of the entities' integration, the upload
UI, and the child-protection specialist's question about publishing the homes'
locations — that last one needs a person, not an agent.
