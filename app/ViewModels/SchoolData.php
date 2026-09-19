<?php

namespace App\ViewModels;

use App\Models\School;

/**
 * The school pages' data, from App\Models\School (docs/data-contract.md §
 * School). ::all() returns the directory shape consumed by <x-cards.school>;
 * ::find() returns the same plus the detail-page fields.
 *
 * This was a fixture until 2026-09-19. The model now produces exactly the
 * arrays the fixture did, so no Blade template changed when it was swapped;
 * the copy it held lives in database/seeders/SchoolSeeder.php.
 *
 * `href` is added here rather than by the model: the contract makes it the
 * page's job, since only the page knows which locale's route it links from.
 */
class SchoolData
{
    /** @return array<int, array> Directory shape for every published school. */
    public static function all(): array
    {
        return School::published()->oldest('id')->get()
            ->map(fn (School $school) => self::withHref($school->toDirectoryArray()))
            ->all();
    }

    /** Full shape (directory + detail fields) for one school, or null. */
    public static function find(string $slug): ?array
    {
        $school = School::whereSlug($slug)->published()->first();

        return $school === null ? null : self::withHref($school->toDetailArray());
    }

    private static function withHref(array $school): array
    {
        return ['href' => route(app()->getLocale().'.schools.show', $school['slug'])] + $school;
    }
}
