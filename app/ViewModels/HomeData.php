<?php

namespace App\ViewModels;

use App\ViewModels\Concerns\ResolvesLocale;

/**
 * FIXTURE — awaiting replacement by App\Models\Home (Agent B's lane, see
 * docs/data-contract.md § Home). I5: this used to be ~40 lines of __() calls
 * and PlaceholderImage::make() built directly inside
 * resources/views/pages/homes.blade.php — a real record would have made
 * that page a template rewrite, contradicting docs/data-contract.md's "No
 * Blade template should change at integration time." Moved here so the
 * template only consumes an array, same as every other launch page.
 *
 * STRICTER MEDIA RULES bind harder here than anywhere else on the site
 * (spec §9): a child resident in a home is never named, and the site never
 * states why a child is in care. `people` below names only the adult house
 * parents (permitted, in full, with their role) — never a child.
 */
class HomeData
{
    use ResolvesLocale;

    /** @return array Everything resources/views/pages/homes.blade.php needs. */
    public static function get(): array
    {
        return [
            'heroImage' => PlaceholderImage::make(1600, 900, __('homes.hero.image_alt')),
            'contextImage' => PlaceholderImage::make(1200, 800, __('homes.context.image_alt')),
            'careImage' => PlaceholderImage::make(1200, 800, __('homes.care.image_alt')),
            'people' => [
                ['name' => __('homes.people.parent1')] + PlaceholderImage::make(800, 1000, __('homes.people.parent1_alt')),
                ['name' => __('homes.people.parent2')] + PlaceholderImage::make(800, 1000, __('homes.people.parent2_alt')),
            ],
            'evidence' => [
                'before' => PlaceholderImage::make(1200, 800, __('homes.evidence.before_alt'))
                    + ['caption' => __('homes.evidence.before_caption')],
                'after' => PlaceholderImage::make(1200, 800, __('homes.evidence.after_alt'))
                    + ['caption' => __('homes.evidence.after_caption')],
            ],
            // facts shape matches docs/data-contract.md § Home — see the
            // contract update made alongside this class (I5).
            'facts' => [
                ['key' => __('homes.facts.homes_key'), 'value' => __('homes.facts.homes_value')],
                ['key' => __('homes.facts.children_key'), 'value' => __('homes.facts.children_value')],
                ['key' => __('homes.facts.parents_key'), 'value' => __('homes.facts.parents_value')],
                ['key' => __('homes.facts.cost_key'), 'value' => __('homes.facts.cost_value')],
            ],
            // status is a qualitative string, never a number — same rule as
            // School's status (docs/data-contract.md § School, rule 1).
            'status' => __('homes.status'),
        ];
    }
}
