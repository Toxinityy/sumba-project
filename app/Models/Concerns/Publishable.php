<?php

namespace App\Models\Concerns;

use DomainException;
use Illuminate\Database\Eloquent\Builder;

/*
 | A null published_at is the draft state. `status` on School and Home is the
 | editor's qualitative sentence, not a publication state, so publication
 | rides on the timestamp Post needs anyway. Spec §6 said otherwise until the
 | correction dated 2026-09-17; that note records why.
 */
trait Publishable
{
    protected static function bootPublishable(): void
    {
        static::saving(function ($owner) {
            if ($owner->published_at !== null && $owner->published_at->lessThanOrEqualTo(now())
                && $owner->media()->unpublishable()->exists()) {
                throw new DomainException('Published content cannot use media without current web consent (spec §9).');
            }
        });
    }

    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now())
            ->whereDoesntHave('media', fn (Builder $media) => $media->unpublishable());
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lessThanOrEqualTo(now())
            && ! $this->media()->unpublishable()->exists();
    }

    /** Withdrawal of consent calls this; it must not be a queued job (§9). */
    public function unpublish(): void
    {
        $this->forceFill(['published_at' => null])->save();
    }
}
