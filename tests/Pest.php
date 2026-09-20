<?php

use Database\Seeders\SchoolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class)->in('Feature');

// The pages read schools from the database, so every page-rendering test gets
// the six seeded schools. Feature/Models is left out: those tests build
// exactly the records they assert on.
uses(RefreshDatabase::class)
    ->beforeEach(fn () => $this->seed(SchoolSeeder::class))
    ->in('Feature/*.php', 'Feature/Admin', 'Feature/Cards', 'Feature/Pages', 'Feature/Sections');
