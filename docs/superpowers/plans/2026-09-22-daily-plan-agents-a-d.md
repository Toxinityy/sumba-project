# Daily plan — 2026-09-22 — Agents A, B, C and D

> **For agentic workers:** Use superpowers:executing-plans to work your own
> lane inline. Steps use checkbox (`- [ ]`) syntax. Do not dispatch subagents
> inside a shared checkout — four agents are already writing to this repo
> today, and a fifth writer nobody is watching is how the day loses a commit.

**Spec:** `docs/superpowers/specs/2026-09-16-hope-for-sumba-design.md`
(authoritative), `docs/superpowers/plans/2026-09-16-foundations-and-design-system.md`,
`AGENTS.md`, and the open findings in `docs/backend-review-report.md`.

---

## Where things stand

Yesterday's visitor-journey pass is finished and its plan is fully checked off:
the homepage survives an empty database, enquiry validation recovers in both
locales with focus landing on the first invalid field, and the language switch
preserves the contact anchor. Verified in headless Chrome at 320/360/400/720/1440.

**Baseline taken this morning at `d5e5c00` with yesterday's changes still in
the working tree: `php artisan test --compact` → 341 tests, 1,371 assertions,
all passing, in 4m41s.**

Two things about that baseline are load-bearing today:

1. **Yesterday's work is still uncommitted.** Nine modified files sit in the
   working tree. Four agents are about to branch. This is the day's first gate
   and nobody starts until it closes — see **A0**.
2. **The suite now takes 4m41s, not the ~90s the 2026-09-20 plan aimed for.**
   Task A2 that day narrowed the blanket `SchoolSeeder` run; the suite has
   grown past it again. With four agents running it on every red-green cycle
   that is roughly twenty minutes of wall clock per agent per hour. See **A3**.

**Goal for the day:** close the first P0 launch blocker properly — media stops
being a placeholder and consent revocation becomes provable over HTTP rather
than in a database query — and make the surname rule structural instead of
advisory. The panel gets the Post resource that phase 2's acceptance criterion
names, and the safeguarding suite gets a CI gate so none of it can silently rot.

---

## Two decisions this plan makes, both of which conflict with the spec

`AGENTS.md` says to treat the spec as correct and **flag** a conflict rather
than guess. These are flagged, not settled. If the ministry or the spec owner
disagrees, the affected tasks change shape.

### Decision 1 — do not add `spatie/laravel-medialibrary` (affects C, D)

Spec §6 and §8 both name the package, and §8 hangs the conversion pipeline off
it. The backend review asked the narrower question first: *"Confirm whether the
planned media-library dependency is needed before adding it."*

The answer here is no, and the reason is that the work it would do is already
done. `media_assets` already carries the file, the translated alt and caption,
the consent link, the focal point and the named crops. `VariantGenerator`
already encodes an AVIF/WebP/JPEG ladder and steps quality down to a byte
budget. `ImageCapabilities` already detects the host chain. `<x-picture>`
already consumes the resulting shape. What is missing is an upload form, a
delivery route and a `toImageArray()` that returns real files — three small
pieces, not a media library.

Adding the package now would mean re-shaping a table that six seeders and
thirteen model tests already write, installing a Filament plugin, and carrying
a conversion queue config — to arrive at the same three pieces.

The cost of saying no: no browsable reusable media picker. Vera re-uploads a
photo she has used before instead of picking it from a library. That is a real
loss and it is the thing to revisit if editors complain. It is not a launch
blocker, and §8's actual requirements — capability detection, conversion at
upload, art direction, the enforced 200KB budget — are all met without it.

**C records this in `docs/data-contract.md` as task C1 and flags it to the spec
owner.** If the answer comes back "add the package", C2–C5 get rewritten.

### Decision 2 — the surname fix is separate storage, not a CHECK constraint (affects B)

The review offers both. A CHECK constraint is the smaller diff, and it is the
wrong choice today for one reason: **there is no host.** `docs/deployment.md`
still records no hosting decision, so nobody can run the version probe that
tells us whether the production MySQL enforces CHECK or parses and ignores it
(MySQL below 8.0.16 does the latter, silently). A constraint that might be
decorative is worse than no constraint, because it reads like enforcement.

Separate storage depends on no host fact. It is also closer to what spec §9
literally says: *"Minor subject records have no surname field at all."*

B still runs the probe and writes the version down — see **B1** — because the
answer is needed eventually regardless. It just does not gate today's work.

---

## Branches and worktrees

Everything branches from **A0's commit**, not from `d5e5c00`.

| Agent | Worktree | Branch |
|---|---|---|
| A — pages | `D:/Projects/Orca IDE/sumba-project` (main checkout) | `design/spec-and-prototype` |
| B — data layer | `D:/Projects/Orca IDE/sumba-data-layer` | `feature/data-layer-safeguarding` |
| C — media | `D:/Projects/Orca IDE/sumba-media` *(create today)* | `feature/media-delivery` |
| D — panel | `D:/Projects/Orca IDE/sumba-panel` *(create today)* | `feature/panel-and-ci` |

B's worktree still sits at the old `feature/data-layer` tip (`c10e248`). As on
2026-09-20, **branch fresh in that worktree or the first commit re-merges work
from six days ago.**

C and D create theirs after A0 lands:

```bash
cd "D:/Projects/Orca IDE/sumba-project"
git worktree add "../sumba-media" -b feature/media-delivery design/spec-and-prototype
git worktree add "../sumba-panel" -b feature/panel-and-ci   design/spec-and-prototype
```

Each new worktree needs its own `.env` (copy `.env.example`), `composer install`
and `php artisan key:generate` before its first test run.

---

## File ownership — do not cross

| Agent A (pages) | Agent B (data layer) | Agent C (media) | Agent D (panel + CI) |
|---|---|---|---|
| `resources/**` | `app/Models/**` † | `app/Services/Images/**` | `app/Filament/**` |
| `app/ViewModels/**` | `database/migrations/**` | `app/Http/Controllers/Media/**` | `app/Providers/Filament/**` |
| `app/Support/**` | `database/factories/**` | `routes/media.php` *(new)* | `.github/**` |
| `routes/web.php` ‡ | `database/seeders/**` | `config/filesystems.php` | `tests/Feature/Admin/**` |
| `lang/**` | `tests/Feature/Models/**` | `config/images.php` *(new)* | |
| `config/locales.php` | | `tests/Feature/Media/**` | |
| `tests/Feature/**` except `Models`, `Media`, `Admin` | | | |

† **`app/Models/MediaAsset.php` is time-shared.** B holds it until the B2
surname commit lands (target: midday). From that commit onward it belongs to C
for the rest of the day and B does not touch it again. This is the only shared
file in the plan and it is shared in sequence, not in parallel.

‡ `routes/web.php` stays A's all day. C's delivery route lives in its own
`routes/media.php` registered from `bootstrap/app.php` precisely so two agents
are not editing one route file. **C makes the one-line `bootstrap/app.php`
registration as part of C2 and tells the others in the same breath** — it is
the only line C writes outside its own column.

`docs/data-contract.md` changes only with the owning agents agreeing, as it did
on 2026-09-20. Today that is C1 (image shape, C + A) and B1 (subject identity,
B + D).

---

## A0 — commit yesterday's work *(gate: nobody branches until this lands)*

**Owner: Agent A. Budget: 15 minutes. Everyone else waits.**

Nine files are modified and unstaged. They are the finished, verified output of
the 2026-09-21 plan; they are not scratch work, and three agents are about to
branch from whatever HEAD is at that moment.

- [x] **Step 1: Read the diff before staging any of it.**

```bash
cd "D:/Projects/Orca IDE/sumba-project"
git diff
git diff --stat
```

Expected: `.gitignore` (3 added lines), the 2026-09-21 plan's verification
record, `resources/js/app.js` (+22, the contact-anchor language switch),
`language-switcher.blade.php`, `sections/form.blade.php`, `routes/web.php`
(+3, the null-safe featured lookup), and three test files.

- [x] **Step 2: Confirm the `.gitignore` additions are intentional.**

Three new ignore lines arrived with a pass that was not about ignoring files.
Check what they ignore. If any of them would hide build output another agent
needs, or a `.env`-adjacent file, fix it now rather than discovering it when
C's variant files vanish from a diff this afternoon.

- [x] **Step 3: Stage explicit paths. Never `git add -A`.**

