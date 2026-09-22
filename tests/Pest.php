<?php

use Database\Seeders\PostSeeder;
use Database\Seeders\SchoolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class)->in('Feature');

/*
 | Every Feature test outside Models gets a clean database. Models declares
 | RefreshDatabase per file, because those tests build exactly the records
 | they assert on.
 */
uses(RefreshDatabase::class)
    ->in('Feature/*.php', 'Feature/Admin', 'Feature/Cards', 'Feature/Pages', 'Feature/Sections');

/*
 | Seeding is not free — roughly 150ms per test, paid 347 times when it was
 | attached to every directory above (2026-09-22: the suite had drifted to
 | 4m41s). It now goes only to the directories that render seeded content.
 |
 | A root-level Feature test that needs schools or stories seeds them itself:
 |
 |     beforeEach(fn () => $this->seed(SchoolSeeder::class));
 |
 | That is the same per-file opt-in Feature/Models already uses. Prefer it to
 | widening this list — the next entity makes the blanket cost worse, not
 | better.
 */
uses()
    ->beforeEach(function () {
        $this->seed(SchoolSeeder::class);
        $this->seed(PostSeeder::class);
    })
    ->in('Feature/Pages', 'Feature/Cards', 'Feature/Sections');
