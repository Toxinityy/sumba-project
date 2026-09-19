<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\StatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['label', 'body', 'value', 'as_of', 'position'])]
class Stat extends Model
{
    /** @use HasFactory<StatFactory> */
    use HasFactory, HasTranslations;

    protected array $translatable = ['label', 'body'];

    protected function casts(): array
    {
        return ['as_of' => 'date'];
    }

    /**
     * Statistics go stale quietly. This exists so the panel can surface the
     * ones that need re-checking before a reader notices they are two years
     * old; the threshold is the caller's to choose.
     */
    public function scopeStaleSince(Builder $query, string $date): void
    {
        $query->whereDate('as_of', '<', $date);
    }
}
