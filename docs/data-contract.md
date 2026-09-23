# Data contract — the seam between pages and models

**Status:** Authoritative for both workstreams. Changing a shape here requires
agreement from both sides; changing it on one side only is the failure this
document exists to prevent.

Two workstreams are building in parallel:

- **Pages** (`resources/views/pages/**`) consume the arrays below, initially
  from fixtures in `app/ViewModels/`.
- **Data layer** (`app/Models/**`, `database/**`) produces the same arrays
  from Eloquent.

Integration is swapping the fixture call for the model call. **No Blade
template should change at integration time.** If one has to, the contract was
wrong and this document gets fixed first.

These shapes are derived from the props the existing components already
accept, not from the spec in the abstract. The components are built, tested
and reviewed — they are the fixed point, and the data layer bends to them.

---

## The image shape

Used by every entity. `<x-picture>` throws `InvalidArgumentException` if
`sources['jpeg']` is missing or empty — JPEG is the mandatory universal
fallback.

```php
[
    'sources' => [
        'avif' => ['/img/x-800.avif 800w', '/img/x-1600.avif 1600w'], // optional
        'webp' => ['/img/x-800.webp 800w', '/img/x-1600.webp 1600w'], // optional
        'jpeg' => ['/img/x-800.jpg 800w',  '/img/x-1600.jpg 1600w'],  // REQUIRED
    ],
    'width'  => 1600,   // int, the intrinsic width of the fallback
    'height' => 900,    // int
    'alt'    => 'Murid TK Karuni di ruang kelas',  // string, localised
]
```

Which formats appear depends on `ImageCapabilities::bestChain()` on the host —
the data layer must not assume AVIF exists. `jpeg` is always present.

**Known wrinkle, do not "fix" unilaterally:** two components take this shape
*flattened* into their item rather than nested under an `image` key —
`<x-sections.people>` reads `$portrait['sources'|'width'|'height'|'alt'|'name']`
and `<x-sections.evidence>` reads `$step[...|'caption']`. Everything else uses
the nested `'image' => [...]` form.

**The data layer produces the NESTED form everywhere.** Pages do the small
unpacking for those two components. Normalising the components is queued as a
separate change so it gets its own review.

---

## School

Consumed by `<x-cards.school>` (directory) and the school detail page.

```php
[
    'slug'     => 'karuni',
    'href'     => '/id/sekolah/karuni',        // locale-aware, built by the page
    'level'    => 'TK',                        // TK | SMP | SMA
    'age_range' => '4-6 tahun',                // localised, derived from level
    'name'     => 'TK Harapan Karuni',
    'location' => 'Karuni, Sumba Barat Daya',
    'need'     => 'Ruang baca baru untuk 60 anak.',   // ONE sentence, localised
    'status'   => 'Butuh 4 mitra lagi',              // short qualitative string
    'image'    => [ /* image shape */ ],
]
```

**`age_range` is derived from `level`, never stored** — TK 4-6, SMP 12-15,
SMA 15-18 (`'4-6 tahun'` / `'ages 4-6'`). A school whose level says SMP and
whose age range says 4-6 is a data bug a reader would spot before the team
did, so the two cannot be entered separately. The level ladder renders it.
*(Amended 2026-09-19.)*

**`status` is a qualitative string and never a number.** No goal, no amount
raised, no percentage, no progress value. Spec decision 4: a progress bar
frozen at 40% for six months costs more credibility than the precision earns,
and the team cannot keep such numbers current. **If the model grows a
`funding_goal` or `amount_raised` column, this contract has been violated.**

### School detail additionally needs

