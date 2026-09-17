<?php

namespace App\Models;

use App\Models\Concerns\HasMediaAssets;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Publishable;
use Database\Factories\HomeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/*
 | A children's home has a school's shape minus the level, plus the care
 | model. Media rules are stricter here: residents are never named, and the
 | site never states why a child is in care — which is why there is no
 | resident-name field on this model or anything hanging off it.
 */
#[Fillable([
    'slug', 'name', 'location', 'lede', 'care_model', 'current_need', 'status',
    'residents', 'carers', 'context_heading', 'context_body', 'work_heading',
    'work_body', 'published_at',
])]
class Home extends Model
{
    /** @use HasFactory<HomeFactory> */
    use HasFactory, HasMediaAssets, HasTranslations, Publishable;

    protected array $translatable = [
        'slug', 'name', 'location', 'lede', 'care_model', 'current_need', 'status',
        'context_heading', 'context_body', 'work_heading', 'work_body',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
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
