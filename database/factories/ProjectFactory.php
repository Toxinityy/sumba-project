<?php

namespace Database\Factories;

use App\Models\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'slug' => ['id' => Str::slug($title), 'en' => Str::slug($title)],
            'title' => ['id' => $title, 'en' => $title],
            'summary' => ['id' => fake()->sentence(), 'en' => fake()->sentence()],
            'body' => ['id' => '<p>'.fake()->paragraph().'</p>', 'en' => '<p>'.fake()->paragraph().'</p>'],
            'status' => ProjectStatus::Underway,
            'published_at' => now()->subWeek(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['published_at' => null]);
    }
}
