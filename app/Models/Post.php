<?php

namespace App\Models;

use App\Models\Concerns\HasMediaAssets;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Publishable;
use App\Models\Enums\PostKind;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'slug', 'title', 'kind', 'hook', 'body', 'quote', 'subject_given_name',
    'subject_honorific', 'subject_is_minor', 'subject_role', 'published_at',
])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, HasMediaAssets, HasTranslations, Publishable;

    protected array $translatable = ['slug', 'title', 'hook', 'body', 'quote', 'subject_role'];

    protected function casts(): array
    {
        return [
            'kind' => PostKind::class,
            'subject_is_minor' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * The card shape (docs/data-contract.md § Post), resolved for the current
     * locale. No `href`: the page builds that, as it does for School.
     */
    public function toCardArray(): array
    {
        return [
            'slug' => $this->trans('slug'),
            'kind' => $this->kind->value,
            // A profile is named by its subject; an essay has none, so it is
            // named by its own title. Contract § Post, amended 2026-09-20.
            'name' => $this->subjectName() ?? $this->trans('title'),
            'title' => $this->trans('title'),
            'hook' => $this->trans('hook'),
            'image' => $this->media()->oldest('id')->first()?->toImageArray(),
            'published_at' => $this->published_at?->format('Y-m-d'),
        ];
    }

    /** The card shape plus what a story detail page renders. */
    public function toDetailArray(): array
    {
        return $this->toCardArray() + [
            'body' => $this->trans('body'),
            'quote' => $this->trans('quote') === null ? null : [
                'text' => $this->trans('quote'),
                'attribution' => $this->subjectName(),
                'role' => $this->trans('subject_role'),
            ],
        ];
    }

    /**
     * Slugs are per-locale, so /en/stories/{slug} must resolve the English
     * slug and /id/cerita/{slug} the Indonesian one. The locale comes from
     * the route, not app()->getLocale(): implicit binding runs before the
     * `setlocale` middleware, so the app locale is still the default here.
     * Same reasoning as School::resolveRouteBinding().
     */
    public function resolveRouteBinding($value, $field = null): ?self
    {
        $locale = request()->route()?->parameter('locale') ?? app()->getLocale();

        return $this->whereSlug($value, $locale)->published()->first();
    }

    public function about(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The adult subject's family name, if there is one. Always null for a
     * minor — not by convention but because the composite foreign key on
     * subject_surnames cannot reference a post whose subject_is_minor is
     * true (spec §9, migration 2026_09_22_000002).
     */
    public function subjectSurname(): HasOne
    {
        return $this->hasOne(SubjectSurname::class);
    }

    public function scopeOfKind(Builder $query, PostKind $kind): void
    {
        $query->where('kind', $kind);
    }

    /**
     * How the subject is named publicly. A minor has no surname to append, so
     * this can only ever return a given name for them.
     */
    public function subjectName(): ?string
    {
        // An honorific is not a name on its own: a photo essay has no subject
        // at all, and must not come back named "Ibu".
        if (blank($this->subject_given_name)) {
            return null;
        }

        return trim(implode(' ', array_filter([
            // "Ibu" / "Bapak" — never shown for a minor, who is a given name
            // alone (§9), even if one was somehow stored.
            $this->subject_is_minor ? null : $this->subject_honorific,
            $this->subject_given_name,
            $this->subject_is_minor ? null : $this->subjectSurname?->family_name,
        ]))) ?: null;
    }
}
