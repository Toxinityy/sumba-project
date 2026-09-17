# Pages workstream — fix wave report

Full suite: **190 tests, 511 assertions, 0 failures** (`php artisan test`).
Started from 182/454 green; every new/changed test above is additive or a
correctness fix to an existing counterfeit test, nothing was deleted to make
the count go down.

## C1 — contact form misdelivery + placeholder address (Critical)

Two fixes, per the brief:

1. **`docs/deployment.md` step 6** now lists `MAIL_MAILER`, `MAIL_HOST`,
   `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` and
   `CONTACT_TO`, with an explicit line: leaving any of them out means the
   form silently logs instead of sending, and the page keeps printing
   `halo@contoh.org` as the ministry's own address.
2. **`App\Providers\AppServiceProvider::boot()`** now throws a
   `RuntimeException` when `app()->environment('production')` and either
   `config('mail.contact_to')` is still `halo@contoh.org` or
   `config('mail.default')` is still `log` — refuses to serve at all rather
   than fail silently. Test: `tests/Feature/MailAndProxyGuardsTest.php`
   (production + placeholder throws, production + log throws, production +
   real config boots clean, non-production + placeholder still boots so
   local/staging dev isn't blocked).

## I1 — safeguarding guard on Children's Homes (Important)

`tests/Feature/Pages/ChildrensHomesPageTest.php` gained
`it('never names a resident child or states why any child is in care')`,
which renders both locales and asserts:
- none of `Rambu, Umbu, Tamu, Kahi, Ledu, Wangi, Deni, Yuni` appear outside
  the two house-parent full names (`Bapak & Ibu Umbu Deta`, `Bapak & Ibu
  Ndara Kaka` — stripped first, including their `&amp;`-escaped HTML form,
  before the scan)
- none of `orphan, orphaned, abandoned, yatim, piatu, ditinggalkan,
  ditelantarkan, parents died, orang tuanya meninggal` appear anywhere.

Failure messages state the rule and point at `lang/id.json`/`lang/en.json`
as the likely diff, per the brief.

## I2 — rate limiter self-DoS behind Cloudflare (Important)

`bootstrap/app.php` now calls `$middleware->trustProxies(at: '*')`, with a
comment stating this is only safe because the origin must not be directly
reachable. Added deploy runbook step 12 (restrict the origin to Cloudflare's
published IP ranges via cPanel's IP Blocker/ModSecurity or `.htaccess`, where
the host offers it — flagged as a known gap where it doesn't).

Test: `tests/Feature/MailAndProxyGuardsTest.php`'s
`'reads the client IP from the X-Forwarded-For header, not the proxy socket'`
— registers a throwaway route through the real HTTP kernel (so the global
`TrustProxies` middleware actually runs), sends `REMOTE_ADDR=10.0.0.1` +
`X-Forwarded-For: 203.0.113.7`, asserts `request()->ip()` is the forwarded
address, not the socket one.

## I3 — form fields fail WCAG 1.3.5 (Important)

`resources/views/components/sections/form.blade.php`: added
`$field['autocomplete'] ?? null` and `$field['required'] ?? false` support
on both `<input>` and `<textarea>` — `required` also emits
`aria-required="true"`. `resources/views/pages/contact.blade.php`'s three
fields now carry `autocomplete` (`name`, `email`, `organization`) and
`required` on name/email/message. Covered by the existing
`FormSectionTest.php` suite still passing plus the fixed touch-target test
below (which now exercises the same markup path).

## I4 — input boundaries fail WCAG 1.4.11 (Important)

Added `--border-strong` to `resources/css/tokens.css` (light + the two dark
variants) and exposed it as `--color-line-strong` in `resources/css/app.css`'s
`@theme` block (class: `border-line-strong`). `--accent` was **not** touched.
Form inputs (`form.blade.php`) switched from `border-line` to
`border-line-strong`; every purely decorative use of `border-line`
(cards, nav, dividers) is untouched.

**Measured contrast** (`tests/Unit/TokenContrastTest.php`, new
`uiBoundaryPairs` dataset, asserted ≥3.0):
- light `#6E7062` on surface `#F7F8F3` → **4.73:1**
- dark `#8A7A68` on surface `#211A15` → **4.14:1**

Both comfortably clear the 3:1 SC 1.4.11 floor with margin, without
darkening `--accent` (still 4.68:1, untouched).

Also fixed the related MINOR item in the same component:
`focus-visible:ring-offset-2` had no offset colour, painting a white halo
around focused inputs in dark mode — added `focus-visible:ring-offset-surface`.

## I5 — Home/About were lang-key templates, not fixture consumers (Important)

Added `App\ViewModels\HomeData::get()` and `App\ViewModels\AboutData::get()`,
following `SchoolData`'s pattern. `resources/views/pages/homes.blade.php` and
`about.blade.php` were reduced from ~40/~15 lines of `__()` +
`PlaceholderImage::make()` calls to consuming `$home`/`$about` arrays.
`routes/web.php` swapped both pages from `Route::view()` to `Route::get()` +
`view()` (needed so the fixture can resolve `app()->getLocale()`, which
`Route::view()`'s `$data` argument can't see — same reasoning already used
for every other locale-sensitive route in the file).

**`docs/agent-a-pages-report.md` corrected** — it claimed the opposite of
what was true (that this was fine, not a speculative abstraction to skip);
the correction is inline in the file, not a silent rewrite of history.

**`docs/data-contract.md` changed — flagged prominently, per the brief, since
a parallel agent is building models against this document right now:**
added a `facts` shape (and confirmed `care_model`/`people`/qualitative
`status`) to § Home, dated and marked as an I5 update, matching exactly what
`HomeData::get()` now produces:
```php
'facts' => [
    ['key' => 'Rumah anak',              'value' => '2'],
    ['key' => 'Anak yang diasuh',        'value' => '18'],
    ['key' => 'Keluarga pengasuh tetap', 'value' => '2'],
    ['key' => 'Biaya bagi keluarga',     'value' => 'Gratis'],
],
```
**This is a live contract change during a parallel build — read the diff in
`docs/data-contract.md` § Home before planning App\Models\Home's schema.**

## I6 — §7 fallback note has no implementation (Important)

Not implemented, per the brief (would require editing stabilised section
components, out of scope here). `docs/data-contract.md`'s Localisation
section gained an explicit carve-out: `_fallback_locale` is contract-only
until a real model has an untranslated field to fall back from; rendering
spec §7's note will require a component change at integration, and the
"no template change at integration" promise does not cover it.

## MINOR items

- **Counterfeit tests fixed, not deleted** (all three had a real, findable
  fix):
  - `FormSectionTest`'s touch-target test now asserts `min-h-11` on the
    `<input>`/`<textarea>` tags specifically (regex-extracted), not just
    `toContain('min-h-11')` anywhere in the HTML — `<x-button>`'s hardcoded
    `min-h-11` used to make this pass even with the field's own class deleted.
  - `AboutPageTest`'s funding-figure test and `GetInvolvedPageTest`'s
    progress-bar test were pointed at pages with no `<progress>`/
    `role="progressbar"` code path at all. About's test stayed as a
    documented regression guard (About genuinely has zero funding-shaped
    markup today; the honest limitation is noted inline). Get Involved's was
    replaced with an assertion on the actual qualitative status string the
    page renders (`give.how.status`), asserting it contains no digit.
  - `GetInvolvedPageTest`'s `'shows all six tiers'` test now asserts all six
    tier titles, not one.
- **Honeypot** (`contact.blade.php`): `class="hidden"` → the `hidden`
  attribute, which needs no stylesheet.
- **"Partner with us" on Contact**: the Next step section (which pointed
  the CTA back at the Contact page itself) was dropped from
  `contact.blade.php` — the whole page already is "partner with us", so
  there was no useful second destination to offer.
- **`focus-visible:ring-offset-2` with no offset colour**: fixed as part of
  I4 above.
- **`ContactPageTest`'s global `enquiryMails()`**: moved into a
  `namespace Tests\Feature\Pages\ContactPageTest;` block in that file, so a
  second file declaring the same function name can no longer fatally
  collide with it.
- **`about.quote.*` dead lang keys**: removed from both `lang/id.json` and
  `lang/en.json`.

## Commits

See `git log` on this branch for the exact SHAs staged by file group
(mail/proxy guards + deploy runbook; safeguarding test; WCAG fixes + tokens;
HomeData/AboutData + contract update; minor fixes + report).

## Not done / could not do

- Nothing in scope was skipped. `app/Models/**`, `database/**`,
  `composer.json/.lock`, and the spec file were not touched, per the git
  hazard warning — none of this wave's fixes needed them.
- I4's exact hex values are a judgement call within the ≥3:1 floor, not a
  spec-mandated number — flagged here in case the design system wants a
  specific value instead.
