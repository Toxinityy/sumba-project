<?php

namespace App\ViewModels;

/**
 * FIXTURE — awaiting real partners (docs/data-contract.md § Partner).
 *
 * Previously built inline in resources/views/pages/partners.blade.php. The
 * landing page's partner band needs the same list, and two copies of the same
 * placeholder array is how the two drift apart.
 *
 * The names stay CLEARLY FICTIONAL on purpose. Real partner names and marks
 * need written permission before they appear on a public page, and this site's
 * audience is institutional donors doing due diligence — a plausible-looking
 * fake partner is worse than an honest placeholder.
 */
class PartnerData
{
    /** @return array<int, array{name: string, logo: string, type: string}> */
    public static function all(): array
    {
        return [
            ['name' => __('partners.partner1'), 'logo' => 'https://placehold.co/160x60?text=Mitra+1', 'type' => 'corporate'],
            ['name' => __('partners.partner2'), 'logo' => 'https://placehold.co/160x60?text=Mitra+2', 'type' => 'church'],
            ['name' => __('partners.partner3'), 'logo' => 'https://placehold.co/160x60?text=Mitra+3', 'type' => 'foundation'],
        ];
    }
}