```php
[
    // ... everything above, plus:
    'lede'    => 'Enam puluh anak belajar di dua ruang kelas...',  // paragraph
    'context' => ['heading' => '...', 'body' => '...', 'image' => [...]],
    'work'    => ['heading' => '...', 'body' => '...', 'image' => [...]],
    'evidence' => [
        'before' => [ /* flattened image */ 'caption' => 'Maret 2026 — ...' ],
        'after'  => [ /* flattened image */ 'caption' => 'Agustus 2026 — ...' ],
    ],
    'facts' => [                       // for <x-sections.current-need>
        ['key' => 'Dibuka',  'value' => '2009'],
        ['key' => 'Murid',   'value' => '60'],
        ['key' => 'Guru',    'value' => '3'],
        ['key' => 'Biaya bagi keluarga', 'value' => 'Gratis'],
    ],
    'people' => [ /* portrait items, see below */ ],
]
```

`facts` values are strings, deliberately — "Gratis" and "60" sit in the same
column, and the component renders them identically.

---

## Home (children's home)

Same shape as School minus `level`, plus `care_model` (a paragraph).

**Safeguarding applies harder here.** Children resident in a home are never
named, and the site never states why a child is in care. The data layer must
not expose a name field for home residents at all — see Post below.

**I5 update (2026-09-17): this section previously had no `facts` shape at
all, while the page renders a four-row facts list — a real gap, found while
moving Children's Homes off template-local content into
`App\ViewModels\HomeData` (see docs/pages-fix-wave-report.md). `facts`
follows the exact shape School detail already uses:**

```php
[
    // ... School detail's shape minus 'level', plus:
    'care_model' => 'Anak-anak tinggal dalam kelompok kecil...',  // paragraph
    'facts' => [                       // for <x-sections.current-need>
        ['key' => 'Rumah anak',              'value' => '2'],
        ['key' => 'Anak yang diasuh',        'value' => '18'],
        ['key' => 'Keluarga pengasuh tetap', 'value' => '2'],
        ['key' => 'Biaya bagi keluarga',     'value' => 'Gratis'],
    ],
    'people' => [ /* house-parent portraits, full names permitted — adults, not residents */ ],
]
```

Same rule as School: `facts` values are strings, and `status` (fed to the
same `<x-sections.current-need>`) is a qualitative string, never a number —
rule 1 below applies here exactly as it does to School.

---

## Post (stories and news)

Consumed by `<x-cards.story>` and the story detail page.

```php
[
    'slug'  => 'rambu-sembilan-kilometer',
    'href'  => '/id/cerita/rambu-sembilan-kilometer',
    'kind'  => 'profile',              // profile | update | news
    'name'  => 'Rambu',                // subject; see the rule below
    'hook'  => 'Berjalan sembilan kilometer setiap pagi.',  // one sentence
    'body'  => '<p>...</p>',           // rendered HTML for detail pages
    'image' => [ /* image shape */ ],  // portrait 4:5 for profiles
    'published_at' => '2026-08-14',
]
```

**Amended 2026-09-20.** Four things the pages have always needed and this
section never said:

- **`title`** — the story's own headline ("Sembilan kilometer, setiap pagi."),
  distinct from `hook`, which is the one-sentence teaser under it. Translated.
- **`quote`** — `['text' => ..., 'attribution' => ..., 'role' => ...]`.
  **Required on a detail page:** the story route 404s a post without one, and
  the landing page's voice section reads
  `PostData::find('ibu-maria-bulu')['quote']`. Only `text` is stored (a
  translated column); `attribution` is `subjectName()` and `role` is
  `subject_role`, so a quote can never name a child differently from the post
  that carries it.
- **`photo-essay` is a kind.** `PostKind` is now
  `profile | update | news | photo-essay`. Essays are posts whose body is
  mostly images (spec §6: "Gallery needs no model").
- **`name` and `href` mean different things per kind, on purpose.** For a
  profile, `name` is the subject's name and `href` is the story's own page.
  For a photo essay, `name` is the essay's title and `href` is the gallery
  index, because an essay has no page of its own. A page reading `name`
  therefore never has to know which kind it holds.

**Safeguarding — structural, not advisory.** Children are referred to by
**first name only**. Adults (teachers, community members, the founder) may be
named in full with their role.

