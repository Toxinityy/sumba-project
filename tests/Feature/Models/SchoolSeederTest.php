<?php

// tests/Feature/Models/SchoolSeederTest.php

use App\Models\School;
use App\ViewModels\SchoolData;
use Database\Seeders\SchoolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

const SEEDED_SLUGS = ['karuni', 'anakalang', 'kambera', 'melolo', 'lewa', 'waikabubak'];

it('seeds six published schools', function () {
    $this->seed(SchoolSeeder::class);

    expect(School::published()->count())->toBe(6);
});

/*
 | The fixture is what every school page renders today, so matching it key for
 | key, in both locales, is what "the pages will not change" means. `href` is
 | the page's to build and is the only key the model does not produce.
 */
it('reproduces the fixture exactly for every school in both locales', function (string $locale) {
    $this->seed(SchoolSeeder::class);
    app()->setLocale($locale);

    foreach (SEEDED_SLUGS as $slug) {
        $expected = SchoolData::find($slug);
        unset($expected['href']);

        expect(School::whereSlug($slug)->firstOrFail()->toDetailArray())->toBe($expected);
    }
})->with(['id', 'en']);
