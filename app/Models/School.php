<?php

namespace App\Models;

use App\Models\Concerns\HasMediaAssets;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Publishable;
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

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function posts(): MorphMany
    {
        return $this->morphMany(Post::class, 'about');
    }
}
