{{-- resources/views/pages/homes.blade.php --}}
{{-- Spec §5 gives the Children's home page "the same shape as School detail,
     stricter media rules" — but that is the shape of a single home's DETAIL
     page. There is no Home fixture or model to build a directory from yet
     (docs/data-contract.md has no Home entry), so this is the overview the
     prototype shipped (prototype/rumah-anak.html): Hero → care model →
     privacy → Next step. When Home records exist, the care-model and privacy
     sections stay and a <x-sections.directory> slots in between them.

     Every image alt here says "no identifiable faces" on purpose. Residential
     care is the stricter tier of the safeguarding rules (spec §9): the site
     never names a child in a home and never states why they are in care. The
     alt text is where a photo editor reads that rule at the moment they pick
     the photograph, so it is written into the placeholder. --}}
@php($heroImage = \App\ViewModels\PlaceholderImage::make(1600, 900, __('homes.hero.image_alt')))
@php($careImage = \App\ViewModels\PlaceholderImage::make(1200, 900, __('homes.care.image_alt')))

<x-layouts.site :title="__('homes.hero.heading').' — Hope for Sumba'">
  <x-sections.hero
    :heading="__('homes.hero.heading')"
    :subhead="__('homes.hero.body')"
    :image="$heroImage" />

  <x-sections.work :label="__('homes.care.label')" :heading="__('homes.care.heading')" :image="$careImage">
    <p>{{ __('homes.care.body') }}</p>
  </x-sections.work>

  <x-sections.lede :label="__('homes.privacy.label')" :heading="__('homes.privacy.heading')" surface="raised">
    <p>{{ __('homes.privacy.body') }}</p>
    <p class="mt-6">
      <a href="{{ route(app()->getLocale().'.safeguarding') }}"
         class="font-semibold text-accent underline underline-offset-4 hover:no-underline">
        {{ __('homes.privacy.cta') }} &rarr;
      </a>
    </p>
  </x-sections.lede>

  <x-sections.next-step
    :heading="__('nextstep.heading')"
    :body="__('nextstep.body')"
    :partnerHref="route(app()->getLocale().'.contact')"
    :giveHref="route(app()->getLocale().'.give')" />
</x-layouts.site>
