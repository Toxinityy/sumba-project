# Daily plan — 2026-09-20 — Agent A (pages) and Agent B (data layer)

**Where things stand:** School is real end to end. The six schools are seeded,
`SchoolData` reads the model, the pages render from the database with no Blade
change, and the language switch carries a reader to the other locale's slug of
the same school. EXIF and GPS are stripped on every stored `MediaAsset`. The
landing page changed twice yesterday: new faces (Newsreader + Public Sans), and
Contact folded into it as `/id#kontak` with Home in the nav. 297 tests green at
`da6d5fd`.

**Goal for the day:** **Post** becomes the second real entity end to end. It is
the right one next because the landing page leans on it harder than on anything
else — the stories rail, the voice quote that closes the dark chapter, the
portraits in every school's People section (already model-backed through
`School::toDetailArray()`), the stories index, story detail, and the gallery.
When it lands, the only fixtures left are Home, Partner, Tier and About.

---

## How to work today (no subagent-driven execution)

Unchanged from yesterday, because it worked:

1. Write the test first and watch it fail.
2. Implement until it passes.
3. **Break the feature on purpose and confirm the test goes red, then restore.**
   This caught a counterfeit test again yesterday: `SchoolSeederTest`'s
   "reproduces the fixture exactly" compared the model with itself the moment
   `SchoolData` started reading the model, and could no longer fail.
4. Run the full suite (`php artisan test`).
5. One commit per task, staging **explicit paths only** — never
   `git add -A` / `git add .` / `git commit -a`.

**Branches.** Agent A stays on `design/spec-and-prototype`. Agent B's
`feature/data-layer` is merged and its worktree still sits at the old tip:
**start today by branching `feature/data-layer-posts` from `da6d5fd`** in that
worktree, or the first commit lands on yesterday's base and re-merges yesterday.

**Checkpoints:** midday and end of day, same as yesterday.

---

## File ownership (unchanged — do not cross)

| Agent A owns | Agent B owns |
|---|---|
| `resources/**` (pages, components, CSS, JS) | `app/Models/**` |
| `app/ViewModels/**`, `app/Support/**` | `database/**` (migrations, factories, seeders) |
| `routes/web.php`, `config/locales.php` | `composer.json`, `composer.lock` |
| `lang/**` | `tests/Feature/Models/**` |
| `tests/Feature/**` except Models | |

`docs/data-contract.md` changes only with **both** agents agreeing — task B1.

---

## Agent B — data layer

### B1. Four contract amendments for Post *(first — unblocks both)*

The fixture and the contract have drifted, and the model matches neither. All
four are real gaps, not cosmetics:

- **`title`** — every profile carries a headline distinct from `hook`
  ("Sembilan kilometer, setiap pagi."). It is in the fixture, not in the
  contract, and not translatable on the model.
- **`quote`** — `['text', 'attribution', 'role']`. The story detail route
  **404s a post that has no quote**, and the landing page's voice section reads
  `PostData::find('ibu-maria-bulu')['quote']`. A required shape that the
  contract never mentions.
- **`photo-essay` is not a kind the model has.** The fixture emits
  `kind => 'photo-essay'`; `PostKind` is `profile | update | news`. Decide:
  add the case, or make essays their own thing. Adding the case is the smaller
  change and matches spec §6.
- **`href` and `name` are overloaded for essays.** An essay's `href` is the
  gallery index, not its own page, and `name` holds the essay title where a
  profile holds the subject's name. Write down which is intended before the
  model reproduces it by accident.
- *Done when:* `docs/data-contract.md` § Post reflects all four and Agent A has
  seen them.

### B2. Post shape methods
- `toCardArray()` (the stories rail and index) and `toDetailArray()` (adds
  `body` and `quote`), keys exactly as the contract says, resolved for
  `app()->getLocale()`.
- The safeguarding rule is structural, not advisory: a minor subject has no
  surname field, so `subjectName()` stays the only way a name reaches a page.
- *Test:* the returned keys equal the contract's keys in both locales, and a
  profile whose subject is a minor cannot produce a full name.

