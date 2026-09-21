<?php

use App\Models\School;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('never installs demo content or a known-password account in production', function () {
    app()['env'] = 'production';

    app(DatabaseSeeder::class)->setContainer(app())->run();

    expect(User::count())->toBe(0)
        ->and(School::count())->toBe(0);
});
