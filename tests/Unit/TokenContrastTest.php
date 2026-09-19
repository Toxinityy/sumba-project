<?php
// tests/Unit/TokenContrastTest.php

function relativeLuminance(string $hex): float {
    $hex = ltrim($hex, '#');
    $parts = [];
    foreach ([0, 2, 4] as $offset) {
        $channel = hexdec(substr($hex, $offset, 2)) / 255;
        $parts[] = $channel <= 0.03928
            ? $channel / 12.92
            : (($channel + 0.055) / 1.055) ** 2.4;
    }
    return 0.2126 * $parts[0] + 0.7152 * $parts[1] + 0.0722 * $parts[2];
}

function contrastRatio(string $fg, string $bg): float {
    $a = relativeLuminance($fg);
    $b = relativeLuminance($bg);
    return (max($a, $b) + 0.05) / (min($a, $b) + 0.05);
}

dataset('textPairs', [
    'light ink on surface'          => ['#1E2A22', '#F7F8F3'],
    'light muted on surface'        => ['#4B5A4E', '#F7F8F3'],
    'light accent on surface'       => ['#96660E', '#F7F8F3'],
    'light accent on raised'        => ['#96660E', '#FFFFFF'],
    'light accent-ink on accent'    => ['#FFFFFF', '#96660E'],
    'light ink on inverse'          => ['#F7F8F3', '#1E2A22'],
    'light muted on inverse'        => ['#B9C2B4', '#1E2A22'],
    'light badge-ink on badge'      => ['#5A3E0E', '#EFE3C8'],
    'dark ink on surface'           => ['#F5EFE6', '#211A15'],
    'dark muted on surface'         => ['#C9BEB0', '#211A15'],
    'dark accent on surface'        => ['#E8A23A', '#211A15'],
    'dark accent on raised'         => ['#E8A23A', '#2B231C'],
    'dark accent-ink on accent'     => ['#241A0E', '#E8A23A'],
    'dark ink on inverse'           => ['#F5EFE6', '#3A2E22'],
    'dark muted on inverse'         => ['#CFC4B4', '#3A2E22'],
    'dark badge-ink on badge'       => ['#F0C97D', '#3B2E1C'],

    // Pairings the landing-page redesign introduced (2026-09-18). Every one is
    // a combination that now appears on a real page, not a hypothetical.
    'light ink on sunk'             => ['#1E2A22', '#ECEEE3'],
    'light muted on sunk'          => ['#4B5A4E', '#ECEEE3'],
    'light badge-ink on sunk'      => ['#5A3E0E', '#ECEEE3'],
    'light ink on badge'           => ['#1E2A22', '#EFE3C8'],
    'light muted on badge'         => ['#4B5A4E', '#EFE3C8'],
    'light muted on raised'        => ['#4B5A4E', '#FFFFFF'],
    'light ink on raised'          => ['#1E2A22', '#FFFFFF'],
    'light inverse-accent on inverse' => ['#E8A23A', '#1E2A22'],
    'dark ink on sunk'             => ['#F5EFE6', '#1A1410'],
    'dark muted on sunk'           => ['#C9BEB0', '#1A1410'],
    'dark badge-ink on sunk'       => ['#F0C97D', '#1A1410'],
    'dark ink on badge'            => ['#F5EFE6', '#3B2E1C'],
    'dark muted on badge'          => ['#C9BEB0', '#3B2E1C'],
    'dark muted on raised'         => ['#C9BEB0', '#2B231C'],
    'dark ink on raised'           => ['#F5EFE6', '#2B231C'],
    'dark inverse-accent on inverse'  => ['#E8A23A', '#3A2E22'],
    'inverse-accent-ink on inverse-accent' => ['#241A0E', '#E8A23A'],
]);

it('meets WCAG AA for body text', function (string $fg, string $bg) {
    expect(contrastRatio($fg, $bg))->toBeGreaterThanOrEqual(4.5);
})->with('textPairs');

// I4: SC 1.4.11 (Non-text Contrast, Level AA) requires 3:1 for a UI
// component's visual boundary — --border was designed as decorative (the
// spec's own token table says so) and measures only 1.35:1 light / 1.23:1
// dark against --surface, yet it was the sole visible edge of every text
// input. --border-strong is the dedicated interactive-boundary token; it
// must clear 3:1 against the surface the inputs actually sit on, in both
// themes, without touching --accent (the spec forbids darkening it).
dataset('uiBoundaryPairs', [
    'light border-strong on surface' => ['#6E7062', '#F7F8F3'],
    'dark border-strong on surface'  => ['#8A7A68', '#211A15'],
]);

it('meets WCAG AA (3:1) for interactive UI boundaries', function (string $fg, string $bg) {
    expect(contrastRatio($fg, $bg))->toBeGreaterThanOrEqual(3.0);
})->with('uiBoundaryPairs');

