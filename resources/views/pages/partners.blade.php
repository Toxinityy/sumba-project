{{-- resources/views/pages/partners.blade.php --}}
{{-- Spine per design spec §5: Hero → Lede → Partners → Next step. Deferred
     past launch (spec §10) — Partners needs real logos and written
     permission, neither of which exist yet. Built here so the whole site
     is clickable for review, not because launch scope changed.

     <x-sections.partners> has existed since the design system was built
     and had never been placed on a page until this pass. Its first real
     use gets clearly fictional placeholders (partners.partner1/2/3, "Example
     Partner N" — the data contract's own placeholder convention), never
     names that could be mistaken for a real organisation: this site's
     audience is institutional donors doing due diligence, and a fake
     partner logo is worse than an empty page. --}}
@php($heroImage = \App\ViewModels\PlaceholderImage::make(1600, 900, __('partners.hero.image_alt')))
@php($partners = [
    ['name' => __('partners.partner1'), 'logo' => 'https://placehold.co/160x60?text=Mitra+1', 'type' => 'corporate'],
    ['name' => __('partners.partner2'), 'logo' => 'https://placehold.co/160x60?text=Mitra+2', 'type' => 'church'],
    ['name' => __('partners.partner3'), 'logo' => 'https://placehold.co/160x60?text=Mitra+3', 'type' => 'foundation'],
])

<x-layouts.site :title="__('partners.hero.heading').' — Hope for Sumba'">
  <x-sections.hero
    :heading="__('partners.hero.heading')"
    :subhead="__('partners.hero.body')"
    :image="$heroImage" />

  <x-sections.lede :label="__('partners.lede.label')" :heading="__('partners.lede.heading')" surface="raised">
    <p>{{ __('partners.lede.body') }}</p>
  </x-sections.lede>

  <x-sections.partners :partners="$partners" :heading="__('partners.grid.heading')" />

  <x-sections.next-step
    :heading="__('nextstep.heading')"
    :body="__('nextstep.body')"
    :partnerHref="route(app()->getLocale().'.contact')"
    :giveHref="route(app()->getLocale().'.give')" />
</x-layouts.site>
