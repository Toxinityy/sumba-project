<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // No WithoutModelEvents: the §9 safeguarding guards are `saving` events,
    // and a seeder that skipped them could write exactly the record they
    // exist to refuse.
    public function run(): void
    {
        $this->call(SchoolSeeder::class);

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
