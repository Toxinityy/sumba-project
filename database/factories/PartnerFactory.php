<?php

namespace Database\Factories;

use App\Models\Enums\PartnerType;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Partner>
 */
class PartnerFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'logo_path' => 'img/partners/'.Str::slug($name).'.png',
            'type' => PartnerType::Corporate,
            'testimonial' => null,
            'position' => 0,
        ];
    }

    public function withTestimonial(): static
    {
        return $this->state([
            'testimonial' => ['id' => fake()->sentence(), 'en' => fake()->sentence()],
            'testimonial_attribution' => fake()->name(),
        ]);
    }
}
