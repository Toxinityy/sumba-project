<?php

namespace Database\Factories;

use App\Models\Consent;
use App\Models\Enums\ConsentScope;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Consent>
 */
class ConsentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subject_given_name' => fake()->firstName(),
            'subject_is_minor' => true,
            'guardian_name' => fake()->name(),
            'guardian_relationship' => 'Ibu',
            'subject_assented' => true,
            'scope' => ConsentScope::Web,
            'granted_on' => now()->subMonths(2)->toDateString(),
            'review_on' => now()->addYear()->toDateString(),
            'form_scan_path' => 'consents/'.fake()->uuid().'.pdf',
        ];
    }

    /** Consent given for a printed newsletter, which is not consent for the web. */
    public function printOnly(): static
    {
        return $this->state(['scope' => ConsentScope::Print]);
    }

    public function lapsed(): static
    {
        return $this->state([
            'granted_on' => now()->subYears(3)->toDateString(),
            'review_on' => now()->subMonth()->toDateString(),
        ]);
    }

    public function withdrawn(): static
    {
        return $this->state(['withdrawn_at' => now()->subDay()]);
    }

    public function forAdult(): static
    {
        return $this->state([
            'subject_is_minor' => false,
            'guardian_name' => null,
            'guardian_relationship' => null,
        ]);
    }
}
