<?php

// tests/Feature/Models/SchoolSeederTest.php

use App\Models\School;
use Database\Seeders\SchoolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds six published schools', function () {
    $this->seed(SchoolSeeder::class);

    expect(School::published()->count())->toBe(6);
});
