# Deployment

## Versions

Captured after scaffolding the Laravel application (Task 1) and running `npm install`.

- **Laravel framework:** 13.32.0 (`php artisan --version` → `Laravel Framework 13.32.0`; `composer show laravel/framework` → `v13.32.0`)
- **PHP:** 8.3.10 (`php -v` → `PHP 8.3.10 (cli) (built: Jul 30 2024 15:15:59) (ZTS Visual C++ 2019 x64)`)
- **Tailwind CSS:** `^4.0.0` in `package.json`, resolved to `4.3.3` after `npm install`. There is **no** `tailwind.config.js` — this is Tailwind v4, configured via `@import "tailwindcss"` and `@theme` in CSS, not `tailwind.config.js` + `@tailwind` directives.
- **Pest:** not installed by the default `composer create-project laravel/laravel` scaffold (it ships PHPUnit 12.5.12 and PHPUnit-style example tests instead). Added explicitly via `composer require pestphp/pest pestphp/pest-plugin-laravel --dev` to satisfy this plan's requirement that `php artisan test` runs Pest. Installed version: **pestphp/pest 4.7.8**. `tests/Pest.php` and the Pest-style example tests were added by hand (`vendor/bin/pest --init` hung waiting on an interactive prompt in this non-TTY environment, so the standard manual layout was used instead: `uses(TestCase::class)->in('Feature')` in `tests/Pest.php`, with `tests/Feature/ExampleTest.php` and `tests/Unit/ExampleTest.php` rewritten to Pest's `test()`/`expect()` syntax).
- **Node:** 24.19.0
- **npm:** 12.0.2
