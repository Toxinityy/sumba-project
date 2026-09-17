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

Only the `latin` and `latin-ext` subsets were kept — Indonesian and English
are both Latin-script, so cyrillic/greek/vietnamese subsets were discarded
as dead weight. Both families are variable fonts, so Google served one file
per subset per style, covering the whole requested weight range (300–700
for Fraunces roman, 400–600 for Fraunces italic, 400–700 for Plus Jakarta
Sans) rather than one file per static weight.

The `normal` style was kept for both families. The `italic` style was
initially discarded for Fraunces as well, on the assumption that the
synthetic oblique the browser applies to the roman face would be good
enough — it is not, at 28–32px on the one typographic flourish (pull
quotes, spec §4) the design spec names explicitly. The italic Fraunces
latin/latin-ext files were fetched from the same query and added; italic
Plus Jakarta Sans is still unused (no italic body text in the design) and
was not fetched.

## Files kept (final names — used by Task 11's `<link rel="preload">` tags)

| File | Subset | Style | Weight range | Bytes |
|---|---|---|---|---|
| `public/fonts/fraunces-latin.woff2` | latin | normal | 300–700 | 67,304 |
| `public/fonts/fraunces-latin-ext.woff2` | latin-ext | normal | 300–700 | 59,388 |
| `public/fonts/fraunces-italic-latin.woff2` | latin | italic | 400–600 | 81,520 |
| `public/fonts/fraunces-italic-latin-ext.woff2` | latin-ext | italic | 400–600 | 71,460 |
| `public/fonts/plus-jakarta-sans-latin.woff2` | latin | normal | 400–700 | 27,348 |
| `public/fonts/plus-jakarta-sans-latin-ext.woff2` | latin-ext | normal | 400–700 | 21,728 |

Total added to the repo: **328,748 bytes (~321 KiB)**.

Only the pull-quote component (`resources/views/components/sections/quote.blade.php`)
renders italic text, and only when its content needs the `-ext` subset does
the italic latin-ext file get fetched by the browser at all — same
`unicode-range` gating as the roman faces.

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

After `npm run build`, no reference to any third-party font host or font
family other than Fraunces/Plus Jakarta Sans remains in `resources/` or
`public/build/`. Check hosts *and* stray font names — a narrower grep for
Google hosts only is exactly how a `bunny.net`-fetched font (Instrument
Sans, pulled in by the Laravel scaffold's Vite plugin) went unnoticed:

```
$ grep -rli "fonts.googleapis\|fonts.gstatic\|bunny\|instrument" resources/ public/build/ || echo "clean"
clean
```
