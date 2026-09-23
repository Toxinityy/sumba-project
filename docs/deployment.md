# Deployment

## Versions

Captured after scaffolding the Laravel application (Task 1) and running `npm install`.

- **Laravel framework:** 13.32.0 (`php artisan --version` → `Laravel Framework 13.32.0`; `composer show laravel/framework` → `v13.32.0`)
- **PHP:** 8.3.10 (`php -v` → `PHP 8.3.10 (cli) (built: Jul 30 2024 15:15:59) (ZTS Visual C++ 2019 x64)`)
- **Tailwind CSS:** `^4.0.0` in `package.json`, resolved to `4.3.3` after `npm install`. There is **no** `tailwind.config.js` — this is Tailwind v4, configured via `@import "tailwindcss"` and `@theme` in CSS, not `tailwind.config.js` + `@tailwind` directives.
- **Pest:** not installed by the default `composer create-project laravel/laravel` scaffold (it ships PHPUnit 12.5.12 and PHPUnit-style example tests instead). Added explicitly via `composer require pestphp/pest pestphp/pest-plugin-laravel --dev` to satisfy this plan's requirement that `php artisan test` runs Pest. Installed version: **pestphp/pest 4.7.8**. `tests/Pest.php` and the Pest-style example tests were added by hand (`vendor/bin/pest --init` hung waiting on an interactive prompt in this non-TTY environment, so the standard manual layout was used instead: `uses(TestCase::class)->in('Feature')` in `tests/Pest.php`, with `tests/Feature/ExampleTest.php` and `tests/Unit/ExampleTest.php` rewritten to Pest's `test()`/`expect()` syntax).
- **Node:** 24.19.0
- **npm:** 12.0.2

## Host image capabilities

**LOCAL** — output of `php artisan images:capabilities` on the development machine (PHP 8.3.10, GD enabled, Imagick not installed). This says nothing about the production cPanel host; Task 10 runs the same command on staging and records that separately.

```
+--------+-----------+
| Format | Encodable |
+--------+-----------+
| avif | yes |
| webp | yes |
| jpeg | yes |
+--------+-----------+
Chain: avif -> webp -> jpeg
```

## Deploying to cPanel

