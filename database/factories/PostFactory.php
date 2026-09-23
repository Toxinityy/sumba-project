<?php

namespace Database\Factories;

use App\Models\Enums\PostKind;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'slug' => ['id' => Str::slug($title), 'en' => Str::slug($title)],
            'title' => ['id' => $title, 'en' => $title],
            'kind' => PostKind::Update,
            'hook' => ['id' => fake()->sentence(), 'en' => fake()->sentence()],
            'body' => ['id' => '<p>'.fake()->paragraph().'</p>', 'en' => '<p>'.fake()->paragraph().'</p>'],
            'subject_is_minor' => false,
            'published_at' => now()->subDays(10),
        ];
    }

    /** A child's profile: given name only, by construction (spec §9). */
    public function aboutAMinor(): static
    {
        return $this->state([
            'kind' => PostKind::Profile,
            'subject_given_name' => fake()->firstName(),
            'subject_is_minor' => true,
        ]);
    }

    /** An adult — a teacher, the founder — may be named in full with a role. */
    public function aboutAnAdult(): static
    {
        return $this->state([
            'kind' => PostKind::Profile,
            'subject_given_name' => fake()->firstName(),
            'subject_is_minor' => false,
            'subject_role' => ['id' => 'Kepala sekolah', 'en' => 'Head teacher'],
            // The surname is a row in its own table now (spec §9), so it is
            // created after the post exists rather than set as an attribute.
        ])->afterCreating(fn (Post $post) => $post->subjectSurname()->create([
            'family_name' => fake()->lastName(),
        ]));
    }

    public function draft(): static
    {
        return $this->state(['published_at' => null]);
    }
}
