{{-- resources/views/pages/gallery.blade.php --}}
{{-- Spine per design spec §5: Hero → Stories (photo essays) → Next step.
     Deferred past launch (spec §10) — Gallery needs finished photo essays,
     which don't exist yet. Built here so the whole site is clickable for
     review, not because launch scope changed. $essays comes from
     App\ViewModels\PostData::photoEssays() via routes/web.php — spec §6:
     "a gallery needs no model of its own... photo essays are posts whose
     body is mostly image blocks."

     Not built here: spec §9's YouTube embeds. An embed ships ~1MB of
     player before anyone presses play; the thumbnail-facade fix that
     avoids that is its own piece of work and is deferred, per the brief. --}}
@php($heroImage = \App\ViewModels\PlaceholderImage::make(1600, 900, __('gallery.hero.image_alt')))

<x-layouts.site :title="__('gallery.hero.heading').' — Hope for Sumba'">
  <x-sections.hero
    :heading="__('gallery.hero.heading')"
    :subhead="__('gallery.hero.body')"
    :image="$heroImage" />

  <x-sections.stories :label="__('gallery.grid.label')" :stories="$essays" />

  <x-sections.next-step
    :heading="__('nextstep.heading')"
    :body="__('nextstep.body')"
    :partnerHref="route(app()->getLocale().'.contact')"
    :giveHref="route(app()->getLocale().'.give')" />
</x-layouts.site>
