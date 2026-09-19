<?php

namespace App\Models;

use App\Models\Concerns\HasMediaAssets;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Publishable;
use App\Models\Enums\PostKind;
use App\Models\Enums\SchoolLevel;
use Database\Factories\SchoolFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'slug', 'name', 'level', 'location', 'lede', 'current_need', 'status',
    'pupils', 'teachers', 'opened_year', 'context_heading', 'context_body',
    'work_heading', 'work_body', 'published_at',
])]
class School extends Model
{
    /** @use HasFactory<SchoolFactory> */
    use HasFactory, HasMediaAssets, HasTranslations, Publishable;

    // `status` is here: a short qualitative sentence the editor writes, in
    // both locales. It is never a number, a goal or a percentage.
    protected array $translatable = [
        'slug', 'name', 'location', 'lede', 'current_need', 'status',
        'context_heading', 'context_body', 'work_heading', 'work_body',
    ];

    protected function casts(): array
    {
        return [
            'level' => SchoolLevel::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * The directory shape (docs/data-contract.md § School), resolved for the
     * current locale. No `href`: the page builds that, since only the page
     * knows which route it is linking from.
     */
    public function toDirectoryArray(): array
    {
        return [
            'slug' => $this->trans('slug'),
            'level' => $this->level->value,
            'age_range' => $this->level->ageRange(app()->getLocale()),
            'name' => $this->trans('name'),
            'location' => $this->trans('location'),
            'need' => $this->trans('current_need'),
            'status' => $this->trans('status'),
            'image' => $this->mediaFor('hero')->first()?->toImageArray(),
        ];
    }

    /** The directory shape plus everything the school detail page renders. */
    public function toDetailArray(): array
    {
        $project = $this->projects()->published()->oldest('id')->first();

        return $this->toDirectoryArray() + [
            'lede' => $this->trans('lede'),
            'people' => $this->people(),
            'context' => $this->section('context'),
            'work' => $this->section('work'),
            'evidence' => $project === null ? null : [
                'before' => $this->evidenceImage($project, 'before'),
                'after' => $this->evidenceImage($project, 'after'),
            ],
            // Strings, deliberately: "Gratis" sits in the same column as "60".
            // Every school is free to families, so that fact is not a column.
            'facts' => [
                ['key' => self::pick('Dibuka', 'Opened'), 'value' => (string) $this->opened_year],
                ['key' => self::pick('Murid', 'Pupils'), 'value' => (string) $this->pupils],
                ['key' => self::pick('Guru', 'Teachers'), 'value' => (string) $this->teachers],
                ['key' => self::pick('Biaya bagi keluarga', 'Cost to families'), 'value' => self::pick('Gratis', 'Free')],
            ],
        ];
    }

    /** Portrait items, flattened as <x-sections.people> reads them. */
    private function people(): array
    {
        return $this->posts()->published()->ofKind(PostKind::Profile)->oldest('id')->get()
            ->map(fn (Post $post) => ($post->mediaFor('portrait')->first()?->toImageArray() ?? []) + ['name' => $post->subjectName()])
            // A profile with no portrait yet has nothing to show in this section.
            ->filter(fn (array $person) => isset($person['sources']))
            ->values()
            ->all();
    }

    private function section(string $role): array
    {
        return [
            'heading' => $this->trans("{$role}_heading"),
            'body' => $this->trans("{$role}_body"),
            'image' => $this->mediaFor($role)->first()?->toImageArray(),
        ];
    }

    private function evidenceImage(Project $project, string $role): ?array
    {
        $asset = $project->mediaFor($role)->first();

        return $asset === null ? null : $asset->toImageArray() + ['caption' => $asset->trans('caption')];
    }

    private static function pick(string $id, string $en): string
    {
        return app()->getLocale() === 'en' ? $en : $id;
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function posts(): MorphMany
    {
        return $this->morphMany(Post::class, 'about');
    }
}
