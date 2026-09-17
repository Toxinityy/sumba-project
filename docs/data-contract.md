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
    'name'     => 'TK Harapan Karuni',
    'location' => 'Karuni, Sumba Barat Daya',
    'need'     => 'Ruang baca baru untuk 60 anak.',   // ONE sentence, localised
    'status'   => 'Butuh 4 mitra lagi',              // short qualitative string
    'image'    => [ /* image shape */ ],
]
```

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

---

## Rules the contract enforces

These are not style preferences. Each has a specific failure it prevents.

1. **No numeric funding field, anywhere, in any entity.** Not on the model, not
   in the array, not in the database.
2. **Minors have no surname field.** Enforced in the schema, not by editorial
   discipline.
3. **`jpeg` is always present in `sources`.** `<x-picture>` throws otherwise,
   loudly, by design.
4. **Indonesian strings are the primary case.** They run 15–20% longer than
   English; nothing downstream may be sized to the English length.
5. **Strings arrive formatted.** Pages do not format currency, dates, or
   numbers — the data layer does, so the two workstreams cannot disagree about
   how a rupiah figure looks.
