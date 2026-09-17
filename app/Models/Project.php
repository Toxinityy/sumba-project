<?php

namespace App\Models;

use App\Models\Concerns\HasMediaAssets;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Publishable;
use App\Models\Enums\ProjectStatus;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'slug', 'title', 'summary', 'body', 'status', 'school_id', 'home_id', 'published_at',
])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, HasMediaAssets, HasTranslations, Publishable;

    protected array $translatable = ['slug', 'title', 'summary', 'body'];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function home(): BelongsTo
    {
        return $this->belongsTo(Home::class);
    }

    public function posts(): MorphMany
    {
        return $this->morphMany(Post::class, 'about');
    }

    /** The Evidence section is a dated pair, so both halves must be present. */
    public function hasEvidencePair(): bool
    {
        return $this->mediaFor('before')->exists() && $this->mediaFor('after')->exists();
    }
}
