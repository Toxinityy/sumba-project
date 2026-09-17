<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/*
 | A null published_at is the draft state. Spec §6 asks for a `status` enum
 | here, but `status` is already taken on School and Home by the qualitative
 | progress string the data contract fixes, so publication rides on the
 | timestamp Post needs anyway. See the note in docs — flagged, not guessed.
 */
trait Publishable
{
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lessThanOrEqualTo(now());
    }

    /** Withdrawal of consent calls this; it must not be a queued job (§9). */
    public function unpublish(): void
    {
        $this->forceFill(['published_at' => null])->save();
    }
}
