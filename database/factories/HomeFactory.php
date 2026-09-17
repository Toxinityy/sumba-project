<?php

namespace Database\Factories;

use App\Models\Home;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Home>
 */
class HomeFactory extends Factory
{
    public function definition(): array
    {
        $place = fake()->unique()->city();
        $slug = Str::slug($place);

        return [
            'slug' => ['id' => $slug, 'en' => $slug],
            'name' => ['id' => 'Rumah Anak '.$place, 'en' => $place.' Home'],
            'location' => ['id' => $place.', Sumba Timur', 'en' => $place.', East Sumba'],
            'lede' => ['id' => fake()->paragraph(), 'en' => fake()->paragraph()],
            'care_model' => ['id' => fake()->paragraph(), 'en' => fake()->paragraph()],
            'current_need' => ['id' => 'Dapur yang lebih besar.', 'en' => 'A larger kitchen.'],
            'status' => ['id' => 'Didanai penuh tahun ini', 'en' => 'Fully funded this year'],
            'residents' => 24,
            'carers' => 5,
            'context_heading' => ['id' => 'Mengapa rumah ini ada', 'en' => 'Why this home exists'],
            'context_body' => ['id' => fake()->paragraph(), 'en' => fake()->paragraph()],
            'work_heading' => ['id' => 'Perawatan sehari-hari', 'en' => 'Daily care'],
            'work_body' => ['id' => fake()->paragraph(), 'en' => fake()->paragraph()],
            'published_at' => now()->subWeek(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['published_at' => null]);
    }
}
