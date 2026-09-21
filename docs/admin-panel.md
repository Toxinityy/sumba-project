# The admin panel

Filament 5, at `/admin`. This is the staff side of the site: Vera and the
team edit content here, in Indonesian, and the public site stays bilingual.

## Getting in

| | |
|---|---|
| Address | `https://<site>/admin` — locally, `http://127.0.0.1:8000/admin` |
| Login | `/admin/login`, email and password |
| Accounts | Created by hand, one per member of staff |

Create an account:

```
php artisan make:filament-user
```

It asks for a name, an email and a password. **Every user can open the
panel** (`User::canAccessPanel()` returns true) because every account is
made by hand for a member of staff. There are no roles yet: spec §6 has
Vera publishing without a reviewer. The day the site grows public accounts,
that method is the one line to change.

The bundled `DatabaseSeeder` contains review fixtures and a known-password
`test@example.com` account. It does nothing in production; create real staff
accounts by hand after deploying.

## What is in it

**Sekolah (schools).** The only content type wired into the panel so far.
The public schools and stories pages both read from the database, but posts
do not yet have an editing screen.

- A list showing the Indonesian name and location, the level, the pupil
  count, whether it is live, and whether English exists yet ("ID saja" means
  the English is still missing).
- An edit form with a **Bahasa Indonesia** tab and an **English** tab.
  Indonesian is required; English may be left empty, because a page with no
  English renders Indonesian with a note rather than 404ing (spec §7).
- **Fakta dan penerbitan:** level, year opened, pupils, teachers, and the
  publication date. An empty date is a draft, and a draft does not appear on
  the site at all.

Two project rules are enforced in the form itself, not left to review:

- **Status may not be a funding figure.** "75%", "Rp 40.000.000" and "40
  persen" are refused. A number inside a sentence is fine — the seeded copy
  for Karuni reads "Ruang baca baru untuk 60 anak."
- **Slugs are per locale**, lower case and hyphens, so `/id/sekolah/karuni`
  and `/en/schools/karuni-hope` can be the same school.

## Not built yet

Posts, homes, projects, partners, statistics and tiers have no screens yet —
only schools. Also missing, from spec §6: image uploads in the panel, a
preview link for unpublished pages, and revision history with an undo.

## Deploying it

Filament's compiled CSS and JS live under `public/css/filament`,
`public/js/filament` and `public/fonts/filament`. They are **gitignored**, so
they are not in the repository: after deploying, they have to be published on
the server (or uploaded with the rest of the build).

```
php artisan filament:assets
```

**PHP needs the `intl` extension.** Filament 5 requires it. It was off in
local PHP and had to be enabled in `php.ini`; check it is on with the host
before committing to a cPanel plan, because Filament will not install
without it.
