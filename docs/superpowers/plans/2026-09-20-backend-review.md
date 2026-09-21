# Backend Review Plan

**Goal:** Review the existing backend against the approved design, fix confirmed backend defects that fit the current architecture, and produce a prioritized backlog for missing features and host-dependent checks while frontend work continues.

**Scope:** Review and implement confirmed backend fixes in backend-owned files. Complete the review as far as the available repository and local runtime permit; mark unavailable host, staging and specialist checks as explicit blockers with owners. Execute inline, without subagents. No deployment or commits are required.

**Architecture:** Trace each boundary from input through persistence to public output. Reuse the existing Pest suite; distinguish model helper tests from HTTP and panel enforcement. Preserve the model-to-page array contracts.

**Stack observed:** PHP requirement `^8.3`, Laravel `^13.17`, Filament `^5.8`, Intervention Image `^4.3`, Pest `^4.7`; JSON translations, database queue, server-rendered Blade.

**Authority:** `AGENTS.md`, `docs/superpowers/specs/2026-09-16-hope-for-sumba-design.md`, `docs/superpowers/plans/2026-09-16-foundations-and-design-system.md`, and `docs/data-contract.md`. Read the dated corrections before older examples.

## Repository snapshot and boundaries

Read against HEAD `e56321f` on 2026-09-20, with concurrent frontend changes present. This is a code-inspection baseline, not a passing test report. No tests were run to prepare this plan.

- School and Post public data come from Eloquent through `SchoolData` and `PostData`; several other view models remain fixtures.
- The Filament resource inventory contains Schools only. Its form edits translated text, facts and publication date; uploads, Post editing, signed preview and revisions remain incomplete.
- `MediaAsset::toImageArray()` still emits placeholders. The variant generator exists separately from the unfinished upload-to-public-image flow.
- Existing tests cover model shapes, consent helper behavior, EXIF stripping, locale routing, image conversion, panel editing and mail/proxy guards.
- `docs/admin-panel.md` still says Schools are the only database-backed public content; Post now is too. Record this documentation drift.

**Parallel-work agreement:** Read any file needed, but leave frontend-owned files unchanged: `resources/**`, `lang/**`, `app/ViewModels/**`, `app/Support/**`, `routes/web.php`, `config/locales.php`, and existing feature tests outside `Models`. The active `2026-09-20-high-priority-ux.md` plan also touches `app/Providers/AppServiceProvider.php`, preview middleware and deployment documentation; treat those as shared and review their final diff after that work lands. Do not duplicate its contact recovery or production visibility fixes.

The review writes this plan, backend-owned code and model/admin tests, and `docs/backend-review-report.md`. Shared feature tests remain with the frontend agent. Changes to `docs/data-contract.md` require agreement from both workstreams. Because this checkout already holds uncommitted frontend edits, keep them in place and edit disjoint files rather than creating a worktree that would omit their integration state.

**Implementation gate:** For each confirmed defect, add one test that fails against the current behavior, make the smallest fix in the shared backend path, and rerun targeted plus integrated tests. Do not build a CMS, image upload system, revision log or hosting environment as a side effect of this review; give those separate acceptance criteria in the report.

**Global constraints:** No numeric funding display, including sponsorship prices and currency conversions. Indonesian is the default locale; English may lag. No shell access, Node process or persistent queue worker may be assumed in production. Assets compile locally. Safeguarding applies to every publication path. Do not introduce dependencies, abstractions or deferred-page features merely to complete a review.

## 1. Establish a reproducible baseline

**Read:** `composer.json`, `composer.lock`, `phpunit.xml`, `tests/Pest.php`, migrations, factories, seeders, and the two authoritative documents.

- [ ] Capture HEAD, branch and dirty paths with `git rev-parse HEAD`, `git branch --show-current`, and `git status --short`; distinguish existing edits from review artifacts.
- [ ] Inspect `phpunit.xml` before running database tests: it currently selects SQLite `:memory:`. Verify no cached configuration redirects tests to a working database.
- [ ] Run the existing backend baseline in an isolated checkout with development dependencies available:

