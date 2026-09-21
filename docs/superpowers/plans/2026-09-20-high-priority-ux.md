# High-priority visitor journeys Implementation Plan

> **For agentic workers:** Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [x]`) syntax for tracking.

**Goal:** Make support enquiries actionable, keep prototype evidence out of production, and let visitors recover from contact delivery failures.

**Architecture:** Keep the existing Laravel routes, Blade sections and bilingual JSON copy. Giving becomes an enquiry-led flow; preview-only evidence stays available outside production. Use the existing mail transport and rate limiter with localized recovery responses, not a new queue or payment integration.

**Tech Stack:** Laravel, Blade, Tailwind 4 CSS-first, Pest, Vite; cPanel production with assets built locally.

**Spec:** `docs/superpowers/specs/2026-09-16-hope-for-sumba-design.md`, `AGENTS.md`, and the user-approved high-priority review in this conversation.

## Global Constraints

- No numeric funding display, anywhere. No fundraising goals, no amounts raised, no progress bars.
- Never size a button or card to fit English text.
- No new dependencies or production Node process.
- No invented statistics, payment details, partner endorsements or legal registration details.
- Read the existing design spec and foundations plan. Their currency-display instructions conflict with AGENTS.md; the user's explicit no-numeric-funding rule wins. Record that correction in both documents.
- Leave the existing non-main feature checkout in place; no commits, publication or deployment are part of this request.

## Task 1: Actionable support enquiries without monetary figures

**Files:** `resources/views/components/cards/tier.blade.php`, `resources/views/components/sections/directory.blade.php`, `resources/views/components/sections/next-step.blade.php`, `resources/views/pages/give.blade.php`, `lang/{id,en}.json`; existing card, section and GetInvolved tests; authoritative spec and foundations plan.

**Interfaces:** Tier cards consume title, description and image; legacy cost inputs must never render. Next-step accepts an optional secondary destination and optional primary label, preserving defaults for other pages. Giving passes the localized contact anchor as its only closing action.

- [x] Replace tests requiring public currency with rendered-page assertions in both locales:
  ```php
  $html = $this->get('/en/get-involved')->assertOk()->getContent();
  expect($html)->not->toMatch('/(?:Rp\s*[\d.]|USD\s*[\d,])/');
  expect($html)->not->toContain('PLACEHOLDER', 'Wise / PayPal');
  expect($html)->toContain('href="'.url('/en').'#contact"');
  ```
- [x] Run `php vendor/bin/pest tests/Feature/Pages/GetInvolvedPageTest.php tests/Feature/Cards/CardsTest.php tests/Feature/Sections/RemainingSectionsTest.php --compact`; confirm the new assertions fail.
- [x] Remove tier cost markup and cost forwarding; retain legacy props as ignored inputs so old callers cannot leak money through attributes. Replace bank instructions with three concrete steps: tell us your interest, discuss the current school need, receive confirmed next steps from the team. Add an enquiry CTA in the hero and use the existing next-step section with one action at the end.
  ```blade
  <x-sections.next-step :heading="__('give.enquiry.heading')"
      :body="__('give.enquiry.body')" :partnerLabel="__('give.enquiry.cta')"
      :partnerHref="\App\Support\LocalizedUrl::contact()" />
  ```
- [x] Update conflicting currency guidance and rerun the targeted tests.

## Task 2: Production excludes unverified evidence and deferred destinations

**Files:** `app/Http/Middleware/PreviewOnly.php`, `routes/web.php`, homepage and footer templates, bilingual copy, `tests/Feature/Pages/ProductionContentTest.php`, deployment documentation.

**Interfaces:** PreviewOnly middleware denies requests in production at request time, including with cached routes. Existing deferred route names remain available for previews. Homepage/footer check the same production boundary before advertising them.

- [x] Add production-mode HTTP tests: deferred URLs return 404 in both locales, home has no links to those destinations, and fixture counts, challenge ledger and partner marks are absent. Assert enquiry/form and school/story links remain reachable. Keep existing non-production page tests.
  ```php
  app()['env'] = 'production';
  $this->get('/en/impact')->assertNotFound();
  $this->get('/en')->assertOk()->assertDontSee('href="'.url('/en/impact').'"', false);
  ```
- [x] Run `php vendor/bin/pest tests/Feature/Pages/ProductionContentTest.php --compact`; confirm failures.
- [x] Apply one small middleware to the four deferred page routes and the component gallery:
  ```php
  public function handle(Request $request, Closure $next): Response
  {
      abort_if(app()->environment('production'), 404);
      return $next($request);
  }
  ```
- [x] In production, omit fixture statistics, challenge ledger, placeholder evidence and partner section. Keep the quote's padded chapter so the featured-school overlap remains supported. Hide all links to deferred destinations in homepage/footer. Replace unsupported school-total headings with qualitative copy. Replace the placeholder registration line with a link to request foundation details.
- [x] Run production tests and existing landing/deferred-page tests. Document that real photography, verified editorial content and ministry details are still launch requirements.

## Task 3: Contact delivery and rate-limit recovery

**Files:** `routes/web.php`, `app/Providers/AppServiceProvider.php`, homepage contact feedback, `lang/{id,en}.json`, existing ContactPage tests.

**Interfaces:** POST contact redirects to the same locale's home contact anchor. Expected transport failures and throttled browser submissions flash only the four enquiry fields plus a form-level error. JSON clients retain 429 and Retry-After. Successful delivery alone sets `contact.sent`.

- [x] Add tests using a throwing Symfony mail transport under Laravel's real mailer; assert retained input, a localized form error, no success flash, and rendered direct-email recovery. Add a rate-limit test that submits six valid requests: five deliveries, sixth returns to the form with input and a wait instruction. Assert JSON rate-limit responses remain 429 with Retry-After.
  ```php
  $response->assertRedirect(url('/en').'#contact')
      ->assertSessionHasErrors('contact')
      ->assertSessionHasInput('message', 'Please send a school proposal.')
      ->assertSessionMissing('contact.sent');
  ```
- [x] Run `php vendor/bin/pest tests/Feature/Pages/ContactPageTest.php --compact`; confirm new failure paths fail.
- [x] Catch only `TransportExceptionInterface` around `Mail::raw`, report it, and redirect with `withErrors(['contact' => __('contact.form.failed')])->withInput($fields)`. Register a named contact limiter with the existing 5/minute limit and a browser recovery response; derive wait seconds from Retry-After. Never automatically retry sending after an ambiguous transport failure.
- [x] Render the form-level error with `role="alert"`, an actual `mailto:` fallback, and the existing old-input values. Supply actionable Indonesian and English copy. Rerun tests.

## Final verification

- [x] Run the affected page/card/section tests and color-token tests, then the complete Pest suite if the focused checks pass.
- [x] Run `npm run build` to compile uploadable assets locally.
- [x] Run `git diff --check` and inspect the final diff for unrelated changes, numeric displays and broken links.
- [x] Record observed results and browser-verification limitations. No browser audit is implied by server-rendered tests.

## Verification record (2026-09-21)

- `php vendor/bin/pest tests/Feature/Pages tests/Feature/Cards tests/Feature/Sections tests/Feature/MailAndProxyGuardsTest.php tests/Feature/LayoutTest.php tests/Feature/LanguageSwitcherTest.php tests/Unit/TokenContrastTest.php --compact`: 203 passed, 977 assertions.
- `npm run build`: passed; Vite emitted uploadable CSS and JS in `public/build/`.
- `php -l` on changed PHP application files and `git diff --check`: passed.
- Full suite: 325 passed, three failures in `tests/Feature/Models/SafeguardingTest.php` while unrelated safeguarding model and test files were being edited in the shared checkout. Those files were left untouched by this UX task.
- Browser visual review, staging delivery and production content verification against approved photography are outstanding.

## Prototype parity

- [x] Removed monetary figures and unavailable payment instructions from the checked-in clickable support prototype. `node prototype/build.js` now works under the repository's ES-module package setting and regenerated `prototype/dukung.html`. `node --check prototype/build.js` and a rendered-content assertion passed.
