<?php

namespace Database\Factories;

use App\Models\Stat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stat>
 */
class StatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'label' => ['id' => 'Anak bersekolah tahun ini', 'en' => 'Children in school this year'],
            'value' => '612',
            'as_of' => now()->subMonth()->toDateString(),
            'position' => 0,
        ];
    }
}