```powershell
php vendor/bin/pest tests/Feature/Models tests/Feature/Admin --compact
php vendor/bin/pest tests/Feature/ImageCapabilitiesTest.php tests/Feature/VariantGeneratorTest.php tests/Feature/MailAndProxyGuardsTest.php --compact
```

- [ ] Record failures, skips, duration, commit and environment. Do not reuse the historical test totals in daily plans as current evidence.

**Deliverable:** A baseline that separates pre-existing failures, missing runtime capabilities and later integration regressions.

## 2. Review safeguarding and publication first

**Read:** `app/Models/Consent.php`, `MediaAsset.php`, `Post.php`, `School.php`, `Home.php`, `Project.php`, `Concerns/Publishable.php`, safeguarding migrations, `tests/Feature/Models/SafeguardingTest.php`, and School panel save paths.

**Observed concerns, requiring reproduction:** `isPublishable()` has no application callers in the inspected tree. `coversWebUse()` checks scope, withdrawal and review date but not `subject_assented` or `granted_on`. Publication scopes check timestamps only. These are stronger concerns than a missing admin convenience feature.

- [ ] Trace all callers using `rg -n 'isPublishable|coversWebUse|published_at|withdraw\(' app tests`. Inspect actual create, update, relation-attachment and public read paths, not just helpers.
- [ ] Build a small reproduction matrix: no consent; print-only consent; expired or withdrawn consent; minor without assent; future grant date; valid web consent. For each, exercise the helper and publication of an owning School or Post. Invalid consent must not allow the asset to become public through a timestamp change.
- [ ] Test attaching invalid media to an already-published owner, replacing consent after publication, republishing after withdrawal, and crossing the review-date boundary without another save.
- [ ] Trace withdrawal through every referencing record, including related story portraits, rich-text references and already-generated files. Test a failure midway through withdrawal and determine whether partial state can remain. Record public/CDN cache and direct-image URL behavior separately from database unpublishing.
- [ ] Check private storage and access controls for consent scans. Verify ordinary public requests cannot retrieve them.
- [ ] Flag the explicit spec conflict: `Post` and `MediaAsset` have surname columns with model-event guards, whereas spec §9 requires no surname field for minor subjects. Review bulk writes and raw updates that bypass events; do not silently declare the schema requirement satisfied.
- [ ] Separate editorial checks (identifying combinations, names in prose/alt text, residential-care disclosure) from what these technical guards can enforce. Retain specialist policy review as an external requirement.

**Pass condition:** Reproductions demonstrate enforcement at publication and continued exposure boundaries, or document each failure with an owner and launch severity. A passing `isPublishable()` unit assertion alone is insufficient.

## 3. Review media ingestion and delivery

**Read:** `app/Models/MediaAsset.php`, `app/Services/Images/*`, `app/View/Components/Picture.php`, `config/filesystems.php`, `tests/Feature/Models/ExifStrippingTest.php`, image capability and variant tests, and spec §8.

- [ ] Map upload receipt → validation → metadata stripping → stored master → queued conversion → public variants → image array. Mark missing links explicitly; do not describe the standalone generator as a complete pipeline.
- [ ] Check MIME/content validation, file size and decoded dimensions, path traversal, corrupt files, overwriting the same path, and what happens if stripping fails. Verify an unprocessed upload cannot become publicly fetchable first.
- [ ] Run EXIF/GPS and orientation checks for masters and generated variants under GD; record Imagick as unverified unless that driver is actually available and tested. Include retries and failed saves.
- [ ] Check named crops/focal points and the 3000px working limit against actual code. Check whether conversion happens on upload rather than public requests.
- [ ] Exercise format fallback, tiny originals, invalid dimensions/budgets, and a high-detail image that exceeds the budget at the quality floor. The generator currently logs and returns an oversized result; determine where the required hero limit is enforced and flag any gap.
- [ ] Check URL reachability, dimensions and mandatory JPEG fallback using real generated files. Review concurrent writes, failed conversions and obsolete-file cleanup without introducing a new storage abstraction.

**Pass condition:** Evidence distinguishes implemented utilities from missing ingestion, consent enforcement, art direction and delivery integration. No claim of production image readiness based on placeholders.

