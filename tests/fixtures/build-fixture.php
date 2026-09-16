<?php

/**
 * Builds a synthetic "photographic" JPEG for image-pipeline tests.
 *
 * Why generated rather than a committed binary: a real photo would either
 * bloat the repo with a multi-megabyte binary or introduce a network
 * download and a licensing question. Generating it deterministically-ish at
 * test time avoids both.
 *
 * Why not a flat colour or pure noise (see task-7-report.md for the measured
 * numbers that confirm this lands in the right regime):
 * - A flat/solid image compresses to almost nothing at any quality, so a
 *   byte-budget test would pass without the quality-stepping loop ever
 *   running.
 * - Pure random noise compresses *worse* than a real photo and may not fit
 *   under budget even at the lowest quality step, which would wrongly flip
 *   hitQualityFloor to true.
 *
 * So this builds smooth gradients and large soft shapes (low-frequency
 * content, most of a real photo's bytes) plus sparse fine-grain noise and
 * thin edges (high-frequency content, what makes quality stepping matter).
 */
function sumba_build_hero_fixture(string $path, int $width = 3200, int $height = 2000): void
{
    $im = imagecreatetruecolor($width, $height);

    // Smooth vertical gradient — large low-frequency content, like sky/sea.
    for ($y = 0; $y < $height; $y++) {
        $t = $y / $height;
        $r = (int) (60 + 120 * $t);
        $g = (int) (90 + 100 * sin($t * M_PI));
        $b = (int) (140 + 80 * (1 - $t));
        $color = imagecolorallocate($im, $r, $g, $b);
        imageline($im, 0, $y, $width - 1, $y, $color);
    }

    // Large soft-edged blobs — mimic subject masses (hills, foliage, roofs).
    imagealphablending($im, true);
    for ($i = 0; $i < 14; $i++) {
        $cx = mt_rand(0, $width);
        $cy = mt_rand(0, $height);
        $rw = mt_rand((int) ($width * 0.1), (int) ($width * 0.35));
        $rh = mt_rand((int) ($height * 0.1), (int) ($height * 0.35));
        $color = imagecolorallocatealpha(
            $im,
            mt_rand(0, 255),
            mt_rand(0, 255),
            mt_rand(0, 255),
            mt_rand(60, 100),
        );
        imagefilledellipse($im, $cx, $cy, $rw, $rh, $color);
    }

    // Thin edges — like rigging, branches, roof lines.
    for ($i = 0; $i < 60; $i++) {
        $color = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
        imageline($im, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $color);
    }

    // Fine-grain noise on every pixel — enough high-frequency detail to
    // stop JPEG collapsing this to near-nothing, while staying far short of
    // the density of pure noise (which compresses worse than any real
    // photo because it has no large low-frequency regions at all).
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $rgb = imagecolorat($im, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $delta = mt_rand(-28, 28);
            $noiseColor = imagecolorallocate(
                $im,
                max(0, min(255, $r + $delta)),
                max(0, min(255, $g + $delta)),
                max(0, min(255, $b + $delta)),
            );
            imagesetpixel($im, $x, $y, $noiseColor);
        }
    }

    imagejpeg($im, $path, 95);
    imagedestroy($im);
}
