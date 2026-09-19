# Self-hosted fonts

Newsreader (display) and Public Sans (body) are self-hosted as static woff2
files under `public/fonts/`. The site never requests `fonts.googleapis.com` or
`fonts.gstatic.com` at runtime.

They replaced Fraunces and Plus Jakarta Sans on 2026-09-19 (spec §4).

## Source query

Files were fetched once, ahead of deploy, from Google's CSS API:

```
https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,200..800;1,6..72,300..600&family=Public+Sans:ital,wght@0,400..700;1,400..700&display=swap
```

fetched with a Chrome desktop `User-Agent` header so Google returns woff2
(already subset per script) rather than ttf.

Only the `latin` and `latin-ext` subsets were kept. Indonesian and English
are both Latin-script, so cyrillic and vietnamese were discarded. Both
families are variable, so Google serves one file per subset per style
covering the whole requested range.

Public Sans italic is fetched because the English second-language lines
(`.ed-en` in `landing.css`) are italic body text; with no italic file the
browser would fake a slant.

## Files

| File | Subset | Style | Axes | Bytes |
|---|---|---|---|---|
| `newsreader-latin.woff2` | latin | normal | opsz 6–72, wght 200–800 | 132,000 |
| `newsreader-latin-ext.woff2` | latin-ext | normal | opsz 6–72, wght 200–800 | 86,608 |
| `newsreader-italic-latin.woff2` | latin | italic | opsz 6–72, wght 300–600 | 146,872 |
| `newsreader-italic-latin-ext.woff2` | latin-ext | italic | opsz 6–72, wght 300–600 | 95,540 |
| `public-sans-latin.woff2` | latin | normal | wght 400–700 | 26,832 |
| `public-sans-latin-ext.woff2` | latin-ext | normal | wght 400–700 | 18,472 |
| `public-sans-italic-latin.woff2` | latin | italic | wght 400–700 | 28,292 |
| `public-sans-italic-latin-ext.woff2` | latin-ext | italic | wght 400–700 | 19,312 |

`unicode-range` means a typical page fetches only the two preloaded `-latin`
roman files (about 155 KB); the `-ext` and italic files load only when a page
renders those characters or styles.

## Wiring

`resources/css/fonts.css` declares the eight `@font-face` rules (variable
`font-weight` ranges, `font-display: swap`, `unicode-range` per subset).
`resources/css/app.css` imports it first. The site layout preloads
`newsreader-latin.woff2` and `public-sans-latin.woff2`.

`tests/Feature/Pages/LandingPageTest.php` checks that every file named in
`fonts.css` and in the preload tags exists, because a missing font does not
break the page: it silently falls back to Georgia or the system sans.

## Verification

After `npm run build`, no third-party font host or stray font family should
remain in `resources/` or `public/build/`:

```
$ grep -rli "fonts.googleapis\|fonts.gstatic\|bunny\|instrument\|fraunces\|jakarta" resources/css resources/views/components public/build/ || echo "clean"
clean
```
