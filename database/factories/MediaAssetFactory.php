<?php

namespace Database\Factories;

use App\Models\Consent;
use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaAsset>
 */
class MediaAssetFactory extends Factory
{
    public function definition(): array
    {
        $subject = fake()->words(3, true);

        return [
            'path' => 'media/'.fake()->uuid().'.jpg',
            'width' => 3000,
            'height' => 2000,
            'alt' => ['id' => 'Foto '.$subject, 'en' => 'Photo of '.$subject],
            'caption' => null,
            'taken_on' => now()->subMonths(3)->toDateString(),
            'depicts_minor' => false,
            'focal_x' => 0.5,
            'focal_y' => 0.4,
            'crops' => ['wide' => '21:9', 'tall' => '4:5', 'card' => '4:5', 'story' => '3:2'],
            'role' => 'gallery',
        ];
    }

    /**
     * An asset depicting a minor cannot exist without a consent record, so
     * this state supplies one rather than leaving the caller to discover the
     * refusal.
     */
    public function depictingMinor(?Consent $consent = null): static
    {
        return $this->state([
            'depicts_minor' => true,
            'subject_family_name' => null,
            'consent_id' => $consent?->id ?? Consent::factory(),
        ]);
    }

    public function role(string $role): static
    {
        return $this->state(['role' => $role]);
    }
}