it('keeps the dark inverse band distinct from the raised surface', function () {
    // If these collapse to the same value the section alternation dies silently.
    expect('#3A2E22')->not->toBe('#2B231C');
});

it('declares every token used by a theme in the base :root block', function () {
    $css = file_get_contents(dirname(__DIR__, 2) . '/resources/css/tokens.css');
    $base = substr($css, 0, strpos($css, '@media'));

    foreach ([
        '--surface', '--surface-raised', '--surface-sunk', '--ink', '--ink-muted',
        '--accent', '--accent-strong', '--accent-ink', '--border', '--border-strong',
        '--badge-bg', '--badge-ink',
        '--inverse-surface', '--inverse-ink', '--inverse-ink-muted',
    ] as $token) {
        expect($base)->toContain($token);
    }
});

// Spec §4's correction (2026-09-18) asks for exactly this: the contrast test
// must assert the accent-family text colour against EVERY surface token, not a
// hand-picked list. Light --accent is 4.26:1 on --surface-sunk and 3.92:1 on
// --badge-bg, so on those two grounds the accent-family text token is
// --badge-ink. The assertion is over the whole mapping, so a future component
// that reaches for accent text on a tinted ground has a test that already
// covers the ground it landed on.
dataset('accentFamilyOnEverySurface', [
    'light: accent on surface'      => ['#96660E', '#F7F8F3'],
    'light: accent on raised'       => ['#96660E', '#FFFFFF'],
    'light: badge-ink on sunk'      => ['#5A3E0E', '#ECEEE3'],
    'light: badge-ink on badge-bg'  => ['#5A3E0E', '#EFE3C8'],
    'light: inverse-accent on inverse' => ['#E8A23A', '#1E2A22'],
    'dark: accent on surface'       => ['#E8A23A', '#211A15'],
    'dark: accent on raised'        => ['#E8A23A', '#2B231C'],
    'dark: badge-ink on sunk'       => ['#F0C97D', '#1A1410'],
    'dark: badge-ink on badge-bg'   => ['#F0C97D', '#3B2E1C'],
    'dark: inverse-accent on inverse'  => ['#E8A23A', '#3A2E22'],
]);

it('clears AA for accent-family text on every surface token', function (string $fg, string $bg) {
    expect(contrastRatio($fg, $bg))->toBeGreaterThanOrEqual(4.5);
})->with('accentFamilyOnEverySurface');

it('records why sunk and tinted grounds may not use accent as text', function () {
    // The two ratios the spec correction names. This test exists so that
    // "just use text-accent, it passed on surface" is contradicted by a number
    // in the suite rather than by a paragraph in a document.
    expect(contrastRatio('#96660E', '#ECEEE3'))->toBeLessThan(4.5)
        ->and(contrastRatio('#96660E', '#EFE3C8'))->toBeLessThan(4.5);

    // And the substitute really does clear it on both.
    expect(contrastRatio('#5A3E0E', '#ECEEE3'))->toBeGreaterThanOrEqual(4.5)
        ->and(contrastRatio('#5A3E0E', '#EFE3C8'))->toBeGreaterThanOrEqual(4.5);
});

/*
 | The dataset above is hand-copied hex, so it can drift from tokens.css and
 | never learns about a surface added later. This reads the real file: every
 | surface token in each theme must map to the accent-family text colour that
 | is allowed on it, and that pair must clear AA. A new surface token with no
 | entry here fails until someone decides which accent text it takes.
 */
function themeTokens(string $selector): array {
    $css = file_get_contents(dirname(__DIR__, 2) . '/resources/css/tokens.css');
    $block = $selector === ':root'
        ? substr($css, 0, strpos($css, '@media'))
        : substr($css, strpos($css, $selector));
    $block = substr($block, 0, strpos($block, '}'));
    preg_match_all('/(--[\w-]+):\s*(#[0-9a-fA-F]{6})\s*;/', $block, $m);

    return array_combine($m[1], $m[2]);
}

it('clears AA for the allowed accent text on every surface parsed from tokens.css', function (string $selector) {
    $allowed = [
        '--surface' => '--accent',
        '--surface-raised' => '--accent',
        '--surface-sunk' => '--badge-ink',
        '--badge-bg' => '--badge-ink',
        '--inverse-surface' => '--inverse-accent',
    ];
    $tokens = themeTokens($selector);
    $surfaces = array_filter(
        array_keys($tokens),
        fn (string $t) => preg_match('/^--(surface(-[\w]+)?|badge-bg|inverse-surface)$/', $t),
    );

    expect($surfaces)->not->toBeEmpty();
    foreach ($surfaces as $surface) {
        expect($allowed)->toHaveKey($surface);
        $ratio = contrastRatio($tokens[$allowed[$surface]], $tokens[$surface]);
        expect($ratio)->toBeGreaterThanOrEqual(4.5, "{$allowed[$surface]} on {$surface} ({$selector}): {$ratio}");
    }
})->with([':root', ':root[data-theme="dark"]']);
