{{-- resources/views/pages/schools.blade.php --}}
{{-- Spine per design spec §5: Hero/Lede → Directory grid → Next step.
     $schools comes from App\ViewModels\SchoolData::all() via routes/web.php;
     see docs/data-contract.md. --}}
@php($heroImage = \App\ViewModels\PlaceholderImage::make(1600, 900, __('schools.hero.heading')))

<x-layouts.site :title="__('schools.hero.heading').' — Hope for Sumba'">
  <x-sections.hero
    :heading="__('schools.hero.heading')"
    :subhead="__('schools.hero.body')"
    :image="$heroImage" />

  <x-sections.directory :label="__('schools.hero.label')" :schools="$schools" />

  <x-sections.next-step
    :heading="__('nextstep.heading')"
    :body="__('nextstep.body')"
    :partnerHref="\App\Support\LocalizedUrl::contact()"
    :giveHref="route(app()->getLocale().'.give')" />
</x-layouts.site>
