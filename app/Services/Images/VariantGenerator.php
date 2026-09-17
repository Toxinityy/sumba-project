<?php

namespace App\Services\Images;

use Illuminate\Support\Facades\Log;
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

        // Use whichever library ImageCapabilities actually detected formats
        // against — hardcoding GD here would silently mishandle a host where
        // Imagick is the one that's loaded (or the one with AVIF support).
        $manager = new ImageManager($this->capabilities->driver());

        $steps = self::QUALITY_STEPS;

        // decode()/scaleDown() once: encode() does not mutate the image, so
        // re-decoding and re-scaling inside the quality loop below was pure
        // waste — up to six decodes of the same source for one variant.
        $image = $manager->decode($sourcePath)->scaleDown(width: $width);
        $finalWidth = $image->width();
        $finalHeight = $image->height();

        $encoded = null;
        $usedQuality = end($steps);
        $hitFloor = true;

        foreach (self::QUALITY_STEPS as $quality) {
            $encoded = $this->encode($image, $format, $quality);
            $usedQuality = $quality;

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
