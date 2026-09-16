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
the upload. Hosting has not been chosen yet, so `USERNAME` and `example.org`
below are placeholders, not real values — replace them with the actual
account username and domain once hosting is provisioned.

1. Locally: `composer install --no-dev --optimize-autoloader`
2. Locally: `npm run build`
3. Upload everything except `node_modules/`, `tests/`, `.git/`
4. Point the domain's document root at `public/`
5. Set `.env` on the host (`APP_ENV=production`, `APP_DEBUG=false`, database credentials)
6. Add the cron entry from `deploy/cron.txt` (replace its `USERNAME` and PHP path placeholders too)
7. On the host, via cPanel's Terminal or a scheduled one-off cron:
   - `php artisan migrate --force`
   - `php artisan config:cache && php artisan route:cache && php artisan view:cache`
   - `php artisan images:capabilities` — **record the result in this file**
8. Point Cloudflare (free tier) at the domain

### Rollback

Keep the previous upload as a dated directory (e.g. `/home/USERNAME/releases/2026-09-17/`)
on the host and repoint the document root. Database rollbacks use the dated
dump taken before step 7. Shared hosting has a limited disk quota, so keep
only the current release plus one or two previous dated directories and
delete older ones once a release has proven stable — there is no need to
retain a long history on the host itself (the git history already has it).
