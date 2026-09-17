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
