# Hope for Sumba

Bilingual (Indonesian/English) Laravel website for an education ministry
working in Sumba, Indonesia.

## Authoritative documents

- Design spec: `docs/superpowers/specs/2026-09-16-hope-for-sumba-design.md`
- Implementation plan: `docs/superpowers/plans/2026-09-16-foundations-and-design-system.md`

Both are authoritative. When code and either document disagree, treat the
document as correct and flag the conflict rather than guessing.

## Stack

Laravel 13 + Filament + Blade + Tailwind CSS 4 (CSS-first `@theme`, no
`tailwind.config.js`) + Alpine.js. Server-rendered, deployed to cPanel shared
hosting — no Node build step in production beyond compiling assets ahead of
deploy.

## Tests

Pest. Run with `php artisan test`.

## Two rules that are easy to break by accident

1. **No numeric funding display, anywhere.** No fundraising goals, no
   amounts raised, no progress bars. This is a ministry site, not a
   donation tracker.
2. **Never size a button or card to fit English text.** Indonesian text
   runs 15-20% longer than the English equivalent. Layouts must accommodate
   the longer language by default.
