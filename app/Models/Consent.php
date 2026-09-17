<?php

namespace App\Models;

use App\Models\Enums\ConsentScope;
use Database\Factories\ConsentFactory;
use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'subject_given_name', 'subject_is_minor', 'guardian_name', 'guardian_relationship',
    'subject_assented', 'scope', 'granted_on', 'review_on', 'form_scan_path',
])]
class Consent extends Model
{
    /** @use HasFactory<ConsentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'subject_is_minor' => 'boolean',
            'subject_assented' => 'boolean',
            'scope' => ConsentScope::class,
            'granted_on' => 'date',
            'review_on' => 'date',
            'withdrawn_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $consent) {
            if ($consent->subject_is_minor && blank($consent->guardian_name)) {
                throw new DomainException('A minor\'s consent record requires a guardian (spec §9).');
            }
        });
    }

    public function mediaAssets(): HasMany
    {
        return $this->hasMany(MediaAsset::class);
    }

    /**
     * Whether this record permits publication on the public website right now.
     * Scope is checked rather than assumed: consent for a printed newsletter
     * is not consent for a website, and a record past its review date has
     * lapsed rather than merely aged.
     */
    public function coversWebUse(): bool
    {
        return $this->scope === ConsentScope::Web
            && $this->withdrawn_at === null
            && $this->review_on->endOfDay()->isFuture();
    }

    /**
     * Withdrawal has to take effect now, not as a task in someone's inbox
     * (§9), so it unpublishes everything the affected assets are attached to
     * in the same call.
     */
    public function withdraw(): void
    {
        $this->forceFill(['withdrawn_at' => now()])->save();

        $this->mediaAssets()->with('attachable')->each(function (MediaAsset $asset) {
            $owner = $asset->attachable;

            if ($owner !== null && method_exists($owner, 'unpublish')) {
                $owner->unpublish();
            }
        });
    }
}