### B3. `PostSeeder`
- The three profiles and two photo essays from `app/ViewModels/PostData.php`,
  both locales, so the rendered pages stay reviewable and identical.
- **Watch the overlap:** `SchoolSeeder` already seeds profile posts with
  portraits attached to schools, and `School::toDetailArray()['people']` reads
  them. Don't seed a second Ibu Maria — the landing page's voice quote and
  Karuni's People section must be the same record.
- *Done when:* `migrate:fresh --seed` gives the stories index every post the
  fixture had, and each school's People section is unchanged.

### B4. Route-model binding for Post
- Same rule as School: `/en/stories/{slug}` resolves the English slug,
  `/id/cerita/{slug}` the Indonesian one, drafts 404. Copy
  `School::resolveRouteBinding()`; the locale comes from the route, not
  `app()->getLocale()`, because binding runs before the `setlocale` middleware.
- *Test:* both locales resolve; the wrong locale's slug 404s.

### B5. Stat and the challenge ledger *(stretch)*
- `Stat::toArray()` for the landing page's stat band plus the `body` field
  added yesterday, and a `StatSeeder`. `StatData::scale()` and `::challenge()`
  are the shapes to match.
- Rule 1 still governs: **no numeric funding display.** A seeded stat that
  reads as an amount raised is a bug, not content.

---

## Agent A — pages

### A1. See the folded Contact section on a real phone *(independent, do first)*
Yesterday's fold-in was verified over HTTP only — the browser extension was
down, so nobody has looked at it. At 400px and with a keyboard:
- Does `/id#kontak` land with the form's heading clear of the sticky nav
  (`scroll-mt-24`), or under it?
- Tab from the nav's Partner button: does focus reach the form's first field,
  and are field errors announced?
- The new faces at phone size: Newsreader's display cut at
  `clamp(2.15rem, …)` and Public Sans at body size.

### A2. Stop seeding the whole database for every page test *(independent)*
`tests/Pest.php` now seeds `SchoolSeeder` before **every** Feature test outside
`Models`, and the suite went from 73s to 105s. Most of those tests never touch
a school. Narrow it to the tests that do — a Pest group, or seeding inside the
files that need it — and keep the suite under 90s. It will get worse with every
entity, so fix it before Post lands, not after.

### A3. Flip `PostData` to Eloquent *(after B2–B4 merge)*
- `PostData::recent()`, `::find()` and `::photoEssays()` call the model's shape
  methods; `href` stays the page's job, as it is for School.
- **No Blade template may change.** If one has to, the contract was wrong —
  stop and fix `docs/data-contract.md` first. That rule is what made yesterday's
  School flip a one-line diff in the end.
- Story detail moves to `{post}` binding, the same shape the school route now
  uses, which is also what makes the language switch carry the reader to the
  other locale's slug (`LocalizedUrl::parametersFor`). Add the cross-locale
  slug test the schools have.
- *Done when:* the stories index, story detail, the gallery, the landing rail
  and the voice quote all render from the database, and a record edited in the
  database shows up on the page.

### A4. Write down the order for the four remaining fixtures *(cheap, end of day)*
Home, Partner, Tier and About are all that is left. Note in
`docs/data-contract.md` which is next and what each one blocks — About is copy
in `lang/`, not really an entity, and may never need a model at all. Ten
minutes now saves tomorrow's first hour.

---

## Checkpoints

**Midday:** B1 agreed and written, B2 in progress; A1 and A2 done.

**End of day:** B merges `feature/data-layer-posts`; A does A3; full suite
green; the stories pages render from the database.

## Not today

The Filament panel for Vera, the upload UI, Home/Partner/Tier integration, and
the deferred pages.

## Open items that need a person, not an agent

- **Publishing the homes' locations** — still needs a child-protection
  specialist. Oldest open question on the project.
- **The Imagick EXIF check** — B5's stripping is verified under GD only. It
  needs one run on a host that has Imagick, which means the hosting decision.
- **Hosting, Reynold's branding, Dhani's photos** — all unchanged.
- **Push and PR** — nothing has been pushed since the branch was created. Six
  days of work sit on one local branch, in two worktrees, with one copy.
