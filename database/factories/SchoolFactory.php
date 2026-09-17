<?php

namespace Database\Factories;

use App\Models\Enums\SchoolLevel;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<School>
 */
class SchoolFactory extends Factory
{
    public function definition(): array
    {
        $place = fake()->unique()->city();
        $slug = Str::slug($place);

        return [
            'slug' => ['id' => $slug, 'en' => $slug],
            'name' => ['id' => 'TK Harapan '.$place, 'en' => $place.' Kindergarten'],
            'level' => SchoolLevel::Tk,
            'location' => ['id' => $place.', Sumba Barat Daya', 'en' => $place.', Southwest Sumba'],
            'lede' => ['id' => fake()->paragraph(), 'en' => fake()->paragraph()],
            'current_need' => ['id' => 'Ruang baca baru untuk 60 anak.', 'en' => 'A new reading room for 60 children.'],
            // Qualitative, never a number.
            'status' => ['id' => 'Butuh 4 mitra lagi', 'en' => 'Four more partners needed'],
            'pupils' => 60,
            'teachers' => 3,
            'opened_year' => 2009,
            'context_heading' => ['id' => 'Sekolah terdekat sembilan kilometer jauhnya', 'en' => 'The nearest school is nine kilometres away'],
            'context_body' => ['id' => fake()->paragraph(), 'en' => fake()->paragraph()],
            'work_heading' => ['id' => 'Apa yang sedang dikerjakan', 'en' => 'What is being done'],
            'work_body' => ['id' => fake()->paragraph(), 'en' => fake()->paragraph()],
            'published_at' => now()->subWeek(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['published_at' => null]);
    }

    /** A school whose English translation has not been written yet (§7). */
    public function indonesianOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => ['id' => $attributes['name']['id']],
            'current_need' => ['id' => $attributes['current_need']['id']],
        ]);
    }
}
