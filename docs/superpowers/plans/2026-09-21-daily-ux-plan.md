# 2026-09-21 Visitor Journey Hardening Implementation Plan

> **For agentic workers:** Use superpowers:executing-plans to implement this plan in order, inline. Steps use checkbox (`- [ ]`) syntax for tracking. Do not use subagents in the shared checkout.

**Goal:** Keep the bilingual visitor journey usable when editorial content is missing or an enquiry fails, then verify it on narrow screens and by keyboard.

**Architecture:** Retain the existing Laravel routes, Blade sections, and contact endpoint. Handle absent published content at the homepage boundary, add localized validation feedback in the existing form, and change navigation only where the browser review shows a real problem.

**Tech Stack:** Laravel, Blade, Tailwind 4 CSS-first, Pest, Vite; assets are built locally for cPanel.

**Spec:** `docs/superpowers/specs/2026-09-16-hope-for-sumba-design.md`, `docs/superpowers/plans/2026-09-16-foundations-and-design-system.md`, and `AGENTS.md`. The completed high-priority UX work is recorded in `docs/superpowers/plans/2026-09-20-high-priority-ux.md`.

## Global constraints

- No numeric funding display, including goals, amounts raised, or progress bars.
- Allow Indonesian labels and body copy to run longer than English at every viewport.
- Do not publish fixture evidence, payment details, partner endorsements, or unapproved photography.
- Do not treat a passing local test suite as proof of media safety or staging readiness.
- The shared checkout contains uncommitted frontend and backend changes. Inspect the diff before editing; stage explicit paths only if committing.

## Today's order

### 1. Establish the integrated baseline (first 30 minutes)

**Files:** Read `docs/backend-review-report.md`, `docs/superpowers/plans/2026-09-20-high-priority-ux.md`, and the current diff. No code change.

- [x] Record `git status --short` and the current HEAD; preserve the existing uncommitted work.
- [x] Run `php artisan test --compact`. Baseline: 336 tests and 1,334 assertions passed.
- [x] Confirm the four deferred pages still return 404 in production mode, the enquiry flow remains available, and no monetary figures render (covered by the passing integrated suite).

**Done when:** Today's baseline is dated and any failure is attributed to a specific change, rather than yesterday's already-resolved safeguarding failures.

### 2. Make the homepage survive missing or withdrawn content (morning priority)

**Files:** `routes/web.php`, `resources/views/pages/home.blade.php`, `tests/Feature/Pages/HomePageTest.php`; inspect `app/ViewModels/{PostData,SchoolData}.php` and `resources/views/components/sections/directory.blade.php` first.

**Interface:** The home route may pass `null` for `voice` and `featuredSchool`. The page omits the corresponding quote or lead card and retains a usable schools-directory link. It must not substitute invented content.

- [x] Add one feature test with an empty database for `/id` and `/en`, plus a case where the named quote or featured school has been withdrawn. Require HTTP 200, a working enquiry link, and no stale quote or school card.
- [x] Run that test to reproduce the current 500 caused by `PostData::find('ibu-maria-bulu')['quote']` in `routes/web.php`.
- [x] Use a null-safe lookup in the route and guard the quote/featured-school markup in the page. Keep the existing directory and stories sections capable of rendering empty arrays.
- [x] Rerun the focused test and existing home, landing, and production-content tests (5 and 33 tests passed respectively).

**Done when:** Both locales render useful homepages before seeding and after an editor withdraws the featured records.

### 3. Repair enquiry validation recovery (early afternoon)

**Files:** `routes/web.php`, `resources/views/components/sections/form.blade.php`, `resources/views/pages/home.blade.php`, `lang/{id,en}.json`, `tests/Feature/Pages/ContactPageTest.php`.

**Interface:** A failed POST still redirects to the locale's contact anchor with entered values. Field errors use the active locale and identify the affected control; keyboard focus lands at the first error or its summary. Transport and rate-limit recovery from yesterday stays intact.

