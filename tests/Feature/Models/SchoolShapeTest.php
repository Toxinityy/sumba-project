<?php

// tests/Feature/Models/SchoolShapeTest.php

use App\Models\Enums\PostKind;
use App\Models\MediaAsset;
use App\Models\Post;
use App\Models\Project;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
 | The keys docs/data-contract.md § School fixes, minus `href`: the page
 | builds that, because only the page knows which route it is linking from.
 */
const DIRECTORY_KEYS = ['slug', 'level', 'age_range', 'name', 'location', 'need', 'status', 'image'];
const DETAIL_KEYS = [...DIRECTORY_KEYS, 'lede', 'people', 'context', 'work', 'evidence', 'facts'];
const IMAGE_KEYS = ['sources', 'width', 'height', 'alt'];

function attach(object $owner, string $role, array $extra = []): MediaAsset
{
    return MediaAsset::factory()->role($role)->for($owner, 'attachable')->create($extra);
}

function fullSchool(): School
{
    $school = School::factory()->create([
        'slug' => ['id' => 'karuni', 'en' => 'karuni'],
        'name' => ['id' => 'TK Harapan Karuni', 'en' => 'Karuni Kindergarten'],
        'level' => 'TK',
        'pupils' => 60,
        'teachers' => 3,
        'opened_year' => 2009,
    ]);

    foreach (['hero', 'context', 'work'] as $role) {
        attach($school, $role);
    }

    $project = Project::factory()->for($school)->create();
    attach($project, 'before', ['caption' => ['id' => 'Maret 2026 — sebelum', 'en' => 'March 2026 — before']]);
    attach($project, 'after', ['caption' => ['id' => 'Agustus 2026 — sesudah', 'en' => 'August 2026 — after']]);

    $teacher = Post::factory()->aboutAnAdult()->for($school, 'about')->create([
        'subject_given_name' => 'Maria',
    ]);
    // aboutAnAdult() creates a random surname row; this one is asserted on.
    $teacher->subjectSurname()->update(['family_name' => 'Bulu']);
    attach($teacher, 'portrait');

    return $school;
}

it('produces exactly the contract directory keys in both locales', function (string $locale) {
    app()->setLocale($locale);

    expect(array_keys(fullSchool()->toDirectoryArray()))->toEqualCanonicalizing(DIRECTORY_KEYS);
})->with(['id', 'en']);

it('produces exactly the contract detail keys in both locales', function (string $locale) {
    app()->setLocale($locale);
    $detail = fullSchool()->toDetailArray();

    expect(array_keys($detail))->toEqualCanonicalizing(DETAIL_KEYS)
        ->and(array_keys($detail['image']))->toEqualCanonicalizing(IMAGE_KEYS)
        ->and(array_keys($detail['context']))->toEqualCanonicalizing(['heading', 'body', 'image'])
        ->and(array_keys($detail['work']))->toEqualCanonicalizing(['heading', 'body', 'image'])
        ->and(array_keys($detail['evidence']))->toEqualCanonicalizing(['before', 'after'])
        ->and(array_keys($detail['evidence']['before']))->toEqualCanonicalizing([...IMAGE_KEYS, 'caption'])
        ->and(array_keys($detail['people'][0]))->toEqualCanonicalizing([...IMAGE_KEYS, 'name']);

    foreach ($detail['facts'] as $fact) {
        expect(array_keys($fact))->toBe(['key', 'value'])->and($fact['value'])->toBeString();
    }
})->with(['id', 'en']);

it('resolves every string for the current locale', function () {
    $school = fullSchool();

    app()->setLocale('id');
    $id = $school->toDetailArray();
    app()->setLocale('en');
    $en = $school->toDetailArray();

    expect($id['name'])->toBe('TK Harapan Karuni')
        ->and($en['name'])->toBe('Karuni Kindergarten')
        ->and($id['age_range'])->toBe('4-6 tahun')
        ->and($en['age_range'])->toBe('ages 4-6')
        ->and($id['evidence']['before']['caption'])->toBe('Maret 2026 — sebelum')
        ->and($en['evidence']['after']['caption'])->toBe('August 2026 — after')
        ->and($id['facts'])->toBe([
            ['key' => 'Dibuka', 'value' => '2009'],
            ['key' => 'Murid', 'value' => '60'],
            ['key' => 'Guru', 'value' => '3'],
            ['key' => 'Biaya bagi keluarga', 'value' => 'Gratis'],
        ])
        ->and($en['facts'][3])->toBe(['key' => 'Cost to families', 'value' => 'Free']);
});

it('derives the age range from the level, for every level', function (string $level, string $id, string $en) {
    $school = School::factory()->create(['level' => $level]);

    app()->setLocale('id');
    expect($school->toDirectoryArray()['age_range'])->toBe($id);
    app()->setLocale('en');
    expect($school->toDirectoryArray()['age_range'])->toBe($en);
})->with([
    ['TK', '4-6 tahun', 'ages 4-6'],
    ['SMP', '12-15 tahun', 'ages 12-15'],
    ['SMA', '15-18 tahun', 'ages 15-18'],
]);

it('draws people only from published profile posts about the school', function () {
    $school = fullSchool();

    // Each decoy has a portrait, so only the kind, published and ownership
    // filters can keep it out — not the separate no-portrait rule.
    $decoys = [
        Post::factory()->for($school, 'about')->create(['kind' => PostKind::News]),
        Post::factory()->aboutAnAdult()->draft()->for($school, 'about')->create(),
        Post::factory()->aboutAnAdult()->for(School::factory()->create(), 'about')->create(),
    ];
    foreach ($decoys as $decoy) {
        attach($decoy, 'portrait');
    }

    expect(collect($school->toDetailArray()['people'])->pluck('name')->all())->toBe(['Maria Bulu']);
});

it('leaves out a profile that has no portrait yet rather than failing', function () {
    $school = fullSchool();
    Post::factory()->aboutAnAdult()->for($school, 'about')->create();

    expect($school->toDetailArray()['people'])->toHaveCount(1);
});

it('takes the evidence pair from a published project, never a draft', function () {
    $school = School::factory()->create();
    $draft = Project::factory()->draft()->for($school)->create();
    attach($draft, 'before', ['caption' => ['id' => 'draf', 'en' => 'draft']]);
    attach($draft, 'after', ['caption' => ['id' => 'draf', 'en' => 'draft']]);

    expect($school->toDetailArray()['evidence'])->toBeNull();
});
