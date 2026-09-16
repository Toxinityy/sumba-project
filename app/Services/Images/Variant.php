<?php

namespace App\Services\Images;

final readonly class Variant
{
    public function __construct(
        public string $path,
        public string $format,
        public int $width,
        public int $height,
        public int $bytes,
        public int $quality,
        public bool $hitQualityFloor,
    ) {}
}