```bash
git add .gitignore \
  docs/superpowers/plans/2026-09-21-daily-ux-plan.md \
  resources/js/app.js \
  resources/views/components/language-switcher.blade.php \
  resources/views/components/sections/form.blade.php \
  routes/web.php \
  tests/Feature/LanguageSwitcherTest.php \
  tests/Feature/Pages/ContactPageTest.php \
  tests/Feature/Pages/HomePageTest.php
git status --short
```

Expected: nine staged paths. **One untracked file remains: this plan,
`docs/superpowers/plans/2026-09-22-daily-plan-agents-a-d.md`.** That is
deliberate — it is not part of yesterday's fix and does not belong in that
commit. Commit it separately, either side of A0:

```bash
git add docs/superpowers/plans/2026-09-22-daily-plan-agents-a-d.md
git commit -m "docs: plan the media and safeguarding pass across four agents"
```

- [x] **Step 4: Commit.**

```bash
git commit -m "fix: keep the bilingual visitor journey usable when content or delivery fails

Homepage renders before seeding and after a featured record is withdrawn.
Enquiry validation returns localized errors to the contact anchor with focus
on the first invalid field. The language switch preserves the contact anchor.

Co-Authored-By: Claude Opus 5 <noreply@anthropic.com>"
```

- [x] **Step 5: Announce the SHA.** B, C and D branch from it. Post it before
      anyone runs `git worktree add`.

**Done when:** `git status --short` is empty, and three agents have the same
base SHA written down.

---

## Agent A — pages

A's day is deliberately light on new surface. The two P0s are where the risk
is, and A's real-image work depends on B and C landing first — that is
tomorrow, not today. What A has instead is one genuine spec gap and one piece
of infrastructure that is costing every other agent twenty minutes an hour.

### A1. The missing-translation notice *(spec §7, P1 — independent, do after A0)*

**Files:** `app/ViewModels/SchoolData.php`, `app/ViewModels/PostData.php`,
`resources/views/components/` (a new `translation-note` component),
`lang/id.json`, `lang/en.json`, `tests/Feature/Pages/SchoolDetailPageTest.php`,
`tests/Feature/Pages/StoryDetailPageTest.php`.

Spec §7: *"missing locale renders the source language with a quiet inline note
— and that note is itself translated."* `HasTranslations::translationLocale()`
at `app/Models/Concerns/HasTranslations.php:28` already computes exactly the
fact the note needs. The backend review confirmed no view calls it:
`rg translationLocale resources/views` finds nothing. The English page
currently falls back to Indonesian **silently**, which is the one behaviour §7
rules out.

**Interface A produces:** the detail shapes gain one key.

```php
// SchoolData / PostData detail arrays gain:
'translated_from' => null,   // the record is in the requested locale
// or
'translated_from' => 'id',   // this locale is missing; Indonesian is showing
```

`null` means no note. A locale code means render the note naming that locale.
This is the contract key C and D should expect if they ever build the panel's
completeness badges off the same fact — tell D, since D's list view wants it.

- [x] **Step 1: Write the failing test.**

```php
// tests/Feature/Pages/StoryDetailPageTest.php

it('shows a translated fallback note when the English body is missing', function () {
    $post = Post::factory()->create([
        'slug'  => ['id' => 'ibu-maria-bulu', 'en' => 'ibu-maria-bulu'],
        'title' => ['id' => 'Sembilan kilometer, setiap pagi.', 'en' => null],
        'body'  => ['id' => 'Setiap pagi sebelum matahari terbit.', 'en' => null],
        'published_at' => now()->subDay(),
    ]);

    $this->get('/en/stories/ibu-maria-bulu')
        ->assertOk()
        ->assertSee('Not yet available in English')
        ->assertSee('Sembilan kilometer, setiap pagi.');
});

it('shows no note when the translation exists', function () {
    $post = Post::factory()->create([
        'slug'  => ['id' => 'rambu', 'en' => 'rambu'],
        'title' => ['id' => 'Judul', 'en' => 'A title'],
        'body'  => ['id' => 'Isi', 'en' => 'Body text'],
        'published_at' => now()->subDay(),
    ]);

    $this->get('/en/stories/rambu')
        ->assertOk()
        ->assertDontSee('Not yet available in English');
});

it('shows the note in Indonesian when Indonesian is the missing locale', function () {
    $post = Post::factory()->create([
        'slug'  => ['id' => 'a-story', 'en' => 'a-story'],
        'title' => ['id' => null, 'en' => 'An English-first story'],
        'body'  => ['id' => null, 'en' => 'Written in English first.'],
        'published_at' => now()->subDay(),
    ]);

    $this->get('/id/cerita/a-story')
        ->assertOk()
        ->assertSee('Belum tersedia dalam bahasa Indonesia');
});
```

- [x] **Step 2: Run it and watch all three fail.**

```bash
php artisan test tests/Feature/Pages/StoryDetailPageTest.php
```

Expected: the first and third fail on the missing note string. If the second
one fails too, the fallback is broken in a way §7 did not anticipate — stop and
read `translationLocale()` before writing any view code.

- [x] **Step 3: Add the strings to both locale files.**

```json
// lang/en.json
"Not yet available in English. Showing the Indonesian original.": "Not yet available in English. Showing the Indonesian original.",
"Belum tersedia dalam bahasa Indonesia. Menampilkan versi bahasa Inggris.": "Not yet available in Indonesian. Showing the English version."
```

```json
// lang/id.json
"Not yet available in English. Showing the Indonesian original.": "Belum tersedia dalam bahasa Inggris. Menampilkan versi asli bahasa Indonesia.",
"Belum tersedia dalam bahasa Indonesia. Menampilkan versi bahasa Inggris.": "Belum tersedia dalam bahasa Indonesia. Menampilkan versi bahasa Inggris."
```

The note is itself translated — that is the part of §7 that is easy to miss.
A reader on `/id` seeing an English-only story gets the Indonesian sentence.

- [x] **Step 4: Build the component.**

```blade
{{-- resources/views/components/translation-note.blade.php --}}
@props(['from' => null])

@if ($from)
  <p role="note" class="text-sm text-[var(--ink-muted)] italic mb-4">
    {{ $from === 'id'
        ? __('Not yet available in English. Showing the Indonesian original.')
        : __('Belum tersedia dalam bahasa Indonesia. Menampilkan versi bahasa Inggris.') }}
  </p>
@endif
```

Quiet, per §7 — muted and italic, not a warning banner. **Do not give it a
fixed width or a one-line height.** The Indonesian sentence is the longer one
(rule 2), and it must be allowed to wrap to two lines at 360px.

- [x] **Step 5: Populate `translated_from` in the view models and render it.**

In `PostData` and `SchoolData`, on the detail shape only — a card in a rail is
not the place for a fallback note:

```php
'translated_from' => $post->translationLocale('body') === app()->getLocale()
    ? null
    : $post->translationLocale('body'),
```

Then `<x-translation-note :from="$story['translated_from']" />` above the body
in the story detail and school detail pages.

- [x] **Step 6: Run the tests until green, then break it on purpose.**

```bash
php artisan test tests/Feature/Pages/StoryDetailPageTest.php tests/Feature/Pages/SchoolDetailPageTest.php
```

Then delete the `@if ($from)` guard so the note always renders, and confirm the
"shows no note" test goes red. Restore. This is the check that the 2026-09-20
plan caught a counterfeit test with; the note is exactly the kind of assertion
that passes against a string that is always present.

- [x] **Step 7: Commit.**

```bash
git add resources/views/components/translation-note.blade.php \
  resources/views/pages/story.blade.php resources/views/pages/school.blade.php \
  app/ViewModels/PostData.php app/ViewModels/SchoolData.php \
  lang/id.json lang/en.json \
  tests/Feature/Pages/StoryDetailPageTest.php tests/Feature/Pages/SchoolDetailPageTest.php
git commit -m "feat: show the translated fallback note when a locale is missing (spec §7)"
```

**Done when:** an English page falling back to Indonesian says so, in English;
an Indonesian page falling back to English says so, in Indonesian; a fully
translated page says nothing; and the note wraps rather than clips at 360px.

### A2. Tell D which fact the panel's completeness badge reads

Five minutes, and it stops D from computing the same thing a second way.

- [x] Write the `translated_from` key into `docs/data-contract.md` beside the
      detail shapes, noting that it derives from
      `HasTranslations::translationLocale()` and that the panel's per-locale
      completeness badge (§7, D3) must read the same method rather than
      checking `filled()` on raw JSON.

### A3. Get the suite back under two minutes *(infrastructure — everyone pays for this)*

