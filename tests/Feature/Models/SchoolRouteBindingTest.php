<?php

// tests/Feature/Models/SchoolRouteBindingTest.php

use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

/*
 | A probe route per locale, built the way routes/web.php builds the real ones
 | — literal prefix, `setlocale`, locale as a route default — so binding runs
 | under the same middleware order the school page will.
 */
beforeEach(function () {
    foreach (['id', 'en'] as $locale) {
        Route::middleware(['web', 'setlocale'])->prefix($locale)->group(function () use ($locale) {
            Route::get('probe/{school}', fn (School $school) => $school->trans('name'))
                ->defaults('locale', $locale);
        });
    }

    School::factory()->create([
        'slug' => ['id' => 'harapan-karuni', 'en' => 'karuni-hope'],
        'name' => ['id' => 'TK Harapan Karuni', 'en' => 'Karuni Hope Kindergarten'],
    ]);
});

it('resolves each locale against its own slug', function () {
    $this->get('/id/probe/harapan-karuni')->assertOk()->assertSee('TK Harapan Karuni');
    $this->get('/en/probe/karuni-hope')->assertOk()->assertSee('Karuni Hope Kindergarten');
});

it("404s the other locale's slug rather than resolving through it", function () {
    $this->get('/en/probe/harapan-karuni')->assertNotFound();
    $this->get('/id/probe/karuni-hope')->assertNotFound();
});

it('404s a draft school', function () {
    School::factory()->draft()->create(['slug' => ['id' => 'draf', 'en' => 'draft']]);

    $this->get('/id/probe/draf')->assertNotFound();
});

it('uses the source slug when English has not been translated', function () {
    School::factory()->create([
        'slug' => ['id' => 'belum-diterjemahkan'],
        'name' => ['id' => 'Sekolah Belum Diterjemahkan'],
    ]);

    $this->get('/en/probe/belum-diterjemahkan')
        ->assertOk()->assertSee('Sekolah Belum Diterjemahkan');
});
