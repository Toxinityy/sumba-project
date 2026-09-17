<?php

namespace App\ViewModels;

/**
 * FIXTURE — awaiting replacement by the real image pipeline (spec §8) once
 * MediaAsset exists. See docs/data-contract.md's "The image shape" section,
 * which this must keep matching exactly.
 *
 * Same labelled-placeholder approach as the prototype (prototype/build.js's
 * `photo()`) and the component gallery (resources/views/gallery.blade.php):
 * a placehold.co URL stands in for a real photograph, and `alt` names what
 * the real photo should show so a reviewer knows what to shoot.
 * <x-picture> throws without a `jpeg` source, so one is always present.
 */
class PlaceholderImage
{
    public static function make(int $width, int $height, string $alt): array
    {
        return [
            'sources' => [
                'jpeg' => ["https://placehold.co/{$width}x{$height}/jpeg {$width}w"],
            ],
            'width' => $width,
            'height' => $height,
            'alt' => $alt,
        ];
    }
}