**Files:** `tests/Pest.php`, and the test files that actually need a seed.

This morning's full run: **341 tests, 1,371 assertions, 4m41s.** The
2026-09-20 plan set a target of 90 seconds and narrowed `tests/Pest.php`'s
blanket `SchoolSeeder` to get there. It has drifted back out. Four agents each
running the suite on a red-green cycle turn that into the largest single cost
of the day.

- [x] **Step 1: Find where the time goes before changing anything.**

```bash
php artisan test --profile 2>&1 | tail -30
```

Expected: the ten slowest tests named. Do not guess — if the cost turns out to
be one Livewire panel test or the variant encoder rather than seeding, the fix
is somewhere else entirely and the rest of this task is wrong.

- [x] **Step 2: Check what `tests/Pest.php` currently seeds, and for whom.**

```bash
grep -n "seed\|RefreshDatabase\|uses(" tests/Pest.php
```

- [x] **Step 3: Narrow it.** Seed inside the files that read seeded content, or
      behind a Pest group the page tests opt into. `RefreshDatabase` on a test
      that never touches the database is pure cost.

- [x] **Step 4: Prove both halves.** The suite is faster **and** still green:

```bash
php artisan test --compact
```

Expected: 341 tests, 1,371 assertions, passing, materially under 4m41s. If a
test only passed because something else seeded for it, it fails now — that is
the task finding a real dependency, not a regression. Fix it by seeding in that
file.

- [x] **Step 5: Commit, and post the new number** so B, C and D know what a
      healthy run looks like today.

**Done when:** the suite is green, faster, and no test depends on a seed it
does not ask for.

### Not A's today

Consuming real images in `<x-picture>` — the `sizes` audit and the LCP hero
`fetchpriority` pass — waits until B and C have landed real variants. Starting
it against `PlaceholderImage` means doing it twice.

### Agent A verification record (completed 2026-09-22)

**A0.** Committed at `d39571a`; today's plan committed separately at `242acda`,
which is the SHA B, C and D branch from. The `.gitignore` addition was
`/graphify-out` (skill output, safe); `storage/app/.gitignore` already has a
`*` catch-all, so C's new media disk cannot be committed by accident.

**A1.** Four story cases and two school cases written first; the two
"shows note" cases failed against pages that rendered Indonesian prose on
`/en` with no note. `<x-translation-note>` added, `_fallback_locale` emitted
from `PostData::detail()` and `SchoolData::detail()`, strings in both locale
files under `translation.fallback.id`. Break-on-purpose: removing the
`@if ($from)` guard alone did **not** turn the negatives red — with `$from`
null the component rendered a missing-key string, not the note. Forcing the
real string as well turned all four negatives red, which is what proved them.
The note wraps rather than clips by construction — `max-w-prose`, no fixed
width or height, no `truncate` — but this was not checked in a browser.

**A2.** The contract already named this key `_fallback_locale` in the I6
carve-out of 2026-09-17; this plan invented `translated_from` without
checking. Renamed to the contract's name before D built against either. The
carve-out is now closed and records the emit/render split and the values.

**Found and not fixed, because it is the data layer's file:**
`HasTranslations::translationLocale()` resolves requested locale → default
(`id`), so en→id fallback works and id→en cannot. An English-first record
returns `null` from `trans()` on `/id` and renders a **blank** section with no
note, which breaks §7's "never renders blank". Nothing produces such a record
today, but D1's Post resource makes one enterable by hand. Written up in
`docs/data-contract.md` for B.

**A3.** Profiled before changing anything. The top ten tests were only 28% of
the run, so the cost was per-test overhead, not one slow test. `tests/Pest.php`
seeded both seeders before every test in five directories including files that
never touch the database; narrowed to Pages/Cards/Sections. Two real
dependencies surfaced and were fixed in their own files —
`AccentGroundTest` (walks rendered HTML of every route) and
`Admin/SchoolPanelTest` (edits an existing school).

| Run | Time |
|---|---|
| Baseline, `php artisan test --compact`, 341 tests | **4m41s** |
| Sequential after narrowing, 347 tests | **2m33s** |
| `php artisan test --parallel`, 347 tests, three runs | **52.6s / 58.2s / 55.0s** |

**Sequential is still over the two-minute target; parallel is well under it.**
paratest is already installed via Pest 4 and this machine has 12 cores, so
`--parallel` needs no new dependency. Three consecutive runs were green with
an identical 1,386 assertions, so nothing is being skipped or double-counted.

**Two handoffs A could not make itself:**

- **B owns `composer.json`.** The `composer test` script still runs
  `php artisan test` sequentially. Adding `--parallel` there is B's one-line
  call, and it is where the 5× actually reaches the team.
- **D owns `.github/`.** D5's CI workflow should use `--parallel`, and the
  runtime comment D5 step 3 asks for should read 55s, not 4m41s.

Final state: **347 tests, 1,386 assertions, green.** `git diff --check` clean.
Pint reports `unary_operator_spaces`, `braces_position` and
`not_operator_with_successor_space` on `AccentGroundTest`; the identical three
fixers report against that file at `HEAD~1`, so they pre-date this pass and
unrelated code was not reformatted.

---

## Agent B — data layer

B has one job and it is the smaller of the two P0s: make the no-surname rule
structural. The review's words: *"`Post::saving` and `MediaAsset::saving`
reject surnames on minor records through Eloquent, but bulk/raw updates bypass
those hooks. This conflicts with spec §9's structural no-surname requirement."*

**B holds `app/Models/MediaAsset.php` until B2 lands, then hands it to C. Get
B2 committed before midday.**

### B1. Probe the MySQL version question and write down the decision *(first, 30 min)*

**Files:** `docs/data-contract.md`, `docs/deployment.md`.

Decision 2 above chooses separate storage over a CHECK constraint because no
host exists to prove a CHECK would be enforced. Record the reasoning where the
next person looks, and record what would change it.

- [ ] **Step 1: Confirm the local driver, so the record is not guesswork.**

```bash
php artisan tinker --execute="dump(DB::connection()->getDriverName(), DB::connection()->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION));"
```

Expected: `sqlite` and its version. That is the point — **the test suite runs
on SQLite and proves nothing about MySQL's CHECK behaviour**, which is half of
why the constraint route is not chosen today.

- [ ] **Step 2: Write the decision into `docs/data-contract.md`,** under a new
      "Subject identity" heading:
      - minor-capable tables carry no family-name column at all (§9);
      - adult family names live in one separate table;
      - the trigger to revisit: a host is chosen and reports MySQL ≥ 8.0.16, at
        which point a CHECK constraint becomes a defensible belt alongside the
        braces, not instead of them.

- [ ] **Step 3: Add one line to `docs/deployment.md`'s open questions:** the
      production MySQL version is needed, and name what it decides.

- [ ] **Step 4: Show D the new shape before writing the migration.** D's Post
      resource form has a surname field on it by this afternoon; if it is
      pointed at a column B is about to drop, D writes it twice.

**Done when:** both agents have seen the shape and the reasoning is written
where the next reviewer finds it.

### B2. Drop the surname column from `media_assets` *(the free half — do it first)*

**Files:** new migration `database/migrations/2026_09_22_000001_drop_media_asset_surnames.php`,
`app/Models/MediaAsset.php`, `database/factories/MediaAssetFactory.php`,
`tests/Feature/Models/SafeguardingTest.php`.

`media_assets.subject_family_name` is **never written**. `MediaAssetFactory.php:42`
sets it to `null` and nothing else in `app/`, `database/` or `tests/` assigns
it. There is no data to migrate and nothing to preserve. The column exists only
to be guarded against at `MediaAsset.php:73`.

Dropping it satisfies §9 exactly — *"no surname field at all"* — with no
constraint, no separate table and no host dependency.

- [ ] **Step 1: Confirm the column really is unwritten before dropping it.**

```bash
grep -rn "subject_family_name" --include=*.php app database tests | grep -i media
```

Expected: only the `#[Fillable]` list, the guard at line 73, the factory's
`null`, and the migration. **If anything assigns it a value, stop** — the
column has a user this plan did not find, and the task becomes the `posts`
treatment in B3 instead.

- [ ] **Step 2: Write the failing test.**

