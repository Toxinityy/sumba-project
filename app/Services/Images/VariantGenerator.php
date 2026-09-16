<?php

namespace App\Services\Images;

use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\AvifEncoder;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Interfaces\EncodedImageInterface;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\ImageManager;

class VariantGenerator
{
    /**
     * Quality ladder. The budget is enforced by stepping DOWN until the file
     * fits, rather than by picking one quality and hoping. A 200KB hero limit
     * that is not actually checked is not a limit.
     */
    private const QUALITY_STEPS = [82, 74, 66, 58, 50, 42];

    public function __construct(private ImageCapabilities $capabilities) {}

    public function generate(
        string $sourcePath,
        int $width,
        string $format,
        int $budgetBytes,
    ): Variant {
        if (! $this->capabilities->supports($format)) {
            $format = 'jpeg';
        }

        // ponytail: driver is hardcoded to GD because that's what this
        // machine has. Production may have Imagick instead of GD — see the
        // task report for why this isn't fixed here.
        $manager = new ImageManager(new GdDriver());

        $steps = self::QUALITY_STEPS;

        $encoded = null;
        $usedQuality = end($steps);
        $hitFloor = true;
        $finalWidth = 0;
        $finalHeight = 0;

        foreach (self::QUALITY_STEPS as $quality) {
            $image = $manager->decode($sourcePath)->scaleDown(width: $width);
            $encoded = $this->encode($image, $format, $quality);
            $usedQuality = $quality;
            $finalWidth = $image->width();
            $finalHeight = $image->height();

            if ($encoded->size() <= $budgetBytes) {
                $hitFloor = false;
                break;
            }
        }

        if ($hitFloor) {
            // Someone should choose a less detailed crop rather than ship a
            // soft hero. Silence here is how budgets quietly stop being met.
            Log::warning('Image hit the quality floor and still exceeds budget', [
                'source' => $sourcePath,
                'width' => $width,
                'format' => $format,
                'budget_bytes' => $budgetBytes,
                'actual_bytes' => $encoded->size(),
            ]);
        }

        $path = $this->writeToDisk($encoded, $format);

        return new Variant(
            path: $path,
            format: $format,
            width: $finalWidth,
            height: $finalHeight,
            bytes: $encoded->size(),
            quality: $usedQuality,
            hitQualityFloor: $hitFloor,
        );
    }

    private function encode(ImageInterface $image, string $format, int $quality): EncodedImageInterface
    {
        return match ($format) {
            'avif' => $image->encode(new AvifEncoder(quality: $quality)),
            'webp' => $image->encode(new WebpEncoder(quality: $quality)),
            default => $image->encode(new JpegEncoder(quality: $quality)),
        };
    }

    private function writeToDisk(EncodedImageInterface $encoded, string $format): string
    {
        $directory = storage_path('app/variants');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Content-hashed so variants are immutable and can carry far-future
        // cache headers.
        $path = $directory . '/' . hash('xxh128', $encoded->toString()) . '.' . $format;
        $encoded->save($path);

        return $path;
    }
}
