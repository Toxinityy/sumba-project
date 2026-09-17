<?php

// tests/Feature/Models/TranslationTest.php

use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('resolves a translated field against the current locale', function () {
    $school = School::factory()->create([
        'name' => ['id' => 'TK Harapan Karuni', 'en' => 'Karuni Kindergarten'],
    ]);

    app()->setLocale('id');
    expect($school->trans('name'))->toBe('TK Harapan Karuni');

    app()->setLocale('en');
    expect($school->trans('name'))->toBe('Karuni Kindergarten');
});

it('falls back to the default locale rather than rendering blank', function () {
    $school = School::factory()->indonesianOnly()->create();

    app()->setLocale('en');

    // Spec §7: a missing translation renders the source language with a quiet
    // note. The page needs to know which locale it actually got in order to
    // render that note, so the resolved locale is reported, not just the text.
    expect($school->trans('name'))->toBe($school->name['id'])
        ->and($school->translationLocale('name'))->toBe('id');
});

it('reports the current locale when a translation exists, so no note is shown', function () {
    $school = School::factory()->create();

    app()->setLocale('en');

    expect($school->translationLocale('name'))->toBe('en');
});

it('treats an empty string as a missing translation', function () {
    $school = School::factory()->create([
        'current_need' => ['id' => 'Ruang baca baru.', 'en' => ''],
    ]);

    app()->setLocale('en');

    expect($school->trans('current_need'))->toBe('Ruang baca baru.');
});

it('returns null when a field is empty in every locale', function () {
    $school = School::factory()->create(['lede' => []]);

    expect($school->trans('lede'))->toBeNull()
        ->and($school->translationLocale('lede'))->toBeNull();
});

it('matches slugs per locale and not across them', function () {
    School::factory()->create([
        'slug' => ['id' => 'sekolah-karuni', 'en' => 'karuni-school'],
    ]);

    // Slugs are per-locale (§7): the Indonesian slug must not resolve under
    // the English prefix, or /en/schools/sekolah-karuni would serve a page
    // at a URL that is not its own.
    expect(School::whereSlug('sekolah-karuni', 'id')->exists())->toBeTrue()
        ->and(School::whereSlug('karuni-school', 'en')->exists())->toBeTrue()
        ->and(School::whereSlug('sekolah-karuni', 'en')->exists())->toBeFalse();
});

it('defaults slug matching to the current locale', function () {
    School::factory()->create([
        'slug' => ['id' => 'sekolah-karuni', 'en' => 'karuni-school'],
    ]);

    app()->setLocale('en');

    expect(School::whereSlug('karuni-school')->exists())->toBeTrue();
});
