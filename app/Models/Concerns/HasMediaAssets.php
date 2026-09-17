<?php

namespace App\Models\Concerns;

use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/*
 | One polymorphic attachment covers every image slot a page has — hero,
 | gallery, context, work, before, after, portrait — distinguished by `role`.
 | Six named foreign keys would say the same thing and break the moment a
 | section gains an image.
 */
trait HasMediaAssets
{
    public function media(): MorphMany
    {
        return $this->morphMany(MediaAsset::class, 'attachable')->orderBy('position');
    }

    public function mediaFor(string $role): MorphMany
    {
        return $this->media()->role($role);
    }
}
