# Hope for Sumba

Bilingual (Indonesian/English) Laravel website for an education ministry
working in Sumba, Indonesia.

## Stack

Laravel 13, Filament, Blade, Tailwind CSS 4 (CSS-first `@theme`, no
`tailwind.config.js`), Alpine.js. Server-rendered and deployed to cPanel
shared hosting — there is no Node build step in production beyond
compiling assets ahead of deploy.

## Getting started

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

## Tests

The suite uses Pest:

```bash
php artisan test
```

## Authoritative documents

Where code and these documents disagree, the document is correct — flag
the conflict rather than guessing:

- Design spec: `docs/superpowers/specs/2026-09-16-hope-for-sumba-design.md`
- Implementation plan: `docs/superpowers/plans/2026-09-16-foundations-and-design-system.md`
- Data contract: `docs/data-contract.md`

## Security

If you discover a security vulnerability, please report it privately to
the project maintainers rather than opening a public issue.
