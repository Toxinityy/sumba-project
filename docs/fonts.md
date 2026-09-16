# Self-hosted fonts

Fraunces (display) and Plus Jakarta Sans (body) are self-hosted as static woff2
files under `public/fonts/`. The site never requests `fonts.googleapis.com` or
`fonts.gstatic.com` at runtime.

## Source query

Files were fetched once, at build time, from Google's CSS API:

```
https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,400..600&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap
```

fetched with a Chrome desktop `User-Agent` header so Google returns woff2
(already correctly subset per-script) rather than ttf.

Only the `normal` style (no italic) and only the `latin` and `latin-ext`
subsets were kept — Indonesian and English are both Latin-script, so
cyrillic/greek/vietnamese subsets were discarded as dead weight. Both
families are variable fonts, so Google served one file per subset covering
the whole requested weight range (300–700 for Fraunces, 400–700 for Plus
Jakarta Sans) rather than one file per static weight.

## Files kept (final names — used by Task 11's `<link rel="preload">` tags)

| File | Subset | Weight range | Bytes |
|---|---|---|---|
| `public/fonts/fraunces-latin.woff2` | latin | 300–700 | 67,304 |
| `public/fonts/fraunces-latin-ext.woff2` | latin-ext | 300–700 | 59,388 |
| `public/fonts/plus-jakarta-sans-latin.woff2` | latin | 400–700 | 27,348 |
| `public/fonts/plus-jakarta-sans-latin-ext.woff2` | latin-ext | 400–700 | 21,728 |

Total added to the repo: **175,768 bytes (~172 KiB)**.

For most pages (plain latin text) only `fraunces-latin.woff2` and
`plus-jakarta-sans-latin.woff2` will actually be fetched by the browser —
`unicode-range` means the `-latin-ext` files only load when a page renders a
Latin-Extended character (e.g. some Indonesian loanwords/diacritics).

## Wiring

`resources/css/fonts.css` declares the four `@font-face` rules (variable
`font-weight` ranges, `font-display: swap`, matching `unicode-range` per
subset). `resources/css/app.css` imports it first, before `tailwindcss` and
`tokens.css`, so the font declarations are available before anything else in
the stylesheet references `--font-display` / `--font-body`.

## Verification

After `npm run build`, no reference to `fonts.googleapis.com` or
`fonts.gstatic.com` remains in `resources/` or `public/build/`:

```
$ grep -r "fonts.googleapis\|fonts.gstatic" resources/ public/build/ || echo "clean"
clean
```
