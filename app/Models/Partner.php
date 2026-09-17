<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Enums\PartnerType;
use Database\Factories\PartnerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name', 'logo_path', 'type', 'testimonial', 'testimonial_attribution', 'position',
])]
class Partner extends Model
{
    /** @use HasFactory<PartnerFactory> */
    use HasFactory, HasTranslations;

    // The name is a proper noun and the logo a flat graphic that never goes
    // through the variant pipeline, so neither is translated and there is no
    // MediaAsset here — just a path.
    protected array $translatable = ['testimonial'];

    protected function casts(): array
    {
        return ['type' => PartnerType::class];
    }
}
