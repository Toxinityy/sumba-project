<?php

namespace App\View\Components;

use InvalidArgumentException;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * The single responsive <picture> every image on the site flows through.
 *
 * Source order is load-bearing: a browser takes the first <source> whose
 * type it understands, so AVIF must precede WebP must precede the JPEG
 * fallback carried on <img> itself (not wrapped in a <source>), so old
 * browsers and no-JS get a plain src/srcset they understand.
 */
class Picture extends Component
{
    /** Browsers take the first <source> they understand, so order matters. */
    private const MIME = [
        'avif' => 'image/avif',
        'webp' => 'image/webp',
        'jpeg' => 'image/jpeg',
    ];

    /**
     * @param array<string, array<string>> $sources format => srcset entries
     */
    public function __construct(
        public array $sources,
        public int $width,
        public int $height,
        public string $alt,
        public bool $eager = false,
        public string $sizes = '100vw',
    ) {
        // JPEG is the guaranteed fallback for old browsers and no-JS. A
        // missing one would produce a broken <img src="">, so fail loudly
        // at construction rather than degrade silently.
        if (empty($this->sources['jpeg'])) {
            throw new InvalidArgumentException('Picture component requires a jpeg source as the universal fallback.');
        }
    }

    /** @return array<string, array{mime: string, srcset: string}> */
    public function orderedSources(): array
    {
        $out = [];
        foreach (self::MIME as $format => $mime) {
            if ($format === 'jpeg' || empty($this->sources[$format])) {
                continue;
            }

            $out[$format] = [
                'mime' => $mime,
                'srcset' => implode(', ', $this->sources[$format]),
            ];
        }

        return $out;
    }

    /** The <img> fallback is the last (largest) JPEG entry. */
    public function fallbackSrc(): string
    {
        $jpegs = $this->sources['jpeg'];
        $last = end($jpegs);

        return trim(explode(' ', (string) $last)[0]);
    }

    public function jpegSrcset(): string
    {
        return implode(', ', $this->sources['jpeg']);
    }

    public function render(): View
    {
        return view('components.picture');
    }
}
