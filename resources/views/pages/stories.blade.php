{{-- resources/views/pages/stories.blade.php --}}
{{-- Hero → Stories grid → Next step. Spec §5 has no spine row for the
     stories index; this is the Stories section doing its own page, which is
     what the prototype shipped (prototype/cerita.html).

     $stories comes from App\ViewModels\PostData via routes/web.php. Every
     card's href points back at this index until story detail pages exist
     (deferred, spec §10) — see docs/agent-a-pages-report.md. --}}
@php($heroImage = \App\ViewModels\PlaceholderImage::make(1600, 900, __('stories.hero.image_alt')))

<x-layouts.site :title="__('stories.hero.heading').' — Hope for Sumba'">
  <x-sections.hero
    :heading="__('stories.hero.heading')"
    :subhead="__('stories.hero.body')"
    :image="$heroImage" />

  <x-sections.stories :label="__('stories.grid.label')" :stories="$stories" />

  <x-sections.next-step
    :heading="__('nextstep.heading')"
    :body="__('nextstep.body')"
    :partnerHref="\App\Support\LocalizedUrl::contact()"
    :giveHref="route(app()->getLocale().'.give')" />
</x-layouts.site>
