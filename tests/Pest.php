<?php

use Database\Seeders\PostSeeder;
use Database\Seeders\SchoolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class)->in('Feature');

// The pages read schools and stories from the database, so every
// page-rendering test gets both seeded. Feature/Models is left out: those
// tests build exactly the records they assert on.
uses(RefreshDatabase::class)
    ->beforeEach(function () {
        $this->seed(SchoolSeeder::class);
        $this->seed(PostSeeder::class);
    })
    ->in('Feature/*.php', 'Feature/Admin', 'Feature/Cards', 'Feature/Pages', 'Feature/Sections');
