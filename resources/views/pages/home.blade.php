{{-- resources/views/pages/home.blade.php --}}
{{-- Spine per design spec §5: Hero → Lede → Stat band → Featured school →
     Stories → Next step. $stats, $featuredSchool and $stories come from
     App\ViewModels fixtures via routes/web.php; see docs/data-contract.md.

     No dedicated "Featured school" section exists among the thirteen —
     the spine table names it but the component set doesn't have one.
     <x-sections.work> is reused here (image+text, its own tone keeps the
     surface alternation going): see docs/agent-a-pages-report.md. --}}
@php($heroImage = \App\ViewModels\PlaceholderImage::make(1600, 900, __('home.hero.image_alt')))

<x-layouts.site title="Hope for Sumba">
  <x-sections.hero
    :heading="__('home.hero.heading')"
    :subhead="__('home.hero.subhead')"
    :image="$heroImage" />

  <x-sections.lede :label="__('home.lede.label')" :heading="__('home.lede.heading')" surface="raised">
    <p>{{ __('home.lede.body') }}</p>
  </x-sections.lede>

  <x-sections.stat-band :stats="$stats" />

  <x-sections.work :label="__('home.featured.label')" :heading="$featuredSchool['name']" :image="$featuredSchool['image']">
    <p>{{ $featuredSchool['need'] }}</p>
    <p class="mt-4">
      <x-button :href="$featuredSchool['href']" variant="secondary">{{ __('cta.give') }}</x-button>
    </p>
  </x-sections.work>

  <x-sections.stories :label="__('home.stories.label')" :heading="__('home.stories.heading')" :stories="$stories" />

  <x-sections.next-step
    :heading="__('nextstep.heading')"
    :body="__('nextstep.body')"
    :partnerHref="route(app()->getLocale().'.contact')"
    :giveHref="route(app()->getLocale().'.give')" />
</x-layouts.site>
