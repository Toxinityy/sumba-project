<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Database\Factories\MediaAssetFactory;
use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/*
 | Every upload is wrapped by one of these: the file, its translated alt text
 | and caption, the consent that permits publishing it, and the art-direction
 | data (focal point plus named crops) the picture pipeline needs.
 |
 | This is a plain table rather than spatie/laravel-medialibrary, which the
 | spec names. Adding the package is a decision for whoever builds the upload
 | and conversion pipeline; the columns here are the ones the site reads, and
 | they map onto a medialibrary custom-properties payload if that lands later.
 */
#[Fillable([
    'path', 'width', 'height', 'alt', 'caption', 'credit', 'taken_on',
    'depicts_minor', 'consent_id', 'subject_given_name', 'subject_family_name',
    'focal_x', 'focal_y', 'crops', 'role', 'position',
])]
class MediaAsset extends Model
{
    /** @use HasFactory<MediaAssetFactory> */
    use HasFactory, HasTranslations;

    protected array $translatable = ['alt', 'caption'];

    protected function casts(): array
    {
        return [
            'taken_on' => 'date',
            'depicts_minor' => 'boolean',
            'focal_x' => 'float',
            'focal_y' => 'float',
            'crops' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $asset) {
            if (! $asset->depicts_minor) {
                return;
            }

            // Spec §9 wants minor subjects to have no surname field at all.
            // A single table cannot drop a column conditionally and the schema
            // builder cannot express a CHECK constraint, so the invariant is
            // enforced here — where every write path, Filament included, goes
            // through it — rather than left to editorial discipline.
            if (filled($asset->subject_family_name)) {
                throw new DomainException('An asset depicting a minor cannot carry a surname (spec §9).');
            }

            // An asset flagged as depicting a minor is unusable without a
            // consent record to point at, so the link is required at write
            // time rather than checked at render time.
            if ($asset->consent_id === null) {
                throw new DomainException('An asset depicting a minor requires a consent record (spec §9).');
            }
        });
    }

    public function consent(): BelongsTo
    {
        return $this->belongsTo(Consent::class);
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeRole(Builder $query, string $role): void
    {
        $query->where('role', $role);
    }

    /**
     * The publishing gate (§9): an asset depicting a minor goes live only
     * behind valid, web-scoped, unwithdrawn consent. Filament blocks on this
     * rather than warning about it.
     */
    public function isPublishable(): bool
    {
        return ! $this->depicts_minor || ($this->consent?->coversWebUse() ?? false);
    }
}
