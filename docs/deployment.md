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
   - the database credentials for this host
   - leave `QUEUE_CONNECTION`, `SESSION_DRIVER`, `CACHE_STORE` as `database`
     (matching `.env.example`)
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

### Rollback

Keep the previous upload as a dated directory (e.g.
`/home/USERNAME/releases/2026-09-17/`) on the host and repoint the document
root. Database rollbacks restore the dump taken in step 8, before running
migrations. Shared hosting has a limited disk quota, so keep only the
current release plus one or two previous dated directories and delete older
ones once a release has proven stable — there is no need to retain a long
history on the host itself (the git history already has it).