```php
// tests/Feature/Models/SafeguardingTest.php

it('has no surname column on media assets at all', function () {
    expect(Schema::hasColumn('media_assets', 'subject_family_name'))->toBeFalse();
});

it('cannot store a surname on a media asset even by raw insert', function () {
    // ConsentFactory's default is already minor + web-scoped + current.
    $consent = Consent::factory()->create();

    expect(fn () => DB::table('media_assets')->insert([
        'path' => 'photos/a.jpg', 'width' => 100, 'height' => 100,
        'depicts_minor' => true, 'consent_id' => $consent->id,
        'subject_family_name' => 'Bulu',
        'created_at' => now(), 'updated_at' => now(),
    ]))->toThrow(Illuminate\Database\QueryException::class);
});
```

The second test is the one that matters. It is the review's *"test a raw
update"* — it bypasses Eloquent entirely, which is precisely the hole.

- [ ] **Step 3: Run it and watch both fail.**

```bash
php artisan test tests/Feature/Models/SafeguardingTest.php
```

Expected: the first fails because the column exists; the second fails because
the raw insert **succeeds**. That successful insert is the P0, reproduced.

- [ ] **Step 4: Write the migration.**

```php
// database/migrations/2026_09_22_000001_drop_media_asset_surnames.php
public function up(): void
{
    // Spec §9: minor subject records have no surname field at all. This
    // column was never written by any code path — dropping it makes the
    // rule structural instead of a guard a raw UPDATE walks past.
    Schema::table('media_assets', function (Blueprint $table) {
        $table->dropColumn('subject_family_name');
    });
}

public function down(): void
{
    Schema::table('media_assets', function (Blueprint $table) {
        $table->string('subject_family_name')->nullable();
    });
}
```

- [ ] **Step 5: Remove the column's traces from the model and factory.**

Delete `'subject_family_name'` from the `#[Fillable]` attribute at
`MediaAsset.php:32`, delete the guard block at lines 72–75, and delete the
`'subject_family_name' => null` line from `MediaAssetFactory.php:42`.

**Keep the consent-required guard and the published-owner guard.** Only the
surname branch goes. Update the surviving comment so it no longer describes a
column that is gone.

- [ ] **Step 6: Run the migration and the tests.**

```bash
php artisan migrate
php artisan test tests/Feature/Models/
```

Expected: both new cases pass; the existing safeguarding, factory and
relationship cases still pass.

- [ ] **Step 7: Commit, and tell C that `MediaAsset.php` is now theirs.**

```bash
git add database/migrations/2026_09_22_000001_drop_media_asset_surnames.php \
  app/Models/MediaAsset.php database/factories/MediaAssetFactory.php \
  tests/Feature/Models/SafeguardingTest.php
git commit -m "feat: drop the media asset surname column (spec §9 structural)"
```

**Do not touch `app/Models/MediaAsset.php` again today.**

### B3. Move adult surnames out of `posts` *(the half with real data)*

**Files:** new migration `database/migrations/2026_09_22_000002_extract_post_subject_surnames.php`,
`app/Models/Post.php`, new `app/Models/SubjectSurname.php`,
`database/factories/PostFactory.php`, `database/seeders/PostSeeder.php`,
`database/seeders/SchoolSeeder.php`, `tests/Feature/Models/SafeguardingTest.php`,
`tests/Feature/Models/PostSeederTest.php`.

Unlike media, `posts.subject_family_name` **is** populated — `PostSeeder.php:123`
writes `'Bulu'` for Ibu Maria, and `SchoolSeeder.php:58` writes a family name
for portrait posts. Adults legitimately have surnames on this site. So the
column cannot simply be dropped; the rows have to move.

**Interface B produces** (C does not need this; D and A do):

```php
// app/Models/SubjectSurname.php
// Belongs to a Post. Exists only for adult subjects.
public function post(): BelongsTo;

// app/Models/Post.php
public function subjectSurname(): HasOne;          // null for every minor
public function subjectName(): ?string;            // UNCHANGED signature and
                                                   // output: "Ibu Maria Bulu"
```

`subjectName()`'s signature and its returned string do not change. That is
deliberate: `Post.php:60`, `Post.php:75` and `School.php:90` all call it, and
none of them should need editing. If a caller breaks, the extraction went
wrong.

- [ ] **Step 1: Write the failing tests.**

```php
// tests/Feature/Models/SafeguardingTest.php

it('has no surname column on posts at all', function () {
    expect(Schema::hasColumn('posts', 'subject_family_name'))->toBeFalse();
});

it('cannot attach a surname to a minor subject by raw insert', function () {
    $post = Post::factory()->create(['subject_is_minor' => true, 'subject_given_name' => 'Rambu']);

    expect(fn () => DB::table('subject_surnames')->insert([
        'post_id' => $post->id, 'family_name' => 'Bulu',
        'created_at' => now(), 'updated_at' => now(),
    ]))->toThrow(Illuminate\Database\QueryException::class);
});

it('cannot turn an adult with a surname into a minor by raw update', function () {
    $post = Post::factory()->aboutAnAdult()->create(['subject_given_name' => 'Maria']);
    $post->subjectSurname()->create(['family_name' => 'Bulu']);

    expect(fn () => DB::table('posts')->where('id', $post->id)
        ->update(['subject_is_minor' => true]))
        ->toThrow(Illuminate\Database\QueryException::class);
});

it('still renders an adult subject name unchanged', function () {
    $post = Post::factory()->aboutAnAdult()->create([
        'subject_given_name' => 'Maria', 'subject_honorific' => 'Ibu',
    ]);
    $post->subjectSurname()->create(['family_name' => 'Bulu']);

    expect($post->fresh()->subjectName())->toBe('Ibu Maria Bulu');
});
```

The third case is the one that earns the composite key. A raw `UPDATE posts SET
subject_is_minor = 1` on a row that already has a surname is exactly the
"bulk/raw update" the review named, and it must fail at the database, not in a
model event.

- [ ] **Step 2: Run them and watch them fail.**

```bash
php artisan test tests/Feature/Models/SafeguardingTest.php --filter=surname
```

Expected: every case fails, and the raw-insert and raw-update cases fail by
**succeeding** rather than throwing. Note the exact output — it is the P0's
reproduction and belongs in the end-of-day record.

- [ ] **Step 3: Write the migration.** Three phases in one `up()`, in order:

```php
public function up(): void
{
    // Composite key the child table points at. A surname row can only
    // reference a post whose subject_is_minor is false, so both "give a
    // minor a surname" and "turn a surnamed adult into a minor" become
    // foreign-key violations rather than model-event guesses.
    Schema::table('posts', function (Blueprint $table) {
        $table->unique(['id', 'subject_is_minor'], 'posts_id_minor_unique');
    });

    Schema::create('subject_surnames', function (Blueprint $table) {
        $table->id();
        $table->foreignId('post_id')->unique();

        // Fixed false: this table describes adults only. It is a real
        // column rather than a literal in the FK because MySQL will not
        // reference a constant.
        $table->boolean('subject_is_minor')->default(false);

        $table->string('family_name');
        $table->timestamps();

        $table->foreign(['post_id', 'subject_is_minor'])
            ->references(['id', 'subject_is_minor'])->on('posts')
            ->cascadeOnDelete();
    });

    // Move the existing rows before the column goes. Adults only — a minor
    // row with a surname is already a §9 violation and must not be carried
    // forward; it is dropped and counted, not migrated.
    DB::table('posts')
        ->whereNotNull('subject_family_name')
        ->where('subject_family_name', '!=', '')
        ->where('subject_is_minor', false)
        ->orderBy('id')
        ->each(fn ($post) => DB::table('subject_surnames')->insert([
            'post_id' => $post->id,
            'subject_is_minor' => false,
            'family_name' => $post->subject_family_name,
            'created_at' => now(), 'updated_at' => now(),
        ]));

    Schema::table('posts', function (Blueprint $table) {
        $table->dropColumn('subject_family_name');
    });
}

public function down(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->string('subject_family_name')->nullable();
    });

    DB::table('subject_surnames')->orderBy('id')->each(
        fn ($row) => DB::table('posts')->where('id', $row->post_id)
            ->update(['subject_family_name' => $row->family_name])
    );

    Schema::dropIfExists('subject_surnames');
    Schema::table('posts', fn (Blueprint $table) => $table->dropUnique('posts_id_minor_unique'));
}
```

- [ ] **Step 4: Count what the migration would discard, before running it.**

```bash
php artisan tinker --execute="dump(DB::table('posts')->whereNotNull('subject_family_name')->where('subject_family_name','!=','')->where('subject_is_minor',true)->count());"
```

Expected: `0`. Anything above zero is a live §9 violation already in the data —
**stop, name the rows in the end-of-day record, and raise it** before a
migration silently deletes evidence of it.

