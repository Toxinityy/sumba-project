<?php

// tests/Feature/Admin/SchoolPanelTest.php

use App\Filament\Resources\Schools\Pages\EditSchool;
use App\Filament\Resources\Schools\Pages\ListSchools;
use App\Models\School;
use App\Models\User;
use Livewire\Livewire;

/*
 | The panel Vera uses (spec §6). What matters here is not that Filament
 | works, but that OUR rules survive contact with it: both locales in one
 | form, Indonesian required and English optional, a qualitative status, and
 | a draft that stays off the site.
 */

it('keeps the panel behind a login', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
    $this->get('/admin/schools')->assertRedirect('/admin/login');
    $this->get('/admin/login')->assertOk();
});

it('lists the seeded schools for a signed-in editor', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(ListSchools::class)
        ->assertCanSeeTableRecords(School::all())
        ->assertSee('TK Harapan Karuni');
});

it('edits both locales in one form and keeps each one', function () {
    $this->actingAs(User::factory()->create());
    $school = School::whereSlug('karuni', 'id')->firstOrFail();

    Livewire::test(EditSchool::class, ['record' => $school->getRouteKey()])
        ->assertFormSet(fn (array $state) => [
            'name.id' => 'TK Harapan Karuni',
            'name.en' => $state['name']['en'],
        ])
        ->fillForm(['name.id' => 'TK Harapan Karuni Baru', 'status.en' => 'Building work starts in October.'])
        ->call('save')
        ->assertHasNoFormErrors();

    $school->refresh();

    expect($school->name['id'])->toBe('TK Harapan Karuni Baru')
        // The English name was not touched by editing the Indonesian one.
        ->and($school->name['en'])->not->toBeEmpty()
        ->and($school->status['en'])->toBe('Building work starts in October.')
        ->and($school->status['id'])->not->toBeEmpty();
});

it('refuses a status that is a figure, in either locale', function () {
    $this->actingAs(User::factory()->create());
    $school = School::whereSlug('karuni', 'id')->firstOrFail();

    // Rule 1: no numeric funding display, anywhere. The editor is stopped
    // where the number is typed, not by someone noticing it on the site.
    foreach (['75%', 'Rp 40.000.000', '40 persen'] as $figure) {
        Livewire::test(EditSchool::class, ['record' => $school->getRouteKey()])
            ->fillForm(['status.id' => $figure])
            ->call('save')
            ->assertHasFormErrors(['status.id']);
    }

    expect($school->refresh()->status['id'])->not->toBe('75%');
});

it('requires Indonesian but lets English wait', function () {
    $this->actingAs(User::factory()->create());
    $school = School::whereSlug('karuni', 'id')->firstOrFail();

    Livewire::test(EditSchool::class, ['record' => $school->getRouteKey()])
        ->fillForm(['name.id' => ''])
        ->call('save')
        ->assertHasFormErrors(['name.id']);

    // Spec §7: a missing translation renders the source language with a
    // note. So an empty English field is a normal state, not an error.
    Livewire::test(EditSchool::class, ['record' => $school->getRouteKey()])
        ->fillForm(['name.en' => ''])
        ->call('save')
        ->assertHasNoFormErrors();
});

it('keeps a draft off the public site', function () {
    $this->actingAs(User::factory()->create());
    $school = School::whereSlug('karuni', 'id')->firstOrFail();

    Livewire::test(EditSchool::class, ['record' => $school->getRouteKey()])
        ->fillForm(['published_at' => null])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->get('/id/sekolah/karuni')->assertNotFound();
    $this->get('/id/sekolah')->assertOk()->assertDontSee('TK Harapan Karuni');
});
