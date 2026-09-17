<?php

namespace App\ViewModels;

/**
 * FIXTURE — awaiting replacement once About has a real data source (this
 * page has no App\Models equivalent named in docs/data-contract.md; it is
 * static organisational content, not an entity). I5: moved out of
 * resources/views/pages/about.blade.php, which used to build the hero,
 * founder and people images/portraits directly with ~15 lines of __() and
 * PlaceholderImage::make() calls — the same "template rewrite at
 * integration time" risk flagged for Home.
 */
class AboutData
{
    /** @return array Everything resources/views/pages/about.blade.php needs. */
    public static function get(): array
    {
        return [
            'heroImage' => PlaceholderImage::make(1600, 900, __('about.hero.image_alt')),
            'founderImage' => PlaceholderImage::make(1200, 900, __('about.founder.image_alt')),
            'people' => [
                ['name' => __('about.people.reynold')] + PlaceholderImage::make(800, 1000, __('about.people.reynold_alt')),
                ['name' => __('about.people.maria')] + PlaceholderImage::make(800, 1000, __('about.people.maria_alt')),
                ['name' => __('about.people.yohanis')] + PlaceholderImage::make(800, 1000, __('about.people.yohanis_alt')),
            ],
        ];
    }
}