- [ ] **Step 5: Enable foreign keys in the test connection.** SQLite ignores
      foreign keys unless asked. Check `config/database.php`'s `sqlite` block
      for `'foreign_key_constraints' => true`. **If it is off, the three raw-write
      tests will pass locally and the constraint will do nothing** — a false
      green on a P0 is worse than a red.

- [ ] **Step 6: Implement the model side.**

```php
// app/Models/SubjectSurname.php
#[Fillable(['family_name'])]
class SubjectSurname extends Model
{
    public function post(): BelongsTo { return $this->belongsTo(Post::class); }
}
```

In `Post.php`: add `subjectSurname(): HasOne`, drop `'subject_family_name'`
from `#[Fillable]` (line 19), delete the `saving` guard at line 43, and change
line 122 from `$this->subject_is_minor ? null : $this->subject_family_name` to
`$this->subjectSurname?->family_name`. The minor branch is no longer needed —
a minor cannot have a surname row at all.

- [ ] **Step 7: Update the factory and both seeders.**

`PostFactory.php:36` and `:47` set `subject_family_name`. Replace the `adult()`
state's surname with an `afterCreating` that creates the `SubjectSurname` row.
`PostSeeder.php:39-40` looks a post up by given **and** family name — rewrite
that lookup against the new relation. `PostSeeder.php:123` and
`SchoolSeeder.php:58` write surnames; both move to the relation.

- [ ] **Step 8: Migrate fresh, seed, and run the model suite.**

```bash
php artisan migrate:fresh --seed
php artisan test tests/Feature/Models/
```

Expected: all four new cases pass, `PostSeederTest`'s `'Ibu Maria Bulu'`
assertion still passes, and `School::toDetailArray()['people']` still carries
names. **If a Blade template has to change, the extraction leaked** — stop and
fix `subjectName()` instead.

- [ ] **Step 9: Break it on purpose.** Drop the composite foreign key by hand,
      rerun the raw-insert test, confirm it goes red. Restore. A foreign key
      that SQLite is quietly ignoring looks exactly like one that works.

- [ ] **Step 10: Run the full suite, then commit.**

```bash
php artisan test --compact
git add database/migrations/2026_09_22_000002_extract_post_subject_surnames.php \
  app/Models/Post.php app/Models/SubjectSurname.php \
  database/factories/PostFactory.php database/seeders/PostSeeder.php \
  database/seeders/SchoolSeeder.php \
  tests/Feature/Models/SafeguardingTest.php tests/Feature/Models/PostSeederTest.php
git commit -m "feat: move adult surnames to their own table so §9 holds at the schema"
```

**Done when:** no minor-capable table has a surname column; a raw insert and a
raw update both fail at the database; `subjectName()` still returns
`"Ibu Maria Bulu"`; and no Blade template changed.

### B4. Make `SchoolSeeder` rerunnable *(P2, stretch — only if B3 lands early)*

`SchoolSeeder` uses `School::create` per fixed record, so a second run
duplicates all six schools. If seed reruns will be part of deployment, switch
to `updateOrCreate` keyed on the Indonesian slug — **without overwriting
editorial changes**, which means updating only the structural fields, never the
translated copy. If that distinction is not cleanly drawable, write "one-time
import only" into `docs/deployment.md` instead and move on. Ten minutes either
way; do not build a merge strategy today.

---

## Agent C — media

C owns the larger P0. The review: *"Media remains a placeholder flow.
`MediaAsset.php:98` returns `PlaceholderImage`; `VariantGenerator.php:98`
writes private files that no public image array references. There is no panel
upload path. A child image copied into the public disk before `MediaAsset::saving`
runs could be fetched directly even if consent blocks the record."*

**The shape of the fix, stated once so the tasks make sense:** nothing goes on
the public disk, ever. Originals and variants live on a private disk. Every
image reaches a browser through one controller that asks `isPublishable()`
first. That single decision closes four things at once — the pre-`saving` race
has nowhere to write to, revocation is instant because there is no public file
to purge, `public/storage` never needs to exist on a host with no shell, and
the review's *"verify revocation by fetching a formerly public URL"* becomes a
test that passes by construction rather than by cache-purging discipline.

The cost is that every image request touches PHP rather than being served by
Apache directly. On cPanel shared hosting behind Cloudflare, with immutable
content-hashed URLs and far-future cache headers, the origin sees each variant
roughly once. That is the trade and it is the right one for a site whose
highest-severity failure mode is a child's photograph outliving its consent.

**C does not touch `app/Models/MediaAsset.php` until B announces the B2
commit.** Everything in C1–C3 is buildable before then.

### C1. Write the image contract amendment *(first — unblocks A and D)*

**Files:** `docs/data-contract.md`.

- [ ] **Step 1: Record Decision 1** — no `spatie/laravel-medialibrary` — with
      the reasoning from the top of this plan, and the cost admitted plainly
      (no reusable media picker). Flag it to the spec owner; §6 and §8 both
      name the package and this contradicts them.

- [ ] **Step 2: Amend "The image shape"** at `docs/data-contract.md:24`. The
      shape itself does not change — that is the point, and it is why
      `<x-picture>` needs no edit. What changes is where the URLs point:

```php
[
    'sources' => [
        'avif' => ['/media/v/9f2a…-800.avif 800w',  '/media/v/1b7c…-1600.avif 1600w'],
        'webp' => ['/media/v/3d81…-800.webp 800w',  '/media/v/c40e…-1600.webp 1600w'],
        'jpeg' => ['/media/v/77ab…-800.jpeg 800w',  '/media/v/e912…-1600.jpeg 1600w'],
    ],
    'width'  => 1600,
    'height' => 900,
    'alt'    => 'Murid TK Karuni di ruang kelas',
]
```

Note four facts for A and D:

- URLs are content-hashed and immutable; the hash is the variant's identity.
- **They are not public files.** `/media/v/{hash}.{format}` is a route, and it
  returns 404 for anything whose owning asset is not publishable *right now*.
- `jpeg` is still always present; which of `avif`/`webp` appear still depends
  on `ImageCapabilities::bestChain()`, exactly as before.
- An asset with no generated variants yet returns the same
  `PlaceholderImage` shape it does today, so a half-migrated database renders
  rather than throwing.

- [ ] **Step 3: Show A and D.** A's `<x-picture>` call sites and D's upload
      field both read this.

### C2. The private disk and the gated delivery route *(the P0's core)*

**Files:** `config/filesystems.php`, `config/images.php` *(new)*,
`routes/media.php` *(new)*, `bootstrap/app.php` *(one line)*,
`app/Http/Controllers/Media/ServeVariantController.php` *(new)*,
`tests/Feature/Media/DeliveryTest.php` *(new)*.

**Interface C produces** (D consumes this in D4):

```php
// Route name: media.variant
// URL:        /media/v/{hash}.{format}
route('media.variant', ['hash' => $hash, 'format' => 'webp']);
```

- [ ] **Step 1: Write the failing test. This is the review's actual ask.**

```php
// tests/Feature/Media/DeliveryTest.php

// `depictingMinor()` is the existing state (MediaAssetFactory.php:38) and its
// default consent is already minor + web-scoped + current. `withVariants()`
// is new — see the factory note below before writing it.

it('serves a variant whose asset has current web consent', function () {
    $asset = MediaAsset::factory()->depictingMinor()->withVariants()->create();
    $hash  = $asset->variants()->first()->hash;

    $this->get("/media/v/{$hash}.jpeg")
        ->assertOk()
        ->assertHeader('content-type', 'image/jpeg');
});

it('stops serving that exact URL the moment consent is withdrawn', function () {
    $asset = MediaAsset::factory()->depictingMinor()->withVariants()->create();
    $url   = "/media/v/{$asset->variants()->first()->hash}.jpeg";

    $this->get($url)->assertOk();

    $asset->consent->withdraw();

    // The review asked for exactly this: fetch the formerly public URL, do
    // not merely query the database and believe it.
    $this->get($url)->assertNotFound();
});

it('stops serving when consent lapses rather than is withdrawn', function () {
    $asset = MediaAsset::factory()->depictingMinor()->withVariants()->create();
    $url   = "/media/v/{$asset->variants()->first()->hash}.jpeg";

    $this->get($url)->assertOk();

    $this->travelTo($asset->consent->review_on->addDay());

    $this->get($url)->assertNotFound();
});

it('keeps no file on the public disk', function () {
    MediaAsset::factory()->depictingMinor()->withVariants()->create();

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});
```