The schema enforces this by giving minor subjects **no surname field at all** —
if it cannot be entered, it cannot leak. `name` for a minor is therefore a
single given name by construction, not by an editor remembering. Never combine
a child's name with a specific village plus a daily routine; each is harmless
alone and a tracing kit together.

---

## SponsorshipTier

Consumed by `<x-cards.tier>` on Get Involved.

```php
[
    'title'       => 'Ruang kelas',
    'cost'        => 'Rp 180.000.000',        // formatted IDR string
    'costApprox'  => 'approx. USD 11,000',    // EN LOCALE ONLY, else null
    'description' => 'Satu ruang kelas lengkap dengan meja, kursi dan papan tulis.',
    'image'       => [ /* image shape */ ],
]
```

Costs are **stored in IDR** and displayed in IDR in both locales.
`costApprox` is populated only on `/en`, from a single manually-set rate in
settings, and is explicitly labelled approximate. Spec decision 9: CSR
departments think in rupiah, but an overseas individual donor reading a bare
IDR figure has no idea whether a classroom costs $200 or $20,000. A manual
rate avoids an exchange-rate API, a scheduled job, and a stale-data bug nobody
notices for months.

**No rate-conversion logic belongs in the component.** The data layer hands it
a formatted string or `null`.

---

## Stat

Consumed by `<x-sections.stat-band>`.

```php
['value' => '612', 'label' => 'Anak bersekolah tahun ini', 'asOf' => 'Per Agustus 2026']
```

The challenge ledger uses the same shape with an optional `body`: `label` is
the bold opening clause and `body` is the sentence that finishes it.
*(Amended 2026-09-19.)*

```php
['value' => '0', 'label' => 'Guru IPA tetap di SMP Harapan Anakalang', 'body' => 'sejak awal tahun ini. ...']
```

`value` is a string. `asOf` exists to make a stale statistic visible to the
team — a number with no date is what a due-diligence reader distrusts. Render
it whenever present.

---

## Portrait item

Consumed by `<x-sections.people>`. **Flattened, not nested** — see the wrinkle
above.

```php
['sources' => [...], 'width' => 800, 'height' => 1000, 'alt' => '...', 'name' => 'Ibu Maria Bulu']
```

---

## Partner

```php
['name' => 'Contoh Mitra', 'logo' => '/img/partners/x.png', 'type' => 'corporate']
```

Partner logos are flat graphics that never go through the variant pipeline, so
`<x-sections.partners>` uses a plain `<img>` and this shape carries a single
URL rather than an image array. That is deliberate.

---

## Localisation

Every string above marked "localised" arrives **already resolved for the
current locale**. Pages do not receive both languages and choose; the data
layer resolves against `app()->getLocale()`.

Where a translation is missing, the source language is returned along with a
flag the page can use to render the inline fallback note:

```php
['name' => '...', '_fallback_locale' => 'id']   // present only when falling back
```

Missing translations **fall back, they do not 404**.

**Implemented 2026-09-22 (closes the I6 carve-out of 2026-09-17).**
`_fallback_locale` is now emitted and rendered. As the carve-out predicted,
this added a Blade component rather than changing a stabilised section — the
"no Blade template should change at integration time" promise held.

**Who emits it.** `PostData::detail()` and `SchoolData::detail()` — detail
shapes only. Cards and directory items do not carry the key; a rail of
stories is no place for the note. It derives from
`HasTranslations::translationLocale()` on the record's main prose field:
`body` for Post, `lede` for School. A translated title over an untranslated
body is still a fallback, so the prose decides.

**Its three values:**

```php
['_fallback_locale' => null]   // the reader is getting their own locale
['_fallback_locale' => 'id']   // English is missing; Indonesian is showing
                               // null ALSO when the field is empty in every
                               // locale — that is a blank section, not a
                               // fallback, and must not claim otherwise
```

**Who renders it.** `<x-translation-note :from="$x['_fallback_locale'] ?? null" />`,
which reads the note text from `lang/*.json` under `translation.fallback.<locale>`
so the note is itself translated, per §7.

