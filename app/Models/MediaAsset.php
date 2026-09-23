<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Services\Images\ImageCapabilities;
use App\ViewModels\PlaceholderImage;
use Database\Factories\MediaAssetFactory;
use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\ImageManager;

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
    'depicts_minor', 'consent_id', 'subject_given_name',
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
        // Registered first, so it runs before the guards below: a save they
        // refuse must not leave a geotagged file sitting on the public disk.
        static::saving(function (self $asset) {
            if ($asset->isDirty('path')) {
                $asset->stripMetadata();
            }
        });

        static::saving(function (self $asset) {
            if (! $asset->depicts_minor) {
                return;
            }

            // The surname guard that used to sit here is gone, and so is the
            // column (2026_09_22_000001). §9 wants "no surname field at all",
            // and a model event was never that — a raw or bulk UPDATE walked
            // straight past it. Nothing ever wrote the column, so it was
            // dropped rather than constrained. See "Subject identity" in
            // docs/data-contract.md.

            // An asset flagged as depicting a minor is unusable without a
            // consent record to point at, so the link is required at write
            // time rather than checked at render time.
            if ($asset->consent_id === null) {
                throw new DomainException('An asset depicting a minor requires a consent record (spec §9).');
            }

            // Existing published content cannot acquire an unconsented image.
            $owner = $asset->attachable;
            if ($owner?->published_at !== null && $owner->published_at->lessThanOrEqualTo(now())
                && ! $asset->isPublishable()) {
                throw new DomainException('A published record cannot use media without current web consent (spec §9).');
            }
        });
    }

    /**
     * The contract's image shape. The source is still a labelled placeholder
     * — real variants arrive with the upload pipeline (spec §8) — but the
     * dimensions and the localised alt text are this asset's own.
     */
    public function toImageArray(): array
    {
        return PlaceholderImage::make((int) $this->width, (int) $this->height, (string) $this->trans('alt'));
    }

    /**
     * EXIF is stripped unconditionally, GPS included (§9): a geotagged photo
     * of a child outside their home is a published location. Re-encoding
     * drops every metadata block; orientation is applied on decode first, so
     * a phone photo does not come out sideways once its rotation tag is gone.
     * `strip` is explicit because Imagick, unlike GD, keeps EXIF by default.
     *
     * Paths are relative to the public disk, which is what the world can
     * fetch. A path with no file behind it — the seeded placeholders — has
     * nothing to strip.
     */
    private function stripMetadata(): void
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($this->path)) {
            return;
        }

        $file = $disk->path($this->path);

        (new ImageManager(app(ImageCapabilities::class)->driver(), strip: true))
            ->decode($file)
            ->encode(new AutoEncoder(quality: 92))
            ->save($file);
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

    public function scopeUnpublishable(Builder $query): void
    {
        $query->where('depicts_minor', true)
            ->whereDoesntHave('consent', fn (Builder $consent) => $consent
                ->where('subject_is_minor', true)->coveringWebUse());
    }

    /**
     * The publishing gate (§9): an asset depicting a minor goes live only
     * behind valid, web-scoped, unwithdrawn consent. Filament blocks on this
     * rather than warning about it.
     */
    public function isPublishable(): bool
    {
        if (! $this->depicts_minor) {
            return true;
        }

        $consent = $this->consent()->first();

        return $consent?->subject_is_minor && $consent->coversWebUse();
    }
}