The fourth test is the structural one. It is what makes the pre-`saving` race
in the review impossible rather than merely unlikely: there is no public disk
for a child's photograph to be raced onto.

- [ ] **Step 2: Run it and watch every case fail.**

```bash
php artisan test tests/Feature/Media/DeliveryTest.php
```

Expected: 404 on the route for all four — it does not exist yet.

- [ ] **Step 3: Add the private media disk.**

```php
// config/filesystems.php — alongside 'local' and 'public'
'media' => [
    'driver' => 'local',
    'root'   => storage_path('app/media'),
    'throw'  => false,
    // No 'url', no 'visibility' => 'public', and deliberately not symlinked
    // into public/. Reachable only through ServeVariantController, which
    // asks isPublishable() first.
],
```

- [ ] **Step 4: Add `config/images.php`** for the variant widths and byte
      budgets, so the 200KB hero budget §8 requires is a configured number
      rather than a literal buried in a call site:

```php
return [
    'widths'  => [400, 800, 1200, 1600],
    'budgets' => ['hero' => 200 * 1024, 'default' => 120 * 1024],
    'working_max' => 3000,   // §8's downscale ceiling, for PHP memory limits
];
```

- [ ] **Step 5: Write the controller.**

```php
// app/Http/Controllers/Media/ServeVariantController.php
public function __invoke(string $hash, string $format): StreamedResponse
{
    $variant = MediaVariant::where('hash', $hash)->where('format', $format)
        ->with('asset.consent')->firstOrFail();

    // The whole gate, in one place. Not cached, because a cached "yes" is
    // how withdrawal stops being immediate (§9).
    abort_unless($variant->asset->isPublishable(), 404);

    return Storage::disk('media')->response($variant->path, headers: [
        'Content-Type'  => $variant->mimeType(),
        // Immutable because the hash IS the content. Cloudflare may hold it;
        // withdrawal is covered by C4's purge, not by short TTLs here.
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
}
```

**404, not 403.** A 403 on a specific child's photo URL confirms the photo
exists. 404 says nothing.

- [ ] **Step 6: Register the route in its own file**, so `routes/web.php`
      stays A's all day:

```php
// routes/media.php
Route::get('/media/v/{hash}.{format}', ServeVariantController::class)
    ->where(['hash' => '[a-f0-9]{32}', 'format' => 'avif|webp|jpeg'])
    ->name('media.variant');
```

