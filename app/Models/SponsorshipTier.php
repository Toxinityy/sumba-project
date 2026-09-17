<?php

namespace App\Models;

use App\Models\Concerns\HasMediaAssets;
use App\Models\Concerns\HasTranslations;
use Database\Factories\SponsorshipTierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/*
 | `cost` is whole rupiah — a price list, not a fundraising tracker. There is
 | no companion column recording what has been given against a tier, and
 | nothing sums this one.
 */
#[Fillable(['title', 'cost', 'description', 'category', 'position'])]
class SponsorshipTier extends Model
{
    /** @use HasFactory<SponsorshipTierFactory> */
    use HasFactory, HasMediaAssets, HasTranslations;

    protected array $translatable = ['title', 'description'];

    protected function casts(): array
    {
        return ['cost' => 'integer'];
    }
}
