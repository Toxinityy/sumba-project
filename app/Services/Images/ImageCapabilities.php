<?php

namespace App\Services\Images;

use Illuminate\Support\Facades\Cache;
use Imagick;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Interfaces\DriverInterface;

/**
 * What can this host actually encode?
 *
 * Shared cPanel hosting varies: GD is near-universal, Imagick is common but
 * not guaranteed, and AVIF support depends on a libavif build that many
 * shared hosts do not have. Detecting rather than assuming means the pipeline
 * degrades to something that works instead of silently producing nothing.
 *
 * Exactly one library is ever selected — Imagick when it's loaded, GD
 * otherwise — and the capability report reflects *that* library's formats,
 * not the union of both. VariantGenerator must build its ImageManager with
 * driver() rather than hardcoding a driver, or the two can disagree: GD
 * without AVIF plus Imagick with AVIF would otherwise report "avif: yes"
 * and then hand the encode to a GD driver that cannot produce one.
 *
 * Detection is cached: probing Imagick's format list on every request is
 * wasted work on a host we do not control and cannot speed up.
 */
class ImageCapabilities
{
    private const CACHE_KEY = 'image-capabilities';
    private const CACHE_TTL = 3600;

    /** Best first. JPEG is last and unconditional. */
    private const PREFERENCE = ['avif', 'webp', 'jpeg'];

    /**
     * The one driver this host's report is computed against. Imagick is
     * preferred when available; GD is the near-universal fallback.
     */
    public function driver(): DriverInterface
    {
        return extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
    }

    /** @return array<string, bool> */
    public function report(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn () => [
            'avif' => $this->detectAvif(),
            'webp' => $this->detectWebp(),
            'jpeg' => true,
        ]);
    }

    public function supports(string $format): bool
    {
        return $this->report()[$format] ?? false;
    }

    /** @return array<string> */
    public function bestChain(): array
    {
        $chain = array_values(array_filter(
            self::PREFERENCE,
            fn (string $format) => $this->supports($format)
        ));

        // Belt and braces: JPEG must always be encodable or uploads fail.
        if (! in_array('jpeg', $chain, true)) {
            $chain[] = 'jpeg';
        }

        return $chain;
    }

    private function detectAvif(): bool
    {
        if (extension_loaded('imagick')) {
            return in_array('AVIF', array_map('strtoupper', Imagick::queryFormats()), true);
        }

        return function_exists('imageavif');
    }

    private function detectWebp(): bool
    {
        if (extension_loaded('imagick')) {
            return in_array('WEBP', array_map('strtoupper', Imagick::queryFormats()), true);
        }

        return function_exists('imagewebp');
    }
}
