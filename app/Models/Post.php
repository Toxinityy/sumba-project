<?php

namespace App\Models;

use App\Models\Concerns\HasMediaAssets;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Publishable;
use App\Models\Enums\PostKind;
use Database\Factories\PostFactory;
use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'slug', 'title', 'kind', 'hook', 'body', 'subject_given_name',
    'subject_family_name', 'subject_is_minor', 'subject_role', 'published_at',
])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, HasMediaAssets, HasTranslations, Publishable;

    protected array $translatable = ['slug', 'title', 'hook', 'body', 'subject_role'];

    protected function casts(): array
    {
        return [
            'kind' => PostKind::class,
            'subject_is_minor' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $post) {
            // First names only for minors (§9). Enforced on the write path
            // because "the editor will remember" is not a control.
            if ($post->subject_is_minor && filled($post->subject_family_name)) {
                throw new DomainException('A post about a minor cannot carry a surname (spec §9).');
            }
        });
    }

    public function about(): MorphTo
    {
        return $this->morphTo();
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
        return trim(implode(' ', array_filter([
            $this->subject_given_name,
            $this->subject_is_minor ? null : $this->subject_family_name,
        ]))) ?: null;
    }
}