## 4. Review admin access, validation and editor workflow

**Read:** `app/Models/User.php`, `app/Providers/Filament/AdminPanelProvider.php`, `app/Filament/Resources/Schools/**`, `config/auth.php`, `config/session.php`, `database/seeders/DatabaseSeeder.php`, `tests/Feature/Admin/SchoolPanelTest.php`, `docs/admin-panel.md`.

- [ ] Verify unauthenticated requests cannot list, create, edit or delete through panel pages or Livewire actions; verify login throttling and session/logout behavior with installed framework code.
- [ ] Confirm the documented staff-only account creation assumption: `canAccessPanel()` deliberately allows every User. Search for public registration, alternate account creation and accidental deployment of the development user. Do not add roles without a demonstrated need.
- [ ] Test duplicate slugs in the same locale, valid cross-locale differences, empty English fields, malformed JSON-shaped input, negative/fractional counts and invalid years. Check model/database constraints as well as panel validation.
- [ ] Check draft, scheduled and published behavior, save failures and how validation/domain exceptions reach the editor. Review delete actions and dependent data/media consequences.
- [ ] Compare the actual workflow with phase 2's requirement that Vera create both a school and a post, preview them, and recover earlier revisions. List missing Post screens, uploads, signed preview, undo/revisions, translation completeness and consent review dashboard as delivery gaps, not regressions in features that never existed.

**Pass condition:** Access and validation findings have reproductions; remaining editor capabilities have concrete acceptance criteria and priorities.

## 5. Review data integrity and bilingual public contracts

**Read:** `app/Models/**`, content/supporting migrations, seeders, `app/ViewModels/{SchoolData,PostData,HomeData,PartnerData,StatData,TierData}.php`, `app/Support/LocalizedUrl.php`, `routes/web.php`, and `docs/data-contract.md`.

- [ ] Run existing translation, shape, relationship, seeder and route-binding tests; compare results to the documented arrays, including nullable media/evidence/quotes.
- [ ] Exercise missing English content and missing English slug separately. `trans()` may fall back to Indonesian while `whereSlug()` requires the requested locale's slug: verify directory links, detail binding and language switching agree. Preserve wrong-locale slug rejection without quietly losing the promised translation fallback.
- [ ] Render with an empty database, unpublished featured school, withdrawn voice story, renamed featured slugs, empty quote translations and missing related media. The homepage currently names `anakalang` and `ibu-maria-bulu`; verify normal editorial changes cannot produce server errors or expose drafts.
- [ ] Check JSON queries and slug uniqueness on the intended MySQL/MariaDB host version, not only SQLite. Review foreign-key deletion behavior and polymorphic orphan risks.
- [ ] Verify seeders are repeatable and cannot overwrite editorial changes accidentally. Keep demo records and development users out of the production import procedure. Never run `migrate:fresh` on the shared working database.
- [ ] Measure queries for school/story lists and details. Record repeated relationship queries only where demonstrated; avoid speculative caches or pagination for launch-sized content.
- [ ] Inventory remaining fixture-to-model gaps and distinguish launch content from explicitly deferred Gallery, Partners, Impact and Projects.

**Pass condition:** Both locales have stable shapes and valid links; missing optional content and editorial changes have defined outcomes. Contract changes go to both owners before implementation.

## 6. Review contact and production integration after frontend work lands

**Shared files, read only until handoff:** `routes/web.php`, `app/Providers/AppServiceProvider.php`, preview middleware, `bootstrap/app.php`, `config/mail.php`, contact and production-content tests.

- [ ] Review the completed high-priority UX diff against its existing plan instead of implementing it twice.
- [ ] Verify validation boundaries, honeypot rejection, CSRF enforcement, delivery success, transport failure, preserved allowed input, localized recovery and browser/JSON throttling behavior. Standard Laravel tests bypass some middleware protections; include an explicit middleware-enabled check for CSRF.
- [ ] Confirm invalid mail configuration cannot silently report delivery, sensitive enquiry contents are not unnecessarily logged, and a transport failure does not trigger duplicate automatic sends.
- [ ] Trace rate-limit identity through trusted proxy configuration. Verify forged forwarding headers cannot bypass throttling when the origin is directly reachable; record the actual host's origin restrictions separately from application tests.
- [ ] Confirm deferred pages and fixture claims stay inaccessible in production, including when routes were cached in another environment. Run the frontend agent's new production tests on the final integrated state.

