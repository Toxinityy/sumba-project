{{-- resources/views/pages/safeguarding.blade.php --}}
{{-- Spine per design spec §5: Hero → Lede → Lede → Lede — a policy page,
     explicitly exempt from rule 1 ("never two prose-dominant sections
     adjacent"): the spec names this exemption directly rather than leaving
     it as an undocumented deviation. Inventing visual rhythm on a
     child-protection policy would be worse than the prose wall the rule
     otherwise prevents.

     The three ledes are: editorial rules (naming, consent-to-publish),
     enforcement (EXIF/location stripping, withdrawal), and how to request
     removal. Surface tones still alternate (raised/sunk/raised) purely as a
     visual seam between blocks — not to dodge the exemption, which the page
     doesn't need. Content is prototype/perlindungan-anak.html's, split
     across three headed sections instead of two so the section count
     matches the spine exactly; no paragraph was reworded to do it. --}}
@php($heroImage = \App\ViewModels\PlaceholderImage::make(1600, 900, __('safeguarding.hero.image_alt')))

<x-layouts.site :title="__('safeguarding.hero.heading').' — Hope for Sumba'">
  <x-sections.hero
    :heading="__('safeguarding.hero.heading')"
    :subhead="__('safeguarding.hero.body')"
    :image="$heroImage" />

  <x-sections.lede :label="__('safeguarding.hero.label')" :heading="__('safeguarding.rules.heading')" surface="raised">
    <p>{{ __('safeguarding.rules.body1') }}</p>
    <p class="mt-4">{{ __('safeguarding.rules.body2') }}</p>
  </x-sections.lede>

  <x-sections.lede :heading="__('safeguarding.enforcement.heading')" surface="sunk">
    <p>{{ __('safeguarding.enforcement.body1') }}</p>
    <p class="mt-4">{{ __('safeguarding.enforcement.body2') }}</p>
  </x-sections.lede>

  <x-sections.lede :heading="__('safeguarding.removal.heading')" surface="raised">
    <p>{{ __('safeguarding.removal.body') }}</p>
    <p class="mt-6">
      <a href="{{ route(app()->getLocale().'.contact') }}"
         class="font-semibold text-accent underline underline-offset-4 hover:no-underline">
        {{ __('safeguarding.removal.cta') }} &rarr;
      </a>
    </p>
  </x-sections.lede>
</x-layouts.site>