One line in `bootstrap/app.php`'s `withRouting(...)`: `then: fn () =>
Route::middleware('web')->group(base_path('routes/media.php'))`. **Tell the
other three agents when this line lands** — it is the only edit C makes outside
its own column, and `bootstrap/app.php` is a file nobody expects to move.

- [ ] **Step 7: Run the tests — and read the factory note first.**

> **One new factory state, wanted by three agents.** C2, C3 and D4 all need
> `MediaAssetFactory::withVariants()`, which does not exist, and
> `database/factories/**` is B's column. Every other state these tests use is
> already there and should be used under its real name — `depictingMinor()`
> (`MediaAssetFactory.php:38`), `aboutAnAdult()` and `draft()`
> (`PostFactory.php:42`, `:53`), and `printOnly()`, `lapsed()`, `forAdult()`
> (`ConsentFactory.php:30-48`). `ConsentFactory`'s **default is already minor,
> web-scoped and current**, so there is no `minor()` or `webScoped()` state to
> call and none should be added.
>
> **C asks B for `withVariants()` once, in the morning, alongside the
> `media_variants` migration in C4.** Three agents inventing three states on
> one factory is a merge conflict with a safeguarding test on the wrong side
> of it.

```bash
php artisan test tests/Feature/Media/DeliveryTest.php
```

- [ ] **Step 8: Commit.**

```bash
git add config/filesystems.php config/images.php routes/media.php \
  bootstrap/app.php app/Http/Controllers/Media/ServeVariantController.php \
  tests/Feature/Media/DeliveryTest.php
git commit -m "feat: serve media through a consent gate instead of the public disk"
```

**Done when:** a formerly working image URL returns 404 within one request of
withdrawal, and the public disk is empty.

### C3. Ingest: validate, downscale, strip, generate

**Files:** `app/Services/Images/Ingestor.php` *(new)*,
`app/Services/Images/VariantGenerator.php`, `tests/Feature/Media/IngestTest.php` *(new)*.

The review's list: *"design the upload boundary on private storage, strip
metadata before any public copy, validate content/dimensions, generate the
named crops and bounded variants."*

**Interface C produces** (D calls this from the panel in D4):

```php
// app/Services/Images/Ingestor.php
public function ingest(UploadedFile $file, MediaAsset $asset): void;
// Throws DomainException on anything that is not a decodable image, is under
// 800px on the long edge, or exceeds the configured upload ceiling.
// On success: original stored on the 'media' disk with EXIF gone, and one
// MediaVariant row per (crop × width × format) the host can encode.
```

- [ ] **Step 1: Write the failing tests.**

```php
it('refuses a file that is not a decodable image', function () {
    $asset = MediaAsset::factory()->create();

    expect(fn () => app(Ingestor::class)->ingest(
        UploadedFile::fake()->createWithContent('payload.jpg', '<?php echo 1;'),
        $asset,
    ))->toThrow(DomainException::class);
});

it('refuses an image too small to crop from', function () {
    $asset = MediaAsset::factory()->create();

    expect(fn () => app(Ingestor::class)->ingest(
        UploadedFile::fake()->image('tiny.jpg', 320, 240), $asset,
    ))->toThrow(DomainException::class);
});

it('strips EXIF before the file lands anywhere', function () {
    $asset = MediaAsset::factory()->create();

    app(Ingestor::class)->ingest(geotaggedFixture(), $asset);

    $stored = Storage::disk('media')->path($asset->fresh()->path);
    expect(@exif_read_data($stored) ?: [])->not->toHaveKey('GPSLatitude');
});

it('generates one variant per width and format within budget', function () {
    $asset = MediaAsset::factory()->create();

    app(Ingestor::class)->ingest(UploadedFile::fake()->image('wide.jpg', 3000, 2000), $asset);

    expect($asset->variants()->where('format', 'jpeg')->count())
        ->toBe(count(config('images.widths')))
        ->and($asset->variants()->max('bytes'))
        ->toBeLessThanOrEqual(config('images.budgets.default'));
});
```

The first test is the one that matters most. `UploadedFile::fake()->image()`
produces a real image; `createWithContent` with PHP source and a `.jpg` name is
the actual attack, and extension checking alone passes it.

- [ ] **Step 2: Run them and watch them fail.**

```bash
php artisan test tests/Feature/Media/IngestTest.php
```

- [ ] **Step 3: Implement `Ingestor`,** in this order — the order is the
      safeguarding property:

  1. Decode with Intervention. A decode failure **is** the content validation;
     no `getMimeType()`, no extension trust.
  2. Reject under 800px on the long edge — §8 wants 3000px masters, and
     anything that cannot fill an 800w variant is a mistake, not a small photo.
  3. `scaleDown(width: config('images.working_max'))` before anything else
     touches it, per §8's PHP-memory note.
  4. Re-encode with `strip: true`. **The re-encoded bytes are the only thing
     ever written to disk — the uploaded temp file is never copied anywhere.**
     That is what makes "strip before any public copy" true by construction
     rather than by ordering luck.
  5. Store the stripped original on the `media` disk.
  6. Generate variants per crop × width × format via `VariantGenerator`.

- [ ] **Step 4: Point `VariantGenerator` at the media disk.** It currently
      writes to `storage_path('app/variants')` via `mkdir` at line ~98. Change
      `writeToDisk()` to use `Storage::disk('media')` and return the
      disk-relative path. Keep the content hash — it is the URL identity now —
      and keep the quality ladder and the floor warning exactly as they are.
      `tests/Feature/VariantGeneratorTest.php` is A's by the ownership table's
      "except" clause; **ask A to run it, or ask for the file to be moved to
      `tests/Feature/Media/` in C1's amendment.**

- [ ] **Step 5: Run the ingest and delivery tests together, then commit.**

```bash
php artisan test tests/Feature/Media/
git commit -m "feat: ingest uploads on private storage with stripping and budgets"
```

**Done when:** a PHP file named `.jpg` is refused, a geotagged photo lands with
no GPS, and every generated variant is inside its configured budget.

### C4. Wire the model to real variants *(after B announces B2 — not before)*

**Files:** `app/Models/MediaAsset.php`, `app/Models/MediaVariant.php` *(new)*,
migration for `media_variants` — **coordinate with B, migrations are B's
column.** Ask B to create the migration from C's stated columns rather than
writing it in C's worktree.

- [ ] **Step 1:** `MediaVariant` — columns `asset_id`, `hash`, `format`,
      `crop`, `width`, `height`, `bytes`, `path`; unique on `(hash, format)`.
      Plus the one method the controller calls:

```php
public function mimeType(): string
{
    return 'image/'.$this->format;   // avif | webp | jpeg
}
```

- [ ] **Step 2:** `MediaAsset::variants(): HasMany`.

- [ ] **Step 3: Replace `toImageArray()` at `MediaAsset.php:98`.** Group the
      variants by format into the contract's `sources` shape, ordered by width.
      **Keep the `PlaceholderImage` return as the fallback when the asset has
      no variants** — six seeders write placeholder rows, and a hard switch
      turns every one of them into a broken image.

```php
public function toImageArray(): array
{
    $variants = $this->variants()->orderBy('width')->get();

    if ($variants->isEmpty()) {
        return PlaceholderImage::make((int) $this->width, (int) $this->height, (string) $this->trans('alt'));
    }

    $sources = $variants->groupBy('format')->map(
        fn ($group) => $group->map(fn ($v) => route('media.variant', [
            'hash' => $v->hash, 'format' => $v->format,
        ]).' '.$v->width.'w')->all()
    )->all();

    $fallback = $variants->where('format', 'jpeg')->last();

    return [
        'sources' => $sources,
        'width'   => (int) $fallback->width,
        'height'  => (int) $fallback->height,
        'alt'     => (string) $this->trans('alt'),
    ];
}
```

- [ ] **Step 4: Change `stripMetadata()` to the media disk.** Lines 118–131
      use `Storage::disk('public')`. Nothing is on the public disk any more, so
      as written it silently does nothing — it returns early on
      `! $disk->exists()`. **That early return is why this must be changed in
      the same commit as the disk switch**, or EXIF stripping quietly becomes a
      no-op that still has a passing test.

- [ ] **Step 5: Run the existing EXIF test and the media suite.**

```bash
php artisan test tests/Feature/Models/ExifStrippingTest.php tests/Feature/Media/
```

- [ ] **Step 6: Break it on purpose.** Comment out the `abort_unless` in the
      controller and confirm the withdrawal test goes red. Restore. A gate that
      is never observed failing is a gate nobody has tested.

- [ ] **Step 7: Full suite, then commit.**

### C5. Write down what revocation still does not reach *(end of day, 15 min)*

Private storage plus a live gate closes the origin. It does not close a CDN
that has already cached a variant, and `docs/deployment.md` plans Cloudflare in
front of the origin.

- [ ] Add a "Media revocation" section to `docs/deployment.md`: the origin gate
      is immediate; a Cloudflare cache purge by URL prefix is required on
      withdrawal and **is not implemented**; and until a zone exists,
      `Cache-Control: immutable` means a withdrawn image can survive at the edge
      for as long as the edge holds it.

- [ ] Name the two options for whoever provisions the zone: a purge-by-tag call
      in `Consent::withdraw()`, or dropping `immutable` and accepting more
      origin traffic. **Do not implement either today** — both need a real zone
      and an API token to be more than speculation.

- [ ] Note that the review's other open media item — *"withdrawal reaches
      attached owners, not rich-text images"* — is **still open**, and that C2's
      gate means a rich-text `<img>` pointing at `/media/v/…` is already
      covered, while one pointing anywhere else is not. That is an argument for
      §8's rich-text rewrite pass, which is not today.

---

## Agent D — panel and CI

D closes phase 2's acceptance criterion — *"Vera can create a school and a post
end to end and preview them"* — as far as it goes without C's upload, and gets
a CI gate under the safeguarding suite. Only Schools have a resource today;
Posts, consent review and the expiry dashboard are absent.

### D1. The Post resource *(independent of everyone — start right after A0)*

**Files:** `app/Filament/Resources/Posts/PostResource.php`,
`app/Filament/Resources/Posts/Schemas/PostForm.php`,
`app/Filament/Resources/Posts/Tables/PostsTable.php`,
`app/Filament/Resources/Posts/Pages/{ListPosts,CreatePost,EditPost}.php`,
`tests/Feature/Admin/PostPanelTest.php`.

Copy the shape of `app/Filament/Resources/Schools/` — it is the house pattern
and it already solved translatable fields and per-locale slugs.

- [ ] **Step 1: Write the failing tests** — the same four classes of bug the
      School panel review found, because a second resource will have them too:

```php
// tests/Feature/Admin/PostPanelTest.php

it('creates a bilingual post as a draft', function () {
    livewire(CreatePost::class)->fillForm([
        'title' => ['id' => 'Sembilan kilometer', 'en' => 'Nine kilometres'],
        'slug'  => ['id' => 'sembilan-kilometer', 'en' => 'nine-kilometres'],
        'kind'  => 'profile',
        'subject_given_name' => 'Maria',
    ])->call('create')->assertHasNoFormErrors();

    expect(Post::where('slug->id', 'sembilan-kilometer')->first()->published_at)->toBeNull();
});

it('rejects a duplicate slug within one locale', function () {
    Post::factory()->create(['slug' => ['id' => 'taken', 'en' => 'taken-en']]);

    livewire(CreatePost::class)->fillForm([
        'title' => ['id' => 'X', 'en' => 'X'],
        'slug'  => ['id' => 'taken', 'en' => 'fresh'],
        'kind'  => 'profile',
    ])->call('create')->assertHasFormErrors(['slug.id']);
});

it('rejects a currency amount in translated public copy', function () {
    livewire(CreatePost::class)->fillForm([
        'title' => ['id' => 'X', 'en' => 'X'],
        'slug'  => ['id' => 'a', 'en' => 'b'],
        'kind'  => 'update',
        'hook'  => ['id' => 'Kami butuh Rp 15.000.000 untuk atap.', 'en' => 'We need funds.'],
    ])->call('create')->assertHasFormErrors(['hook.id']);
});

it('refuses to publish a post carrying media without current consent', function () {
    $post = Post::factory()->draft()->create();

    // Not a null consent — MediaAsset::saving refuses that outright, so the
    // asset would never exist. Print-only consent is the real editorial
    // mistake: a signed form that does not cover the web (§9).
    $asset = MediaAsset::factory()
        ->depictingMinor(Consent::factory()->printOnly()->create())
        ->for($post, 'attachable')->create();

    livewire(EditPost::class, ['record' => $post->getKey()])
        ->fillForm(['published_at' => now()])
        ->call('save')
        ->assertHasFormErrors();

    expect($post->fresh()->published_at)->toBeNull();
});
```

The third test is rule 1 from `AGENTS.md` — *no numeric funding display,
anywhere*. `SchoolForm.php` already rejects a currency amount in `current_need`;
a post's `hook` and `body` are far more likely places for one to arrive, and a
Blade template will happily render it.

The fourth is §9's publishing gate reaching the panel: *"Filament blocks it
rather than warning."* `Publishable::bootPublishable()` throws a
`DomainException`; **a raw exception surfacing as a Livewire 500 is not
blocking, it is crashing.** Catch it and attach it to the form field.

- [ ] **Step 2: Run them and watch all four fail** (the resource does not
      exist, so they fail on a missing class — that is expected and fine).

```bash
php artisan test tests/Feature/Admin/PostPanelTest.php
```

- [ ] **Step 3: Generate and shape the resource.**

```bash
php artisan make:filament-resource Post --generate
```

Then make it match `SchoolForm.php:27`'s conventions: translatable fields as
per-locale inputs, per-locale unique slug rules, and the currency rejection.
**Reuse the School form's currency validation rule rather than writing a second
one** — if the regex lives in two places it will be fixed in one.

- [ ] **Step 4: Run until green.**

- [ ] **Step 5: Break it on purpose.** Remove the per-locale slug rule and
      confirm the duplicate test goes red. Restore.

- [ ] **Step 6: Commit.**

```bash
git add app/Filament/Resources/Posts tests/Feature/Admin/PostPanelTest.php
git commit -m "feat: add the Post resource so Vera can write a story end to end"
```

**Done when:** a post can be created as a draft, published, and refused
publication when its media lacks consent — with a form error, not a stack trace.

### D2. The consent review dashboard *(§9's expiry requirement)*

**Files:** `app/Filament/Resources/Consents/**`,
`app/Filament/Widgets/ExpiringConsentsWidget.php`,
`tests/Feature/Admin/ConsentPanelTest.php`.

Spec §9: *"Consent records carry review dates; the dashboard lists what's
expiring. A story about a nine-year-old is still indexed when they're nineteen."*
`review_on` exists on the model and `coversWebUse()` already enforces it. There
is no panel surface, so nobody sees it coming.

- [ ] **Step 1: Write the failing tests.**

```php
it('lists consents expiring within thirty days', function () {
    $soon  = Consent::factory()->create(['review_on' => now()->addDays(10)]);
    $later = Consent::factory()->create(['review_on' => now()->addYear()]);

    livewire(ExpiringConsentsWidget::class)
        ->assertCanSeeTableRecords([$soon])
        ->assertCanNotSeeTableRecords([$later]);
});

it('unpublishes the owning records when withdrawal is triggered from the panel', function () {
    $consent = Consent::factory()->create();
    $school  = School::factory()->create(['published_at' => now()->subDay()]);
    MediaAsset::factory()->depictingMinor($consent)->for($school, 'attachable')->create();

    livewire(ListConsents::class)->callTableAction('withdraw', $consent);

    expect($school->fresh()->published_at)->toBeNull()
        ->and($consent->fresh()->withdrawn_at)->not->toBeNull();
});
```

- [ ] **Step 2: Run and watch them fail.**

- [ ] **Step 3: Build a read-mostly Consent resource.** Subject, guardian,
      scope, granted, review date, withdrawn state, and **one withdraw action
      that calls the existing `Consent::withdraw()`**. Do not reimplement
      withdrawal in the panel — it is transactional at `Consent.php`, and a
      second implementation is a second chance to get the transaction wrong.

      Give the action a confirmation step. It unpublishes content immediately
      and by design there is no undo.

- [ ] **Step 4: Add the widget** to the panel dashboard: consents whose
      `review_on` is inside 30 days and which are not withdrawn.

- [ ] **Step 5: Run, break on purpose, commit.**

**Done when:** an editor can see what lapses this month and withdraw a consent
in two clicks, and the withdrawal unpublishes through the model's own path.

### D3. Per-locale completeness badges *(§7, small — after A2's note)*

Spec §7: *"per-locale completeness badges on list views, translation status
visible before publish."*

- [ ] Add a column to the Post and School list tables showing which locales are
      complete. **Read `HasTranslations::translationLocale()`** — the same
      method A1 renders the public note from. Two different definitions of
      "translated" between the panel and the site is a bug report waiting to be
      filed.

- [ ] One test: a record with Indonesian only shows Indonesian complete and
      English incomplete.

### D4. The upload field *(after C announces C3 — afternoon)*

**Files:** `app/Filament/Resources/Posts/Schemas/PostForm.php`,
`app/Filament/Resources/Schools/Schemas/SchoolForm.php`,
`tests/Feature/Admin/MediaUploadTest.php`.

- [ ] **Step 1: Write the failing test.**

```php
it('rejects an upload that is not a real image', function () {
    livewire(EditPost::class, ['record' => Post::factory()->create()->getKey()])
        ->fillForm(['media' => [UploadedFile::fake()->createWithContent('x.jpg', '<?php')]])
        ->call('save')
        ->assertHasFormErrors();
});

it('stores an uploaded image on the private disk and nowhere else', function () {
    livewire(EditPost::class, ['record' => ($post = Post::factory()->create())->getKey()])
        ->fillForm(['media' => [UploadedFile::fake()->image('hero.jpg', 3000, 2000)]])
        ->call('save')->assertHasNoFormErrors();

    expect($post->fresh()->media()->count())->toBe(1)
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
});
```

- [ ] **Step 2: Add a `FileUpload` field pointed at the `media` disk**, calling
      `Ingestor::ingest()` in its save handler. **Do not let Filament store the
      file itself** — every write goes through the ingestor, or the validation,
      downscaling and stripping are optional and someone will find the path
      that skips them.

- [ ] **Step 3: Add the alt-text field beside it and make it required.**
      Localised, per §7, and it is what `toImageArray()` renders. An image with
      no alt is an accessibility failure the test suite will not catch.

- [ ] **Step 4: Run, then commit.**

**Done when:** Vera can attach a photo to a post from the panel, it lands
stripped on private storage, and a disguised PHP file is refused.

### D5. CI for the safeguarding suite *(P2, cheap — end of day)*

**Files:** `.github/workflows/tests.yml` *(new)*.

The review: *"No `.github` directory or other tracked CI workflow was found.
Attach the existing `php artisan test` command to the chosen CI provider before
launch; verify a failing safeguarding test blocks the build."*

- [ ] **Step 1: Write the workflow** — PHP 8.3 to match `composer.json`,
      `composer install`, `npm ci && npm run build`, `php artisan test`. On
      push and pull request.

- [ ] **Step 2: Do the verification the review actually asked for.** A workflow
      that has never failed is a workflow nobody knows runs.

```bash
# On a scratch branch, break the §9 guard on purpose:
#   comment out the consent check in Publishable::bootPublishable()
git checkout -b ci-proof && git commit -am "temp: break the consent gate" && git push
# Confirm the run goes red and names the safeguarding test.
# Then delete the branch — locally and on the remote.
```

Record the run URL in `docs/deployment.md`. **A green CI badge over an
untested gate is worse than no badge**, because it is evidence to a reviewer
that is not evidence at all.

- [ ] **Step 3: Note the suite's runtime in the workflow file** as a comment,
      from A3's post-fix number, so the next person notices when it doubles.

---

## Checkpoints

**Midday.**

- A0 is committed and three worktrees are branched from it.
- **B2 has landed and `app/Models/MediaAsset.php` has transferred to C.** This
  is the one hard handoff in the day; if B2 is not committed, B says so rather
  than letting C discover it in a merge conflict.
- C1's contract amendment is written and A and D have read it.
- C2's delivery gate is green — the withdrawal test fetches a 404.
- D1's Post resource creates a draft.
- A knows whether A3's slowness is seeding or something else.

**End of day.**

- Each agent runs `php artisan test --compact` in its own worktree. **All four
  must be green independently before any merge**, and the integrated run after
  merging must be green too — four green branches do not make a green trunk.
- Merge order, because it is a dependency chain rather than a preference:
  **A0 → B → C → D → A.** B's migrations come before C's model changes; C's
  routes come before D's upload field; A's page work merges last because it
  reads all three.
- Run `php artisan migrate:fresh --seed` on the merged trunk. Two migrations
  landed today from two worktrees; the ordering is only proven by running it.
- `npm run build`, `git diff --check`, and Pint on the changed PHP files.

---

## Not today

- **The rich-text image rewrite pass** (§8). C5 records why C2's gate partially
  covers it. Enabling rich-text uploads before the rewrite exists is what the
  review warns about; do not enable them.
- **Draft preview and revision history.** Both are phase 2 and both need a
  signed route in `routes/web.php`, which is A's file on a day A has other
  work. Tomorrow, with A and D agreeing the route first.
- **`spatie/laravel-medialibrary`,** per Decision 1 — unless the spec owner
  overrules it, in which case C2–C5 are rewritten before they are merged.
- **Real images in `<x-picture>`.** Needs B and C merged. Tomorrow.
- **A Cloudflare purge on withdrawal.** Needs a zone. C5 writes down what is
  missing instead of guessing at an API.

---

## Open items that need a person, not an agent

These have not moved and four agents will not move them.

- **The child-protection specialist review.** Spec §9 opens by saying this is a
  technical standard and not a legal policy. Today makes the *enforcement*
  structural; it does not make the *policy* reviewed. This is the oldest open
  question on the project and it is now the only thing between a technically
  sound safeguarding implementation and a defensible one.
- **Publishing the homes' locations.** Same reviewer, same conversation.
- **A host.** It blocks the MySQL version answer (B1), the Imagick EXIF check,
  Cloudflare purge-on-withdrawal (C5), real SMTP delivery, `storage:link`
  behaviour, the staging restore, and every Lighthouse number §8 asks for
  against staging rather than localhost. It is the single largest unblocker
  available and no agent can buy it.
- **Dhani's photography and Reynold's branding.** C's pipeline is built and
  tested against `UploadedFile::fake()`. It has never seen a real 3000px master
  and the 200KB hero budget has never been measured against a real photograph.
- **Push and open a PR.** Nothing has been pushed to `origin` since the branch
  was created. As of this morning the work sits on one local branch across what
  will be four worktrees, on one machine, with one copy. Four parallel agents
  raise the cost of losing it by exactly four times.