- [x] Add a failing test for invalid Indonesian and English submissions: localized required/email/length feedback, retained safe input, an error-to-control association, and the contact-anchor redirect.
- [x] Supply the few contact-specific validation strings through the existing translation files and `Validator::make` message/attribute arguments; do not add a general localization package.
- [x] In the form, make the first invalid field focusable on the returned page and retain `aria-invalid`/`aria-describedby`. Verify the form-level transport error remains announced and its direct-email fallback remains usable.
- [x] Run the contact tests, then check one invalid submission in each locale using keyboard and screen-reader-friendly focus order.

**Done when:** A visitor can correct an invalid enquiry without hunting for the form or deciphering English validation copy on `/id`.

### 4. Review the narrow-screen journey and apply only observed fixes (late afternoon)

**Files:** Inspect `resources/views/components/{site-nav,language-switcher}.blade.php`, `app/Support/LocalizedUrl.php`, `resources/js/app.js`, and `resources/css/app.css`. Edit only the files needed by observed failures; extend `tests/Feature/LanguageSwitcherTest.php` if navigation behavior changes.

- [x] In a browser, review `/id` and `/en` at 360px, 400px, and desktop width; check 320px and 720px CSS reflow widths as 200% zoom equivalents. Check navigation wrapping, Indonesian CTA fit, hero-to-enquiry path, focus visibility, and form errors. Production-hidden content is checked by the production-mode feature test.
- [x] Check the language switch while the reader is at `#kontak` or `#contact`. URL fragments never reach Laravel, so preserve the equivalent contact fragment with a small client-side enhancement only if the switch currently loses that location. Keep ordinary page-to-page locale routing unchanged.
- [x] If the partner/contact action is obscured in the narrow header, adjust the existing nav layout or link placement without fixed-width labels or a new menu system. The hero action remained clear, so the header was left alone.
- [x] Build with `npm run build` after the JS change; rerun affected and integrated tests, and `git diff --check`.

**Done when:** The Indonesian mobile path to the enquiry works by touch and keyboard, switching language does not strand a visitor, and no horizontal overflow or clipped label appears.

## End-of-day checkpoint and launch gates

- [x] Record test/build results, browser widths checked, remaining defects, and any content needed from the ministry. Do not mark staging checks complete without running them.
- [x] Keep launch blocked on the P0 findings in `docs/backend-review-report.md`: private, consent-gated media ingestion and revocation of direct image URLs; and a schema-level ban on minor surnames. These need separate focused implementation plans and host/MySQL evidence, not a quick UI patch.
- [x] Keep approved photography, the named safeguarding contact and specialist policy review, real mail delivery, and a tested same-host restore on the launch-readiness list. Do not deploy or publish placeholder content from this plan.

## Verification record (completed 2026-09-22, Asia/Jakarta)

- Baseline before this pass: `php artisan test --compact` passed 336 tests and 1,334 assertions. Final integrated run after the last validation change: **341 tests, 1,371 assertions passed**.
- `npm run build`, `php -l routes/web.php`, `node --check resources/js/app.js`, bilingual JSON parsing, and `git diff --check` passed. Pint's file-wide `--test` check reported existing formatting rules in the touched PHP files; this pass did not reformat unrelated code.
- Headless Chrome reviewed Indonesian at 320, 360, 400, 720, and 1440 CSS pixels and English at 360, 400, and 1440. The 320/720 widths check reflow equivalent to 200% zoom on 640/1440-pixel displays; literal browser zoom was not exercised. No horizontal overflow appeared in measured reflow widths. The hero enquiry action remained visible, so no mobile header CTA was added.
- Browser submissions in both locales returned to the correct contact anchor, showed localized errors, and focused the first invalid field within the viewport. Switching language at the contact anchor preserved the equivalent anchor in both directions. Production-only hiding was verified by the feature suite, not by a production browser session.
- Staging, real SMTP delivery, approved photography, child-protection policy review, and same-host restore were not verified here. The media and minor-surname P0 launch blockers remain open in `docs/backend-review-report.md`.
