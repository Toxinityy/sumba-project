# Hope for Sumba

Bilingual (Indonesian/English) Laravel website for an education ministry
working in Sumba, Indonesia.

## Authoritative documents

- Design spec: `docs/superpowers/specs/2026-09-16-hope-for-sumba-design.md`
- Implementation plan: `docs/superpowers/plans/2026-09-16-foundations-and-design-system.md`

Both are authoritative. When code and either document disagree, treat the
document as correct and flag the conflict rather than guessing.

## Deployment

Server-rendered, deployed to cPanel shared hosting: no shell access and no
Node in production, so assets are compiled locally before upload.

Tailwind 4 is configured CSS-first (`@theme` in `resources/css/app.css`).
There is no `tailwind.config.js` — don't create one.

## Two rules that are easy to break by accident

1. **No numeric funding display, anywhere.** No fundraising goals, no
   amounts raised, no progress bars. This is a ministry site, not a
   donation tracker.
2. **Never size a button or card to fit English text.** Indonesian text
   runs 15-20% longer than the English equivalent. Layouts must accommodate
   the longer language by default.