**Pass condition:** Contact and visibility fixes survive backend review without conflicting edits or weakened response contracts.

## 7. Review deployment and operational recovery

**Read:** `docs/deployment.md`, `docs/admin-panel.md`, `deploy/cron.txt`, `config/{queue,filesystems,database,session}.php`, `.env.example`, lockfile platform requirements and bootstrap providers. Do not print real `.env` values.

- [ ] Compare the installed packages' PHP/extension requirements with deployment instructions. Flag the foundations plan's historical PHP 8.2+ wording against Composer's current `^8.3` requirement; verify the host before release.
- [ ] If a disposable release directory and host credentials are available, verify production dependency installation, compiled application/Filament assets, configuration/routes/views caching, storage delivery and writable paths. Otherwise record the exact unverified release checks for staging. Avoid replacing this shared checkout's dependencies or caches while the frontend agent runs.
- [ ] Inspect queue retry/timeout settings and reproduce overlapping cron invocations with a slow job. `--max-time=50` does not itself prove a running job finishes before the next minute; verify the non-overlap claim in `deploy/cron.txt` against installed worker behavior.
- [ ] Check the no-shell procedure for migrations, asset publication, storage links, staff provisioning, queue failures and capability reporting. Confirm production mail boot guards do not make the documented setup sequence impossible.
- [ ] Review upload exclusions for local `.env`, cached local configuration, development artifacts, private scans and secrets. Verify APP_KEY remains stable across routine deployments and rollback.
- [ ] Perform database plus media backup/restore on staging when available, then verify content, login and media URLs. If no staging host exists, mark this as a launch blocker owned by the deployment team. Local unit tests do not establish recovery readiness.
- [ ] Verify safeguarding tests have an actual CI execution path; no `.github` directory was present in this snapshot. Record other CI evidence if it exists rather than assuming there is none.

**Pass condition:** A repeatable no-shell release and tested restore, or a clearly assigned host-dependent blocker. Hosting, Imagick and Cloudflare configuration remain unverified until measured on the real host.

## Report and completion

- [ ] Write `docs/backend-review-report.md` with each finding's severity, exact file/line, violated requirement, reproduction, expected/actual result, smallest repair direction, owner and regression check. Label observations without execution evidence as unverified.
- [ ] Separate security/data-exposure defects, functional regressions, missing planned features, documentation conflicts and host-dependent checks. Prioritize safeguarding exposure, then lost enquiries/access, then broken public data, editor completion and operational readiness.
- [ ] After frontend handoff, run `php artisan test` once on the integrated snapshot; report failures/skips honestly. Re-run affected tests only if subsequent changes justify it.
- [ ] Record the reviewed start/end commits and any files that changed during review. A moving frontend snapshot must not be presented as a stable audited release.
- [ ] Deliver a short ordered repair backlog. The review is complete when every task has evidence or an explicit unresolved dependency, not when every missing feature has been built.

**First review session:** baseline → publication/consent reproductions → upload exposure/EXIF → panel access. Continue with contracts and deployment while frontend changes settle; finish with shared contact/production integration.

## Execution record — 2026-09-21

The review and local fixes are documented in `docs/backend-review-report.md`. Confirmed defects in consent validity, publication gating, withdrawal atomicity, fallback slugs, the production demo seeder, School form validation and cron overlap were reproduced or traced and fixed in backend-owned files. The focused tests passed. The final integrated suite run passed 336 tests and 1,334 assertions; Pint and `git diff --check` passed.

The unchecked items above are deliberate remaining work, not silently completed tasks. Media upload and revocation of served files, Post editing, signed preview, revision history, specialist safeguarding review, MySQL behavior, staging restore, Cloudflare origin restriction and host image capabilities need separate implementation or real-host evidence. The empty-homepage 500 and missing translation notice are assigned to the frontend/pages workstream. Do not treat local SQLite or GD results as launch approval.