**The panel must read the same method.** Spec §7 also wants per-locale
completeness badges on Filament list views. Those badges must call
`translationLocale()` rather than checking `filled()` on the raw JSON — two
definitions of "translated" between the panel and the public site is a bug
report waiting to be filed.

**Known limitation, and it belongs to the data layer.** `translationLocale()`
resolves *requested locale → default locale*, and the default is `id`. So
en→id fallback works and id→en cannot: a record written in English only
returns `null` from `trans()` on `/id` and renders a **blank** section with no
note. That breaks §7's "never renders blank" promise for English-first
records. Nothing produces such a record today — every seeder and both
fixtures are Indonesian-first — but the Filament Post resource makes one
enterable by hand. Fixing it means teaching `translationLocale()` to try the
other supported locale before giving up, which is
`app/Models/Concerns/HasTranslations.php` and therefore the data layer's call,
not the pages'.

---

## Subject identity

*Decided 2026-09-22. Spec §9: "Minor subject records have no surname field at
all. The schema makes the rule unbreakable rather than merely documented. If
it can't be entered, it can't leak."*

Until today that rule lived in `Post::saving` and `MediaAsset::saving`, which
Eloquent enforces and a raw or bulk `UPDATE` walks straight past. The backend
review of 2026-09-21 called it a P0 launch blocker.

**The shape:**

- **Minor-capable tables carry no family-name column at all.** `media_assets`
  never wrote one, so its column is simply gone.
- **Adult family names live in one separate table,** `subject_surnames`, whose
  rows can only reference a post whose `subject_is_minor` is false. The link is
  a composite foreign key, not a trigger and not a model event, so a raw insert
  and a raw update both fail at the database.
- **`Post::subjectName()` is unchanged** — same signature, same output string.
  Every caller (`Post::toCardArray()`, `Post::toDetailArray()`, `School::people()`)
  reads it and none of them needed editing. If a caller ever has to change, the
  extraction leaked and the leak is the bug.

**Why a separate table rather than a CHECK constraint.** The review offered
both. A CHECK is the smaller diff and it was rejected for one reason: **there
is no host.** `docs/deployment.md` still records no hosting decision, so nobody
can run the probe that says whether production MySQL enforces CHECK or parses
and silently ignores it — MySQL below 8.0.16 does the latter. A constraint that
might be decorative is worse than no constraint, because it reads like
enforcement to the next person who audits this.

Separate storage depends on no host fact, and it is closer to what §9 literally
says.

**What the local suite does and does not prove.** Tests run on **SQLite 3.40.0**
(`DB::connection()->getDriverName()`, checked 2026-09-22). SQLite enforces both
CHECK and composite foreign keys, and `config/database.php` sets
`foreign_key_constraints` to true, so the raw-write tests are meaningful here.
They still prove nothing about the production engine. **A green suite is not
host evidence.**

**What would revisit this:** a host is chosen and reports MySQL ≥ 8.0.16. A
CHECK constraint then becomes a defensible belt alongside these braces — not
instead of them. Dropping the separate table because a CHECK exists would put
the invariant back on one engine's version.

---

## Rules the contract enforces

These are not style preferences. Each has a specific failure it prevents.

1. **No numeric funding field, anywhere, in any entity.** Not on the model, not
   in the array, not in the database.
2. **Minors have no surname field.** Enforced in the schema, not by editorial
   discipline — see **Subject identity** above for how, and for what the local
   SQLite suite does not prove about the production engine.
3. **`jpeg` is always present in `sources`.** `<x-picture>` throws otherwise,
   loudly, by design.
4. **Indonesian strings are the primary case.** They run 15–20% longer than
   English; nothing downstream may be sized to the English length.
5. **Strings arrive formatted.** Pages do not format currency, dates, or
   numbers — the data layer does, so the two workstreams cannot disagree about
   how a rupiah figure looks.
