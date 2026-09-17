<?php

namespace Database\Factories;

use App\Models\SponsorshipTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SponsorshipTier>
 */
class SponsorshipTierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => ['id' => 'Ruang kelas', 'en' => 'A classroom'],
            'cost' => 180_000_000,
            'description' => [
                'id' => 'Satu ruang kelas lengkap dengan meja, kursi dan papan tulis.',
                'en' => 'One classroom with desks, chairs and a board.',
            ],
            'category' => 'building',
            'position' => 0,
        ];
    }
}
