<?php

namespace App\Services\Images;

use Illuminate\Support\Facades\Cache;
use Imagick;

/**
 * What can this host actually encode?
 *
 * Shared cPanel hosting varies: GD is near-universal, Imagick is common but
 * not guaranteed, and AVIF support depends on a libavif build that many
 * shared hosts do not have. Detecting rather than assuming means the pipeline
 * degrades to something that works instead of silently producing nothing.
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
        if (function_exists('imageavif')) {
            return true;
        }

        return extension_loaded('imagick')
            && in_array('AVIF', array_map('strtoupper', Imagick::queryFormats()), true);
    }

    private function detectWebp(): bool
    {
        if (function_exists('imagewebp')) {
            return true;
        }

        return extension_loaded('imagick')
            && in_array('WEBP', array_map('strtoupper', Imagick::queryFormats()), true);
    }
}