No shell access, so `composer install` runs locally and `vendor/` ships with
the upload. Hosting has not been chosen yet, so `USERNAME` (the cPanel
account username) and `example.org` (the site's domain) below are
placeholders, not real values — replace every occurrence with the actual
account username and domain once hosting is provisioned.

**Verified 2026-09-17** — both local-side commands below run clean on this
machine: `composer install --no-dev --optimize-autoloader` (removes
pestphp/pest, fakerphp/faker, mockery, and other dev-only packages;
generates the optimized autoloader with no errors) and `npm run build`
(builds `public/build/` — CSS, the font-face CSS, and font files — in under
a second, no errors). `--no-dev` does **not** break anything the deploy
procedure depends on: `tests/` is already excluded from the upload (step 5
below), so the app never needs Pest on the host. After verifying,
dev dependencies were restored with a plain `composer install` — running
`--no-dev` locally leaves the working copy without Pest until that's done,
so restore before running `php artisan test` again.

1. In cPanel's **MultiPHP Manager**, set the domain to a PHP version this
   app supports (8.3+) *before* uploading anything — picking this after the
   fact, once other steps depend on it, fails in confusing ways (wrong
   extensions enabled, wrong CLI PHP binary for the cron entry in step 9).
2. Locally: `composer install --no-dev --optimize-autoloader`
3. Locally: `npm run build`
4. Locally: `php artisan key:generate --show` — this prints an `APP_KEY`
   value without writing to the local `.env`. Copy it; it's needed in step 6.
5. Upload everything except `node_modules/`, `tests/`, `.git/`, to
   `/home/USERNAME/example.org/` (or wherever the domain's document root
   parent is) and point the domain's document root at its `public/`
   subdirectory.
6. Create `.env` on the host — cPanel File Manager's "New File", or upload
   over SFTP; there is no `.env.example` copy step available without a
   shell, so create it directly with the needed keys:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_KEY=` the value copied in step 4
   - `APP_LOCALE=id` — without it, `config:cache` in step 9 freezes the
     locale config into the cache file at request time, and a page served
     before any locale-prefixed route middleware runs would fall back to
     `config/app.php`'s own default rather than `config/locales.php`'s.
     Matching `.env.example` keeps the two in agreement.
   - the database credentials for this host
   - leave `QUEUE_CONNECTION`, `SESSION_DRIVER`, `CACHE_STORE` as `database`
     (matching `.env.example`)
   - `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`,
     `MAIL_FROM_ADDRESS` — the host's real SMTP credentials.
   - `CONTACT_TO` — the real inbox the "Partner with us" form delivers to.
     **If any of these six keys are left out, the contact form does not fail
     loudly — it silently logs every enquiry to `storage/logs/laravel.log`
     instead of sending it, and the contact page keeps printing the
     placeholder address `halo@contoh.org` ("contoh" is Indonesian for
     "example") as the ministry's own.** A boot-time check in
     `App\Providers\AppServiceProvider` refuses to serve the site at all in
     production while either of those two conditions holds, specifically so
     this is caught at deploy time rather than discovered months later when
     a donor's enquiry never arrived.
7. In cPanel File Manager, set permissions so the web server can write to
   `storage/` and `bootstrap/cache/`: select each directory, "Permissions",
   apply recursively, and set `775` (or `755` if the host's PHP runs as the
   file owner — try `755` first, only widen to `775` if the app throws a
   "permission denied" writing to `storage/logs` or a compiled view).
   Laravel writes logs, compiled views, and cached config here on every
   request; if the web server can't write to them the app throws instead of
   rendering.
8. **Back up the database before migrating.** Either export it from
   phpMyAdmin (Export tab → SQL format) or use cPanel's "Backup" tool for a
   full/partial backup. Download the resulting file and keep it outside the
   web root, e.g. alongside the dated release directories described in
   Rollback below — this is the dump that rollback restores from.
9. Run the artisan commands below. Some cPanel plans expose a Terminal app
   under "Advanced" — if it's there, this is the easy path: open it and run
   each command directly. If there's no Terminal, cPanel still runs
   arbitrary commands through **Cron Jobs**, so use that as a one-off:
   1. Under cPanel's "Cron Jobs", add a job with a far-future or unlikely
      schedule (anything not `* * * * *`) whose command is the artisan call,
      redirected to a log file, e.g.:
      `/usr/local/bin/php /home/USERNAME/example.org/artisan migrate --force >> /home/USERNAME/deploy.log 2>&1`
   2. Trigger it once — either wait for its scheduled minute, or (if the
      plan allows it) use "Run Now" from the Cron Jobs list.
   3. Confirm it ran by checking `/home/USERNAME/deploy.log` for the
      command's expected output (or absence of errors).
   4. **Delete the cron entry** immediately after confirming — it's a
      one-off, not a recurring job.
   5. Repeat for each command in order, since they depend on `.env` (from
      step 6) already being in place and on each other running in order:
      - `php artisan migrate --force`
      - `php artisan config:cache && php artisan route:cache && php artisan view:cache`
        (must run *after* `.env` exists and *after* migrating — caching
        config/routes/views before `.env` is in place bakes in local
        defaults instead of the production values)
      - `php artisan images:capabilities` — **record the result in this
        file**
10. Add the recurring cron entry from `deploy/cron.txt` (replace its
    `USERNAME` and PHP path placeholders too) under cPanel's "Cron Jobs",
    scheduled every minute, so queued jobs actually get processed.
11. Point Cloudflare (free tier) at `example.org`.
12. **Restrict the origin to Cloudflare's IP ranges.** The app trusts `*` as
    a proxy (`bootstrap/app.php`) so `CF-Connecting-IP`/`X-Forwarded-For` are
    read as the real visitor IP — correct only if every request Laravel sees
    actually came through Cloudflare. If the origin is directly reachable,
    anyone can forge that header and spoof any IP, defeating the contact
    form's rate limit entirely. Most shared-hosting plans have no firewall
    control for this; where the host offers one (cPanel's IP Blocker/ModSecurity,
    or an .htaccess allow-list keyed on Cloudflare's published ranges at
    https://www.cloudflare.com/ips/), apply it. Where it isn't offered, this
    is a known gap — flag it to whoever chooses hosting.

### Rollback

Keep the previous upload as a dated directory (e.g.
`/home/USERNAME/releases/2026-09-17/`) on the host and repoint the document
root. Database rollbacks restore the dump taken in step 8, before running
migrations. Shared hosting has a limited disk quota, so keep only the
current release plus one or two previous dated directories and delete older
ones once a release has proven stable — there is no need to retain a long
history on the host itself (the git history already has it).

## Performance baseline

### LOCAL FLOOR — not a staging baseline (re-run 2026-09-17)

No hosting has been chosen yet, so there is no staging URL to measure.
The numbers below come from `php artisan serve` on the development machine
and Lighthouse run against `http://127.0.0.1:8123/id/sekolah`:

```
npx lighthouse http://127.0.0.1:8123/id/sekolah --form-factor=mobile \
  --throttling-method=simulate --output=json \
  --output-path=./lighthouse-local.json --chrome-flags="--headless"
```

**LCP, TTFB, and the overall Performance score are still deliberately
omitted**, for the same reason as before: localhost has no network latency
and no shared-hosting CPU contention, so those numbers would be
meaninglessly good and would mislead anyone who later compared a real
staging measurement against them. They remain **pending staging** (the
deferred checklist below).

This re-run replaces the figures captured against the pre-layout
placeholder views (commit `95df185` and earlier — before `x-layouts.site`
existed). Both accessibility defects that entry listed are fixed and
verified by `LayoutTest`, so the score they were dragging down is gone:

- **Accessibility score:** 1.0 (100/100) — up from 0.86. `document-title`
  and `landmark-one-main` are both closed: the layout emits a real
  `<title>` and exactly one `<main>` landmark (`LayoutTest::'emits exactly
  one main landmark...'`), and now also a skip link (`LayoutTest::'offers
  a skip link...'`, Finding 3). No accessibility audit failures at all on
  this page.
- **Best Practices score:** 0.77. `is-on-https` fails only because this is
  plain-HTTP localhost, not a real defect. `errors-in-console` fails on
  404s for `[::1]:5173` font requests — `php artisan serve` without a
  running Vite dev server, an artifact of this local setup, not something
  that exists in the built/production asset pipeline. Worth a second look
  once there's a real staging URL, not urgent before then.
- **SEO score:** 0.92. `meta-description` fails — the page has no meta
  description. Real, host-independent, not caused by hosting.
- **Cumulative Layout Shift:** 0.
- **Total transferred bytes:** 568,954 — this page (Schools index) now
  renders real content and images, unlike the near-empty placeholder the
  previous entry measured, so this number is not comparable to the old
  1,842-byte figure.

**Why this is still a floor, not a baseline:** localhost has no network
latency and no CPU contention, so LCP/TTFB/Performance stay excluded for
the reason given above. Byte totals and CLS are now measured against a
real page rather than a placeholder, but are still local-machine numbers —
treat them as "nothing is broken today," not as what "good" looks like on
the real host.

## Deferred staging checklist

**BLOCKS LAUNCH.** No hosting has been selected yet, so none of this can
run today. It must all be done, in order, once a host exists — before the
site is considered launch-ready.

- [ ] Replace homepage plates and deep-page placeholder images with consent-cleared, optimized photographs. Verify both locales at mobile and desktop widths.
- [ ] Verify school facts and status copy against current records. Edit existing school records that still use numeric partner targets; updating seed text does not change an already seeded database.
- [ ] Obtain the legal registration details and verified giving instructions from the ministry. Keep the giving page enquiry-led until those details can be published accurately.
- [ ] Verify statistics, dated evidence and partner permissions before publishing the deferred pages or enabling their production routes.
- [ ] **Record the production MySQL version**, and say which engine it is
      (MySQL or MariaDB):
  ```bash
  mysql -e "SELECT VERSION();"   # or via cPanel's phpMyAdmin
  ```
  This decides one open schema question. Minor surnames are kept out
  structurally by a separate `subject_surnames` table with a composite
  foreign key (see **Subject identity** in `docs/data-contract.md`), chosen
  because no host existed to prove a CHECK constraint would be enforced —
  **MySQL below 8.0.16 parses CHECK and silently ignores it.** At ≥ 8.0.16 a
  CHECK becomes a defensible second layer. It never replaces the table: that
  would put a safeguarding invariant back on one engine's version number.
  Also confirm the composite foreign key survived the import — a
  `mysqldump` restored with `FOREIGN_KEY_CHECKS=0` and never re-enabled
  enforces nothing, and looks identical until someone writes a bad row.

- [ ] Deploy to a staging subdomain on the real host, following the
      procedure in "Deploying to cPanel" above (same host as production —
      staging on a different host tells you nothing useful).
- [ ] Run `php artisan images:capabilities` **on the host** and record the
      result under `## Host image capabilities` above as **STAGING**,
      dated. The entry currently there is **LOCAL only** (development
      machine) and does not describe the production host.
- [ ] Verify both locales serve on the host:
  ```bash
  curl -sI https://staging.example.org/ | head -1           # expect 302
  curl -sI https://staging.example.org/id/sekolah | head -1 # expect 200
  curl -sI https://staging.example.org/en/schools | head -1 # expect 200
  ```
- [ ] Run throttled-mobile Lighthouse against the staging URL and record
      the full numbers **including LCP and CLS** (the LOCAL FLOOR above
      deliberately excluded LCP/TTFB/Performance — this is where they get
      recorded for real, under `## Performance baseline`, dated):
  ```bash
  npx lighthouse https://staging.example.org/id \
    --form-factor=mobile --throttling-method=simulate \
    --output=json --output-path=./lighthouse-staging.json
  ```
- [ ] Confirm whether the host has Imagick, GD, or both, and run
      `php artisan images:capabilities` there to confirm the report matches.
      `ImageCapabilities::driver()` selects Imagick when it's loaded and GD
      otherwise, and `VariantGenerator` now builds its `ImageManager` from
      that selection rather than a hardcoded driver — so a host with only
      Imagick, only GD, or both installed all work, and the capability
      report is always computed against the library that will actually do
      the encoding. The case that matters most on shared hosting is the
      **mixed** one this spec names (§3: "GD available, Imagick uncertain")
      — GD without AVIF but Imagick with AVIF, or vice versa — where a
      report keyed to the wrong library would say "avif: yes" and then hand
      the encode to a driver that can't produce one.
